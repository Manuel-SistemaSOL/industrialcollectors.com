// Shared product renderer for special listing pages such as Gift Card and Promociones.
(function (window, document) {
    "use strict";

    const DEFAULT_LIMIT = 30;
    const DEFAULT_IMAGE_BASE_URL = "https://ssl.sol.sistemasolgt.com/brovic/files/articulos/";

    function html(value) {
        return String(value ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;");
    }

    function inline(value) {
        return html(
            String(value ?? "")
                .replace(/\\/g, "\\\\")
                .replace(/[\r\n]+/g, " ")
                .replace(/'/g, "\\'")
        );
    }

    function fallbackImage() {
        return window.BM_PRODUCT_IMAGE_FALLBACK || "assets/img/ImagenTemporalLABROCA.png";
    }

    function productImage(product, imageBaseUrl) {
        if (typeof window.BM_resolveFirstProductImage === "function") {
            return window.BM_resolveFirstProductImage([product && product.imagen, product && product.foto], imageBaseUrl);
        }

        const imageName = String((product && product.imagen) || "").trim();

        if (imageName && imageName !== "0") {
            return `${imageBaseUrl}${imageName}`;
        }

        return fallbackImage();
    }

    function formatPrice(value) {
        if (typeof window.formatearPrecioProducto === "function") {
            return window.formatearPrecioProducto(value);
        }

        const numericValue = parseFloat(value);
        return Number.isFinite(numericValue) ? numericValue.toFixed(2) : "0.00";
    }

    function numericPrice(value) {
        if (typeof value === "number") {
            return Number.isFinite(value) ? value : NaN;
        }

        const normalizedValue = String(value ?? "")
            .replace(/[^\d.,-]/g, "")
            .replace(/,/g, "");
        const numericValue = parseFloat(normalizedValue);

        return Number.isFinite(numericValue) ? numericValue : NaN;
    }

    function normalizeProductPricing(product) {
        if (typeof window.BM_normalizeProductPricing === "function") {
            return window.BM_normalizeProductPricing(product);
        }

        return product;
    }

    function productMatchesFilters(product, options) {
        if (typeof options.productFilter === "function" && !options.productFilter(product)) {
            return false;
        }

        const maxDisplayPrice = numericPrice(options.maxDisplayPrice);

        if (!Number.isFinite(maxDisplayPrice)) {
            return true;
        }

        const productPrice = typeof window.BM_getProductEffectivePrice === "function"
            ? window.BM_getProductEffectivePrice(product)
            : numericPrice(product && product.precio_venta);

        return Number.isFinite(productPrice) && productPrice > 0 && productPrice <= maxDisplayPrice;
    }

    function prices(product) {
        if (typeof window.obtenerHtmlPreciosProducto === "function") {
            return window.obtenerHtmlPreciosProducto(product);
        }

        return `
            <span class="product-card-price product-card-price-normal"><span class="product-card-price-label">Precio: </span>Q${formatPrice(product.precio_venta)}</span>
        `;
    }

    function actions(product, imageUrl) {
        if (typeof window.obtenerHtmlAccionesProducto === "function") {
            return window.obtenerHtmlAccionesProducto(product, imageUrl);
        }

        return `
            <div class="product-card-actions">
                <a class="product-card-action product-card-action-cart" href="javascript:void(0)"
                    onclick="agregarAlCarrito(
                        '${inline(product.idarticulo)}',
                        '${inline(product.nombre)}',
                        '${inline(product.precio_venta)}',
                        '${inline(imageUrl)}',
                        '${inline(product.descripcion)}',
                        1
                    )">
                    Agregar al carrito
                </a>
                <a class="product-card-action product-card-action-details" href="producto.php?id=${encodeURIComponent(String(product.idarticulo || ''))}">
                    Ver m&aacute;s
                </a>
            </div>
        `;
    }

    function productCard(product, options) {
        const imageUrl = productImage(product, options.imageBaseUrl);
        const imageFallback = fallbackImage();
        const imageFallbackAttribute = typeof window.BM_isProductImageFallbackUrl === "function" && window.BM_isProductImageFallbackUrl(imageUrl)
            ? ' class="bm-product-image-fallback"'
            : "";
        const productId = String(product.idarticulo || "");
        const productUrl = `producto.php?id=${encodeURIComponent(productId)}`;
        const productName = product.nombre || "Producto";
        const outOfStock = typeof window.BM_isProductOutOfStock === "function"
            ? window.BM_isProductOutOfStock(product)
            : false;
        const productTag = outOfStock && typeof window.BM_getOutOfStockRibbonHtml === "function"
            ? window.BM_getOutOfStockRibbonHtml(product)
            : `<div class="tag">${html(options.tagText)}</div>`;

        return `
            <div class="bm-shop-product-col col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="single-shop-products${outOfStock ? " is-out-of-stock" : ""}">
                    <div class="shop-products-image">
                        <a href="${html(productUrl)}">
                            <img${imageFallbackAttribute} src="${html(imageUrl)}" alt="${html(productName)}" onerror="window.BM_applyProductImageFallback ? window.BM_applyProductImageFallback(this) : (this.onerror=null,this.src='${html(imageFallback)}')">
                        </a>
                        ${productTag}
                        <ul class="shop-action">
                            <li>
                                <a href="javascript:void(0)"
                                    onclick="agregarAlCarrito(
                                        '${inline(product.idarticulo)}',
                                        '${inline(product.nombre)}',
                                        '${inline(product.precio_venta)}',
                                        '${inline(imageUrl)}',
                                        '${inline(product.descripcion)}',
                                        1
                                    )">
                                    <i class="flaticon-shopping-cart"></i>
                                </a>
                            </li>
                            <li>
                                <a href="${html(productUrl)}">
                                    <i class="flaticon-view"></i>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="shop-products-content">
                        <h3>
                            <a href="${html(productUrl)}">${html(productName)}</a>
                        </h3>
                        <ul class="rating">
                            <li><i class='bx bxs-star'></i></li>
                            <li><i class='bx bxs-star'></i></li>
                            <li><i class='bx bxs-star'></i></li>
                            <li><i class='bx bxs-star'></i></li>
                            <li><i class='bx bxs-star'></i></li>
                        </ul>
                        ${prices(product)}
                        ${actions(product, imageUrl)}
                    </div>
                </div>
            </div>
        `;
    }

    function updateCount(total) {
        const counter = document.getElementById("bm-products-count");

        if (counter) {
            counter.textContent = total > 0 ? `${total} productos` : "";
        }
    }

    function loadSpecialProducts(config) {
        const options = Object.assign({
            apiUrl: "",
            imageBaseUrl: DEFAULT_IMAGE_BASE_URL,
            productLimit: DEFAULT_LIMIT,
            tagText: "Producto",
            errorLabel: "productos",
            emptyMessage: "No hay productos para mostrar.",
            maxDisplayPrice: null,
            productFilter: null
        }, config || {});

        const container = document.getElementById("products-collections-filter");

        if (!container || !options.apiUrl) {
            return;
        }

        fetch(options.apiUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({})
        })
            .then(response => response.json())
            .then(data => {
                container.innerHTML = "";

                if (!data.success || !Array.isArray(data.data) || data.data.length === 0) {
                    updateCount(0);
                    container.innerHTML = `
                        <div class="col-12">
                            <div class="alert alert-warning">${html(options.emptyMessage)}</div>
                        </div>`;
                    return;
                }

                const normalizedProducts = data.data.map(normalizeProductPricing);
                const filteredProducts = normalizedProducts.filter(product => productMatchesFilters(product, options));

                if (filteredProducts.length === 0) {
                    updateCount(0);
                    container.innerHTML = `
                        <div class="col-12">
                            <div class="alert alert-warning">${html(options.emptyMessage)}</div>
                        </div>`;
                    return;
                }

                const products = filteredProducts.slice(0, options.productLimit);
                container.innerHTML = products.map(product => productCard(product, options)).join("");
                updateCount(products.length);
            })
            .catch(error => {
                console.error(`Error al cargar ${options.errorLabel}:`, error);
                updateCount(0);
                container.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-danger">Error al cargar los productos.</div>
                    </div>`;
            });
    }

    window.bmLoadSpecialProducts = loadSpecialProducts;
})(window, document);
