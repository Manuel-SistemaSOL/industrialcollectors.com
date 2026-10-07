// ============================================================
// Shop Controller V2
// ============================================================
(function (window, document) {
    'use strict';

    window.BM_TIENDA_CONTROLLER_V2 = true;

    const API_BASE = 'https://ssl.sol.sistemasolgt.com/brovic/';
    const API = {
        catalog: 'api/shop_products.php',
        nuevos: API_BASE + 'api/api_tienda_articulos_listarProductosnuevos.php',
        categoria: API_BASE + 'api/api_tienda_articulos_listarProductosxCategoria.php',
        busqueda: API_BASE + 'api/api_tienda_articulos_listarProductosxSearch.php',
        ofertas: API_BASE + 'api/api_tienda_ofertas.php'
    };
    const IMAGE_BASE = API_BASE + 'files/articulos/';
    const SHOP_PER_PAGE = 30;

    const state = {
        mode: 'nuevos',
        page: 1,
        perPage: SHOP_PER_PAGE,
        idcategoria: '',
        idsucursal: '4',
        search: '',
        priceMin: null,
        priceMax: null,
        products: [],
        filtered: [],
        meta: null,
        loading: false,
        queuedLoad: false,
        queuedSyncAfterLoad: false
    };

    let shopGrid = null;
    let homeGrid = null;
    let pagination = null;
    let countLabel = null;
    let resizeTimer = null;
    let isDivisionView = false;
    let precioMinimo = 0;
    let precioMaximo = 100;

    document.addEventListener('DOMContentLoaded', initShopController);

    function initShopController() {
        shopGrid = document.getElementById('products-collections-filter');
        homeGrid = document.getElementById('contenedor-nuevos-productos');
        pagination = document.getElementById('products-pagination');
        countLabel = document.getElementById('bm-products-count');
        isDivisionView = Boolean(shopGrid && shopGrid.closest('.bm-shop-division'));

        if (!shopGrid && !homeGrid) {
            return;
        }

        if (shopGrid) {
            readStateFromUrl();
            bindPagination();
            bindPriceFilter();
            loadShopProducts(false);
            window.addEventListener('resize', handlePaginationResize);
            return;
        }

        loadHomeProducts();
    }

    function readStateFromUrl() {
        const params = new URLSearchParams(window.location.search);
        const categoria = params.get('categoria') || '';
        const buscar = params.get('buscar') || '';
        const ofertas = params.get('ofertas') === '1';
        state.idsucursal = params.get('idsucursal') || '4';
        state.page = normalizePage(params.get('page'));
        state.priceMin = normalizeOptionalNumber(params.get('price_min'));
        state.priceMax = normalizeOptionalNumber(params.get('price_max'));
        state.idcategoria = categoria;
        state.search = buscar.trim();

        if (isDivisionView || ofertas) {
            state.page = 1;
            state.perPage = SHOP_PER_PAGE;
            state.priceMin = null;
            state.priceMax = null;
        }

        if (ofertas) {
            state.mode = 'ofertas';
            state.idcategoria = '';
            state.search = '';
        } else if (state.search) {
            state.mode = 'busqueda';
        } else if (state.idcategoria) {
            state.mode = 'categoria';
        } else {
            state.mode = 'nuevos';
        }

        normalizeInitialShopUrl(params);
    }

    function normalizeInitialShopUrl(params) {
        const allowed = new URLSearchParams();
        ['idsucursal', 'page', 'price_min', 'price_max', 'categoria', 'buscar', 'ofertas'].forEach(function (key) {
            const value = params.get(key);
            if (value !== null && value !== '') {
                allowed.set(key, value);
            }
        });

        const cleanQuery = allowed.toString();
        if (cleanQuery !== params.toString()) {
            window.history.replaceState({}, '', window.location.pathname + (cleanQuery ? '?' + cleanQuery : ''));
        }
    }

    function normalizePage(value) {
        const page = parseInt(value, 10);
        return Number.isFinite(page) && page > 0 ? page : 1;
    }

    function normalizeOptionalNumber(value) {
        if (value === null || value === '') {
            return null;
        }

        const number = parseFloat(value);
        return Number.isFinite(number) ? number : null;
    }

    function calcularRangoPrecios(products) {
        const prices = (Array.isArray(products) ? products : [])
            .map(function (product) {
                return parseFloat(normalizePrice(product && (product.precio_venta || product.precio || 0)));
            })
            .filter(function (price) {
                return Number.isFinite(price) && price >= 0;
            });

        if (!prices.length) {
            precioMinimo = 0;
            precioMaximo = 100;
            return;
        }

        precioMinimo = Math.floor(Math.min.apply(null, prices));
        precioMaximo = Math.ceil(Math.max.apply(null, prices));

        if (precioMinimo === precioMaximo) {
            precioMaximo = precioMinimo + 100;
        }
    }

    function normalizeMeta(meta, fallbackTotal) {
        const source = meta && typeof meta === 'object' ? meta : {};
        const total = Math.max(0, parseInt(source.total, 10) || fallbackTotal || 0);
        const perPage = Math.max(1, parseInt(source.per_page, 10) || state.perPage);
        const totalPages = Math.max(1, parseInt(source.total_pages, 10) || Math.ceil(total / perPage) || 1);
        const page = Math.min(Math.max(1, parseInt(source.page, 10) || state.page), totalPages);
        const from = total > 0 ? (parseInt(source.from, 10) || ((page - 1) * perPage + 1)) : 0;
        const to = total > 0 ? (parseInt(source.to, 10) || Math.min(total, page * perPage)) : 0;

        state.page = page;
        state.perPage = perPage;

        return {
            page: page,
            perPage: perPage,
            total: total,
            totalPages: totalPages,
            from: from,
            to: to,
            mode: source.mode || state.mode
        };
    }

    function updatePriceRangeFromResponse(filters, products) {
        const range = filters && filters.price_range ? filters.price_range : null;
        const min = range ? parseFloat(range.min) : NaN;
        const max = range ? parseFloat(range.max) : NaN;

        if (Number.isFinite(min) && Number.isFinite(max)) {
            precioMinimo = Math.floor(min);
            precioMaximo = Math.ceil(max);
            if (precioMinimo === precioMaximo) {
                precioMaximo = precioMinimo + 100;
            }
            return;
        }

        calcularRangoPrecios(products);
    }

    function bindPagination() {
        if (!pagination) {
            return;
        }

        pagination.addEventListener('click', function (event) {
            const button = event.target.closest('[data-page]');
            if (!button || button.disabled) {
                return;
            }

            const page = normalizePage(button.getAttribute('data-page'));
            if (page === state.page) {
                return;
            }

            state.page = page;
            loadShopProducts(true).then(scrollToShopStart);
        });
    }

    function bindPriceFilter() {
        document.addEventListener('click', function (event) {
            if (!event.target.closest('.price-range-filter-button .btn')) {
                return;
            }

            event.preventDefault();
            applyPriceFilterFromSlider();
        });
    }

    function getShopRequestSignature() {
        return [
            state.mode,
            state.page,
            state.perPage,
            state.idcategoria,
            state.idsucursal,
            state.search,
            state.priceMin,
            state.priceMax
        ].join('|');
    }

    async function loadShopProducts(syncAfterLoad) {
        if (!shopGrid) {
            return;
        }

        if (state.loading) {
            state.queuedLoad = true;
            state.queuedSyncAfterLoad = state.queuedSyncAfterLoad || Boolean(syncAfterLoad);
            return;
        }

        const requestSignature = getShopRequestSignature();
        state.loading = true;
        renderShopMessage('Cargando productos...', 'info');
        setPaginationVisible(false);
        setCountLabel('');

        try {
            const response = await fetchProductsForState();
            if (!response.success || !Array.isArray(response.data)) {
                throw new Error('Respuesta invalida de la API');
            }

            if (requestSignature !== getShopRequestSignature()) {
                return;
            }

            state.products = normalizeProducts(response.data);
            state.filtered = state.products.slice();
            state.meta = normalizeMeta(response.meta, state.products.length);
            updatePriceRangeFromResponse(response.filters, state.products);

            renderShopPage();
            schedulePriceSliderInit();
            if (syncAfterLoad) {
                syncUrl();
            }
        } catch (error) {
            if (requestSignature !== getShopRequestSignature() && state.queuedLoad) {
                return;
            }

            console.error('Error al cargar productos:', error);
            state.products = [];
            state.filtered = [];
            state.meta = null;
            renderShopMessage('Error al cargar los productos. Por favor intenta de nuevo.', 'danger');
            setPaginationVisible(false);
            setCountLabel('');
        } finally {
            state.loading = false;

            if (state.queuedLoad) {
                const queuedSyncAfterLoad = state.queuedSyncAfterLoad;
                state.queuedLoad = false;
                state.queuedSyncAfterLoad = false;
                loadShopProducts(queuedSyncAfterLoad);
            }
        }
    }

    function getEndpoint() {
        if (state.mode === 'categoria') return API.categoria;
        if (state.mode === 'busqueda') return API.busqueda;
        if (state.mode === 'ofertas') return API.ofertas;
        return API.nuevos;
    }

    async function fetchProductsForState() {
        return postJson(API.catalog, buildPayload());
    }

    function getSidebarCategoryIds() {
        return Array.from(document.querySelectorAll('.widget_categories a.nav-link[href*="categoria="]'))
            .map(function (link) {
                try {
                    return new URL(link.getAttribute('href'), window.location.href).searchParams.get('categoria');
                } catch (error) {
                    return '';
                }
            })
            .filter(function (id, index, ids) {
                return id && ids.indexOf(id) === index;
            });
    }

    function buildPayload() {
        const payload = {
            mode: state.mode,
            page: state.page,
            per_page: state.perPage,
            idsucursal: state.idsucursal
        };

        if (state.mode === 'categoria') {
            payload.idcategoria = state.idcategoria;
        }

        if (state.mode === 'busqueda') {
            payload.search = state.search;
        }

        if (state.priceMin !== null) {
            payload.price_min = state.priceMin;
        }

        if (state.priceMax !== null) {
            payload.price_max = state.priceMax;
        }

        return payload;
    }

    async function postJson(url, payload) {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload || {})
        });

        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }

        return response.json();
    }

    function normalizeProducts(products) {
        const seen = new Set();
        const normalized = [];

        products.forEach(function (product, index) {
            if (!product || typeof product !== 'object') {
                return;
            }

            const safeProduct = normalizeProduct(product);
            const key = safeProduct.idarticulo || safeProduct.codigo || (safeProduct.nombre + '-' + index);

            if (seen.has(key)) {
                return;
            }

            seen.add(key);
            normalized.push(safeProduct);
        });

        return normalized;
    }

    function normalizeProduct(product) {
        const idarticulo = cleanText(product.idarticulo || product.id || '');
        const nombre = cleanText(product.nombre || product.descripcion || 'Producto');
        const descripcion = cleanText(product.descripcion || '');
        const codigo = cleanText(product.codigo || product.codigobarra || '');
        const stock = cleanText(firstFilledStockValue(
            product.stock,
            product.stocksucursal,
            product.stock_sucursal,
            product.existencia,
            product.cantidad_stock,
            'En stock'
        ));
        const productoConPrecio = typeof window.BM_normalizeProductPricing === 'function'
            ? window.BM_normalizeProductPricing(product)
            : product;
        const precioVentaOriginal = normalizePrice(product.precio_venta || product.precio || 0);
        const precioOferta = normalizePrice(
            product.precio_09 ||
            product.precio09 ||
            product.precio9 ||
            product.precio_oferta ||
            0
        );
        const precioVenta = normalizePrice(
            typeof window.BM_getProductEffectivePrice === 'function'
                ? window.BM_getProductEffectivePrice(product)
                : (productoConPrecio.precio_venta || product.precio_venta || product.precio || 0)
        );

        const imagenOriginal = getFirstValidImageValue(product.imagen, product.foto);

        return {
            idarticulo: idarticulo,
            nombre: nombre,
            descripcion: descripcion,
            codigo: codigo,
            stock: stock,
            stocksucursal: stock,
            precio_venta: precioVenta,
            precio_venta_original: precioVentaOriginal,
            precio_09: precioOferta,
            precio_oferta: precioOferta,
            imagen: buildImageUrl(imagenOriginal),
            imagenOriginal: cleanText(imagenOriginal)
        };
    }

    function firstFilledStockValue() {
        for (let index = 0; index < arguments.length; index += 1) {
            const value = arguments[index];

            if (value === 0 || value === '0') {
                return '0';
            }

            if (value === null || value === undefined) {
                continue;
            }

            const text = cleanText(value);

            if (text !== '') {
                return text;
            }
        }

        return '';
    }

    function cleanText(value) {
        return String(value ?? '')
            .replace(/[\u0000-\u001F\u007F]/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function normalizePrice(value) {
        const number = parseFloat(String(value).replace(',', '.'));
        return Number.isFinite(number) && number >= 0 ? number.toFixed(2) : '0.00';
    }

    function getFirstValidImageValue() {
        for (let index = 0; index < arguments.length; index += 1) {
            const raw = cleanText(arguments[index]);

            if (!isInvalidImageValue(raw)) {
                return raw;
            }
        }

        return '';
    }

    function isInvalidImageValue(value) {
        if (typeof window.BM_isInvalidProductImage === 'function') {
            return window.BM_isInvalidProductImage(value);
        }

        const raw = cleanText(value).toLowerCase();
        return !raw || ['0', 'null', 'undefined', 'nofoto', 'nofoto.png', 'n/a'].includes(raw);
    }

    function buildImageUrl(value) {
        const raw = cleanText(value);

        if (isInvalidImageValue(raw)) {
            return window.BM_PRODUCT_IMAGE_FALLBACK;
        }

        if (typeof window.BM_resolveProductImage === 'function') {
            return window.BM_resolveProductImage(raw, IMAGE_BASE);
        }

        if (/^https?:\/\//i.test(raw)) {
            try {
                return new URL(raw).href;
            } catch (error) {
                return window.BM_PRODUCT_IMAGE_FALLBACK;
            }
        }

        if (raw.includes('..')) {
            return window.BM_PRODUCT_IMAGE_FALLBACK;
        }

        return encodeURI(IMAGE_BASE + raw.replace(/^[/\\]+/, ''));
    }

    function applyProductFilters() {
        const min = state.priceMin;
        const max = state.priceMax;

        state.filtered = state.products.filter(function (product) {
            const price = parseFloat(product.precio_venta) || 0;
            if (min !== null && price < min) return false;
            if (max !== null && price > max) return false;
            return true;
        });
    }

    function renderShopPage() {
        if (!shopGrid) {
            return;
        }

        const meta = state.meta || normalizeMeta(null, state.products.length);
        const total = meta.total;
        if (total === 0) {
            renderShopMessage('No hay productos disponibles para mostrar.', 'info');
            setPaginationVisible(false);
            setCountLabel('');
            return;
        }

        shopGrid.innerHTML = '';
        const fragment = document.createDocumentFragment();
        state.products.forEach(function (product) {
            fragment.appendChild(createProductColumn(product, true));
        });
        shopGrid.appendChild(fragment);

        setCountLabel(isDivisionView ? state.products.length + ' productos' : total + ' productos');
        if (isDivisionView) {
            setPaginationVisible(false);
            return;
        }

        renderPagination(total, meta.totalPages, meta.from, meta.to);
    }

    function renderShopMessage(message, type) {
        if (!shopGrid) {
            return;
        }

        shopGrid.innerHTML = '';
        const column = document.createElement('div');
        column.className = 'col-12';

        const alert = document.createElement('div');
        alert.className = 'alert alert-' + (type || 'info');
        alert.textContent = message;

        column.appendChild(alert);
        shopGrid.appendChild(column);
    }

    function createProductColumn(product, shopFormat) {
        const column = document.createElement('div');
        column.className = shopFormat ? 'bm-shop-product-col col-lg-3 col-md-4 col-sm-6 col-6' : 'col-lg-3 col-md-4 col-sm-6 col-6';
        column.appendChild(createProductCard(product, shopFormat));
        return column;
    }

    function createProductCard(product, shopFormat) {
        const outOfStock = typeof window.BM_isProductOutOfStock === 'function'
            ? window.BM_isProductOutOfStock(product)
            : false;
        const card = document.createElement('div');
        card.className = shopFormat ? 'single-shop-products' : 'single-arrivals-products';
        if (outOfStock) {
            card.classList.add('is-out-of-stock');
        }

        const imageWrap = document.createElement('div');
        imageWrap.className = shopFormat ? 'shop-products-image' : 'arrivals-products-image';

        const link = document.createElement('a');
        link.href = 'producto.php?id=' + encodeURIComponent(product.idarticulo);

        const img = document.createElement('img');
        img.src = product.imagen;
        img.alt = product.nombre;
        img.loading = 'lazy';
        img.decoding = 'async';
        if (typeof window.BM_isProductImageFallbackUrl === 'function' && window.BM_isProductImageFallbackUrl(product.imagen)) {
            img.classList.add('bm-product-image-fallback');
        }
        img.onerror = function () {
            if (typeof window.BM_applyProductImageFallback === 'function') {
                window.BM_applyProductImageFallback(this);
                return;
            }

            this.onerror = null;
            this.src = window.BM_PRODUCT_IMAGE_FALLBACK || 'assets/img/ImagenTemporalLABROCA.png';
        };

        link.appendChild(img);
        imageWrap.appendChild(link);

        if (outOfStock && typeof window.BM_getOutOfStockRibbonHtml === 'function') {
            imageWrap.insertAdjacentHTML('beforeend', window.BM_getOutOfStockRibbonHtml(product));
        } else {
            const tag = document.createElement('div');
            tag.className = 'tag';
            tag.textContent = 'New';
            imageWrap.appendChild(tag);
        }

        const content = document.createElement('div');
        content.className = shopFormat ? 'shop-products-content' : 'arrivals-products-content';

        const title = document.createElement('h3');
        if (!shopFormat) {
            title.className = 'product-title-limit';
        }

        const titleLink = document.createElement('a');
        titleLink.href = 'producto.php?id=' + encodeURIComponent(product.idarticulo);
        titleLink.textContent = product.nombre;
        title.appendChild(titleLink);

        content.appendChild(title);
        content.appendChild(createRating());
        content.insertAdjacentHTML('beforeend', getProductPriceHtml(product));
        content.insertAdjacentHTML('beforeend', getProductActionsHtml(product));

        card.appendChild(imageWrap);
        card.appendChild(content);
        return card;
    }

    function createRating() {
        const rating = document.createElement('ul');
        rating.className = 'rating';

        for (let i = 0; i < 5; i += 1) {
            const item = document.createElement('li');
            const icon = document.createElement('i');
            icon.className = 'bx bxs-star';
            item.appendChild(icon);
            rating.appendChild(item);
        }

        return rating;
    }

    function getProductPriceHtml(product) {
        if (typeof window.obtenerHtmlPreciosProducto === 'function') {
            return window.obtenerHtmlPreciosProducto(product);
        }

        return '<span class="product-card-price product-card-price-normal">Precio: Q' + product.precio_venta + '</span>';
    }

    function getProductActionsHtml(product) {
        if (typeof window.obtenerHtmlAccionesProducto === 'function') {
            return window.obtenerHtmlAccionesProducto(product, product.imagen);
        }

        return '';
    }

    function renderPagination(total, totalPages, from, to) {
        if (!pagination) {
            return;
        }

        pagination.innerHTML = '';
        pagination.hidden = false;

        const info = document.createElement('div');
        info.className = 'bm-products-pagination-info';

        const title = document.createElement('strong');
        title.textContent = 'Mostrando ' + from + ' a ' + to + ' de ' + total + ' productos';

        const subtitle = document.createElement('span');
        subtitle.textContent = getPaginationContextLabel();

        info.appendChild(title);
        info.appendChild(subtitle);

        const controls = document.createElement('div');
        controls.className = 'bm-products-pagination-controls';

        controls.appendChild(createPageButton('Anterior', state.page - 1, state.page <= 1, 'is-prev'));
        getVisiblePages(totalPages).forEach(function (page) {
            controls.appendChild(createPageButton(String(page), page, false, page === state.page ? 'is-active' : ''));
        });

        if (state.page < totalPages) {
            controls.appendChild(createPageButton('Siguiente', state.page + 1, false, 'is-next'));
        }

        pagination.appendChild(info);
        pagination.appendChild(controls);
    }

    function getPaginationContextLabel() {
        if (state.mode === 'categoria') return 'Categoria';
        if (state.mode === 'busqueda') return 'Busqueda';
        if (state.mode === 'ofertas') return 'Promociones';
        return 'Tienda';
    }

    function createPageButton(label, page, disabled, extraClass) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = ('bm-pagination-btn ' + (extraClass || '')).trim();
        button.textContent = label;
        button.setAttribute('data-page', String(page));
        button.disabled = Boolean(disabled);
        if (extraClass === 'is-active') {
            button.setAttribute('aria-current', 'page');
        }
        return button;
    }

    function getVisiblePages(totalPages) {
        const maxNumbers = window.innerWidth <= 767 ? 2 : 3;
        const safeTotalPages = Math.max(1, Number(totalPages) || 1);
        const pageCount = Math.min(maxNumbers, safeTotalPages);

        const start = Math.min(
            Math.max(1, state.page - Math.floor(pageCount / 2)),
            Math.max(1, safeTotalPages - pageCount + 1)
        );

        return Array.from({ length: pageCount }, function (_, index) {
            return start + index;
        });
    }

    function setPaginationVisible(visible) {
        if (pagination) {
            pagination.hidden = !visible;
        }
    }

    function setCountLabel(text) {
        if (countLabel) {
            countLabel.textContent = text;
        }
    }

    function handlePaginationResize() {
        if (!pagination || pagination.hidden) {
            return;
        }

        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(function () {
            const meta = state.meta;
            if (!meta || !meta.total) return;
            renderPagination(meta.total, meta.totalPages, meta.from, meta.to);
        }, 120);
    }

    function syncUrl() {
        const url = new URL(window.location.href);
        const params = new URLSearchParams();

        params.set('idsucursal', state.idsucursal || '4');
        state.page > 1 ? params.set('page', String(state.page)) : params.delete('page');

        state.mode === 'categoria' ? params.set('categoria', state.idcategoria) : params.delete('categoria');
        state.mode === 'busqueda' ? params.set('buscar', state.search) : params.delete('buscar');
        state.mode === 'ofertas' ? params.set('ofertas', '1') : params.delete('ofertas');
        state.priceMin !== null ? params.set('price_min', String(state.priceMin)) : params.delete('price_min');
        state.priceMax !== null ? params.set('price_max', String(state.priceMax)) : params.delete('price_max');

        const query = params.toString();
        window.history.replaceState({}, '', url.pathname + (query ? '?' + query : ''));
    }

    function scrollToShopStart() {
        const shopArea = document.querySelector('.shop-area');
        if (!shopArea) {
            return;
        }

        shopArea.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function initPriceSlider() {
        if (typeof jQuery === 'undefined' || !jQuery('#range-slider').length) {
            return;
        }

        const min = precioMinimo || 0;
        const max = precioMaximo || 100;
        const selectedMin = state.priceMin !== null ? Math.max(min, state.priceMin) : min;
        const selectedMax = state.priceMax !== null ? Math.min(max, state.priceMax) : max;

        if (jQuery('#range-slider').hasClass('ui-slider')) {
            jQuery('#range-slider').slider('destroy');
        }

        jQuery('#range-slider').slider({
            range: true,
            min: min,
            max: max,
            values: [selectedMin, selectedMax],
            slide: function (event, ui) {
                jQuery('#price-amount').val('Q' + ui.values[0] + ' - Q' + ui.values[1]);
            }
        });

        jQuery('#price-amount').val('Q' + selectedMin + ' - Q' + selectedMax);
    }

    function schedulePriceSliderInit() {
        if (typeof window.requestAnimationFrame === 'function') {
            window.requestAnimationFrame(initPriceSlider);
            return;
        }

        window.setTimeout(initPriceSlider, 0);
    }

    function applyPriceFilterFromSlider() {
        if (typeof jQuery === 'undefined' || !jQuery('#range-slider').length) {
            return;
        }

        const values = jQuery('#range-slider').slider('values');
        const min = values[0];
        const max = values[1];

        state.priceMin = min <= precioMinimo ? null : min;
        state.priceMax = max >= precioMaximo ? null : max;
        state.page = 1;

        loadShopProducts(true).then(scrollToShopStart);
    }

    async function loadHomeProducts() {
        if (!homeGrid) {
            return;
        }

        renderHomeMessage('Cargando productos...', 'info');

        try {
            const response = await postJson(API.catalog, {
                mode: 'nuevos',
                page: 1,
                per_page: SHOP_PER_PAGE,
                idsucursal: state.idsucursal || '4'
            });
            if (!response.success || !Array.isArray(response.data)) {
                throw new Error('Respuesta invalida de la API');
            }

            const products = normalizeProducts(response.data).slice(0, SHOP_PER_PAGE);
            homeGrid.innerHTML = '';

            if (!products.length) {
                renderHomeMessage('No hay productos disponibles para mostrar.', 'info');
                return;
            }

            const fragment = document.createDocumentFragment();
            products.forEach(function (product) {
                fragment.appendChild(createProductColumn(product, false));
            });
            homeGrid.appendChild(fragment);
        } catch (error) {
            console.error('Error al cargar productos de inicio:', error);
            renderHomeMessage('Error al cargar los productos. Por favor intenta de nuevo.', 'danger');
        }
    }

    function renderHomeMessage(message, type) {
        if (!homeGrid) {
            return;
        }

        homeGrid.innerHTML = '';
        const column = document.createElement('div');
        column.className = 'col-12';

        const alert = document.createElement('div');
        alert.className = 'alert alert-' + (type || 'info');
        alert.textContent = message;

        column.appendChild(alert);
        homeGrid.appendChild(column);
    }

    window.cargarProductosNuevos = function cargarProductosNuevosV2() {
        if (shopGrid) {
            state.mode = 'nuevos';
            state.page = 1;
            state.idcategoria = '';
            state.search = '';
            state.priceMin = null;
            state.priceMax = null;
            return loadShopProducts(true);
        }

        return loadHomeProducts();
    };

    window.cargarProductosPorCategoria = function cargarProductosPorCategoriaV2(idcategoria, idsucursal) {
        if (!shopGrid) return Promise.resolve();
        state.mode = 'categoria';
        state.page = 1;
        state.idcategoria = cleanText(idcategoria);
        state.idsucursal = cleanText(idsucursal || state.idsucursal || '4');
        state.search = '';
        return loadShopProducts(true);
    };

    window.cargarProductosPorBusqueda = function cargarProductosPorBusquedaV2(termino) {
        if (!shopGrid) return Promise.resolve();
        state.mode = 'busqueda';
        state.page = 1;
        state.search = cleanText(termino);
        state.idcategoria = '';
        return loadShopProducts(true);
    };

    window.inicializarFiltroPrecio = initPriceSlider;
    window.aplicarFiltroPrecio = applyPriceFilterFromSlider;
})(window, document);

