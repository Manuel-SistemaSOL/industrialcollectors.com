// ------------------------------------------------------------
// Inicializacion
// ------------------------------------------------------------
document.addEventListener("DOMContentLoaded", function () {
    inicializarHeroInicio();
    inicializarCategoriasDestacadasInicio();

    if (document.getElementById("productos-nuevos")) {
        cargarProductosInicio({
            contenedorId: "productos-nuevos",
            endpoint: "api/shop_products.php",
            payload: {
                mode: "inicio_nuevos",
                page: 1,
                per_page: 4,
                idsucursal: "4"
            },
            etiqueta: "Nuevo",
            mensajeVacio: "No hay productos nuevos para mostrar.",
            mensajeError: "Error al cargar los productos nuevos."
        });
    }

    if (document.getElementById("productos-mas-vendidos")) {
        cargarProductosInicio({
            contenedorId: "productos-mas-vendidos",
            endpoint: "api/shop_products.php",
            payload: {
                mode: "mas_vendidos",
                page: 1,
                per_page: 4,
                idsucursal: "4"
            },
            etiqueta: "Popular",
            mensajeVacio: "No hay productos para mostrar.",
            mensajeError: "Error al cargar los productos."
        });
    }
});

function inicializarHeroInicio(intento = 0) {
    const hero = document.querySelector(".bm-home-hero-carousel");

    if (!hero || typeof jQuery === "undefined" || typeof jQuery.fn.owlCarousel !== "function") {
        if (hero && intento < 20) {
            setTimeout(function () {
                inicializarHeroInicio(intento + 1);
            }, 150);
        }
        return;
    }

    const $hero = jQuery(hero);

    if ($hero.hasClass("owl-loaded")) {
        return;
    }

    $hero.owlCarousel({
        items: 1,
        loop: true,
        nav: false,
        dots: true,
        autoplay: true,
        autoplayTimeout: 5500,
        autoplayHoverPause: true,
        animateOut: "fadeOut",
        animateIn: "fadeIn",
        smartSpeed: 650
    });
}

function prepararProductosGrid(productos, limite) {
    const lista = Array.isArray(productos) ? productos : [];
    const limiteNumerico = parseInt(limite, 10);
    const limiteSeguro = Number.isFinite(limiteNumerico) && limiteNumerico > 0 ? limiteNumerico : 4;

    return lista
        .map(function (producto) {
            return typeof window.BM_normalizeProductPricing === "function"
                ? window.BM_normalizeProductPricing(producto)
                : producto;
        })
        .slice(0, limiteSeguro);
}

function escaparDatoProducto(valor) {
    return String(valor || "")
        .replace(/\\/g, "\\\\")
        .replace(/'/g, "\\'")
        .replace(/\r?\n/g, " ");
}

function escaparHtmlProducto(valor) {
    return String(valor || "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function resolverImagenProductoInicio(producto) {
    if (typeof window.BM_resolveFirstProductImage === "function") {
        return window.BM_resolveFirstProductImage([producto && producto.imagen, producto && producto.foto]);
    }

    const imagen = String((producto && (producto.imagen || producto.foto)) || "").trim();
    const invalidas = ["", "0", "404", "404.png", "null", "undefined", "nofoto", "nofoto.jpg", "nofoto.png", "no-foto", "no-foto.jpg", "no-foto.png", "sin imagen", "sin-imagen", "sin-imagen.jpg", "sin-imagen.png", "n/a"];

    if (invalidas.includes(imagen.toLowerCase()) || imagen.includes("..")) {
        return window.BM_PRODUCT_IMAGE_FALLBACK || "assets/img/ImagenTemporalINDUSTRIALCOLLECTORS.png";
    }

    if (/^https?:\/\//i.test(imagen)) {
        return imagen;
    }

    return encodeURI("https://ssl.sol.sistemasolgt.com/brovic/files/articulos/" + imagen.replace(/^[/\\]+/, ""));
}

function mostrarEstadoProductos(idContenedor, tipo, mensaje) {
    const contenedor = document.getElementById(idContenedor);

    if (!contenedor) {
        return;
    }

    contenedor.innerHTML = `
        <div class="carousel-product-item">
            <div class="alert alert-${tipo}">${escaparHtmlProducto(mensaje)}</div>
        </div>`;
}

function cargarProductosInicio(config) {
    fetch(config.endpoint, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(config.payload || {})
    })
        .then(function (response) {
            if (!response.ok) {
                throw new Error("HTTP " + response.status);
            }

            return response.json();
        })
        .then(function (data) {
            const contenedor = document.getElementById(config.contenedorId);

            if (!contenedor) {
                return;
            }

            contenedor.innerHTML = "";

            if (!data.success || !Array.isArray(data.data)) {
                mostrarEstadoProductos(config.contenedorId, "warning", config.mensajeVacio);
                return;
            }

            const productos = prepararProductosGrid(data.data, config.limite || 4);

            if (productos.length === 0) {
                mostrarEstadoProductos(config.contenedorId, "warning", config.mensajeVacio);
                return;
            }

            contenedor.innerHTML = productos.map(function (producto) {
                return obtenerHtmlProductoInicio(producto, config.etiqueta);
            }).join("");
        })
        .catch(function (error) {
            console.error("Error al cargar productos de inicio:", error);
            mostrarEstadoProductos(config.contenedorId, "danger", config.mensajeError);
        });
}

function inicializarCategoriasDestacadasInicio() {
    const seccion = document.querySelector(".bm-home-featured-categories");
    const contenedor = document.getElementById("bm-home-category-products");

    if (!seccion || !contenedor) {
        return;
    }

    const tabs = Array.from(seccion.querySelectorAll(".bm-home-featured-tab"));
    const botonesScroll = Array.from(seccion.querySelectorAll("[data-featured-scroll]"));

    if (!tabs.length) {
        return;
    }

    tabs.forEach(function (tab) {
        tab.addEventListener("click", function () {
            cargarCategoriaDestacadaInicio(tab, false);
        });
    });

    botonesScroll.forEach(function (boton) {
        boton.addEventListener("click", function () {
            const direccion = parseInt(boton.getAttribute("data-featured-scroll"), 10) || 1;
            const distancia = Math.max(220, Math.floor(contenedor.clientWidth * 0.85));

            contenedor.scrollBy({
                left: distancia * direccion,
                behavior: "smooth"
            });
        });
    });

    cargarCategoriaDestacadaInicio(tabs.find(function (tab) {
        return tab.classList.contains("is-active");
    }) || tabs[0], false);
}

function activarCategoriaDestacadaInicio(tabActiva) {
    const seccion = tabActiva.closest(".bm-home-featured-categories");

    if (!seccion) {
        return;
    }

    seccion.querySelectorAll(".bm-home-featured-tab").forEach(function (tab) {
        const activa = tab === tabActiva;
        tab.classList.toggle("is-active", activa);
        tab.setAttribute("aria-selected", activa ? "true" : "false");
    });

    const enlaceVerTodo = document.getElementById("bm-home-category-more");
    const href = tabActiva.getAttribute("data-category-href");

    if (enlaceVerTodo && href) {
        enlaceVerTodo.setAttribute("href", href);
    }

    const tabsWrapper = tabActiva.closest(".bm-home-featured-tabs-wrap");

    if (tabsWrapper && tabsWrapper.scrollWidth > tabsWrapper.clientWidth) {
        tabActiva.scrollIntoView({
            behavior: "smooth",
            block: "nearest",
            inline: "center"
        });
    }
}

function obtenerPayloadCategoriaDestacada(tab, usarBusqueda) {
    const modoCategoria = tab.getAttribute("data-category-mode") || "busqueda";
    const idcategoria = tab.getAttribute("data-category-id") || "";
    const busqueda = tab.getAttribute("data-category-search") || tab.getAttribute("data-category-label") || "";
    const usarCategoria = !usarBusqueda && modoCategoria === "categoria" && idcategoria !== "";
    const payload = {
        mode: usarCategoria ? "categoria" : "busqueda",
        page: 1,
        per_page: 8,
        idsucursal: "4"
    };

    if (usarCategoria) {
        payload.idcategoria = idcategoria;
    } else {
        payload.search = busqueda;
    }

    return payload;
}

function cargarCategoriaDestacadaInicio(tab, usarBusqueda) {
    const contenedorId = "bm-home-category-products";
    const contenedor = document.getElementById(contenedorId);

    if (!tab || !contenedor) {
        return;
    }

    activarCategoriaDestacadaInicio(tab);
    contenedor.scrollTo({ left: 0, behavior: "auto" });
    mostrarEstadoProductos(contenedorId, "info", "Cargando productos...");

    const payload = obtenerPayloadCategoriaDestacada(tab, usarBusqueda);
    const etiqueta = tab.getAttribute("data-category-label") || "esta categoría";

    fetch("api/shop_products.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
    })
        .then(function (response) {
            if (!response.ok) {
                throw new Error("HTTP " + response.status);
            }

            return response.json();
        })
        .then(function (data) {
            if (!data.success || !Array.isArray(data.data)) {
                throw new Error("Respuesta invalida");
            }

            const productos = prepararProductosGrid(data.data, 8);

            if (productos.length === 0 && payload.mode === "categoria" && !usarBusqueda) {
                cargarCategoriaDestacadaInicio(tab, true);
                return;
            }

            if (productos.length === 0) {
                mostrarEstadoProductos(contenedorId, "warning", "No hay productos para mostrar en " + etiqueta + ".");
                return;
            }

            contenedor.innerHTML = productos.map(function (producto) {
                return obtenerHtmlProductoInicio(producto, "");
            }).join("");
        })
        .catch(function (error) {
            if (payload.mode === "categoria" && !usarBusqueda) {
                cargarCategoriaDestacadaInicio(tab, true);
                return;
            }

            console.error("Error al cargar categoria destacada:", error);
            mostrarEstadoProductos(contenedorId, "danger", "Error al cargar los productos de " + etiqueta + ".");
        });
}

function obtenerHtmlProductoInicio(producto, etiqueta) {
    const imagen = resolverImagenProductoInicio(producto);
    const fallback = window.BM_PRODUCT_IMAGE_FALLBACK || "assets/img/ImagenTemporalINDUSTRIALCOLLECTORS.png";
    const claseImagenFallback = typeof window.BM_isProductImageFallbackUrl === "function" && window.BM_isProductImageFallbackUrl(imagen)
        ? " class=\"bm-product-image-fallback\""
        : "";
    const nombreProducto = String(producto.nombre || "");
    const descripcionProducto = String(producto.descripcion || "");
    const nombreSeguro = escaparDatoProducto(nombreProducto);
    const descripcionSegura = escaparDatoProducto(descripcionProducto);
    const nombreHtml = escaparHtmlProducto(nombreProducto);
    const etiquetaHtml = escaparHtmlProducto(etiqueta || "");
    const productoAgotado = typeof window.BM_isProductOutOfStock === "function"
        ? window.BM_isProductOutOfStock(producto)
        : false;
    const etiquetaProductoHtml = productoAgotado && typeof window.BM_getOutOfStockRibbonHtml === "function"
        ? window.BM_getOutOfStockRibbonHtml(producto)
        : (etiquetaHtml ? `<div class="tag">${etiquetaHtml}</div>` : "");

    return `
        <div class="carousel-product-item">
            <div class="single-arrivals-products${productoAgotado ? " is-out-of-stock" : ""}">

                <div class="arrivals-products-image">
                    <a href="javascript:void(0)"
                    onclick="vistaRapida(
                        '${nombreSeguro}',
                        '${imagen}',
                        '${producto.precio_venta}',
                        '${producto.idarticulo}',
                        '${producto.codigo}',
                        '${descripcionSegura}',
                        '${producto.stock}'
                    )">
                        <img src="${imagen}" alt="${nombreHtml}"${claseImagenFallback} onerror="window.BM_applyProductImageFallback ? window.BM_applyProductImageFallback(this) : (this.onerror=null,this.src='${fallback}')">
                    </a>
                    ${etiquetaProductoHtml}

                    <ul class="arrivals-action">
                        <li>
                            <a href="javascript:void(0)"
                            onclick="agregarAlCarrito(
                                '${producto.idarticulo}',
                                '${nombreSeguro}',
                                '${producto.precio_venta}',
                                '${imagen}',
                                '${descripcionSegura}'
                            )">
                                <i class="flaticon-shopping-cart"></i>
                            </a>
                        </li>
                        <li><a href="javascript:void(0)"
                            onclick="vistaRapida(
                                '${nombreSeguro}',
                                '${imagen}',
                                '${producto.precio_venta}',
                                '${producto.idarticulo}',
                                '${producto.codigo}',
                                '${descripcionSegura}',
                                '${producto.stock}'
                            )">
                            <i class="flaticon-view"></i></a>
                        </li>
                    </ul>
                </div>

                <div class="arrivals-products-content">
                    <h3 class="product-title-limit">${nombreHtml}</h3>

                    <ul class="rating">
                        <li><i class='bx bxs-star'></i></li>
                        <li><i class='bx bxs-star'></i></li>
                        <li><i class='bx bxs-star'></i></li>
                        <li><i class='bx bxs-star'></i></li>
                        <li><i class='bx bxs-star'></i></li>
                    </ul>

                    ${obtenerHtmlPreciosProducto(producto)}
                    ${obtenerHtmlAccionesProducto(producto, imagen)}
                </div>

            </div>
        </div>
    `;
}
