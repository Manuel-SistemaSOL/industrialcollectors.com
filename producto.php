<?php

require_once __DIR__ . '/assets/php/category_visuals.php';
$current_page = basename($_SERVER['PHP_SELF']);

require_once __DIR__ . '/assets/php/product_seo.php';
$idarticulo = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 999999]]);
if (!$idarticulo) {
    bm_product_error(404);
}
$ch = curl_init('https://ssl.sol.sistemasolgt.com/induscollesitio/api/api_tienda_articulos_listarid.php');
curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode(['idarticulo' => $idarticulo]), CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_PROXY => '', CURLOPT_NOPROXY => '*', CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => 20, CURLOPT_HTTPHEADER => ['Content-Type: application/json']]);
$api_response = curl_exec($ch);
$api_status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$producto_data = json_decode((string) $api_response, true);
if ($api_response === false || $api_status !== 200 || !is_array($producto_data) || !isset($producto_data['success'])) {
    bm_product_error(503);
}
if (empty($producto_data['data']) && !empty($producto_data['success'])) {
    bm_product_error(404);
}
if (empty($producto_data['success'])) {
    bm_product_error(503);
}
$productoSeo = $producto_data['data'][0] ?? [];
if ((int) ($productoSeo['idarticulo'] ?? 0) !== $idarticulo || empty($productoSeo['nombre'])) {
    bm_product_error(503);
}

$nombreProductoSeo = trim((string) ($productoSeo['nombre'] ?? 'Producto'));
$descripcionProductoSeo = trim((string) ($productoSeo['descripcion'] ?? ''));

$seo_title = $nombreProductoSeo . ' | Industrial Collectors, S.A.';

$seo_description = $descripcionProductoSeo !== ''
    ? mb_substr(strip_tags($descripcionProductoSeo), 0, 155)
    : 'Consulta información, precio y disponibilidad de este producto en Industrial Collectors, S.A.';

$seo_canonical_url =
    'https://industrialcollectors.com/producto.php?id=' .
    rawurlencode((string) $idarticulo);

$seo_robots = 'index, follow, max-image-preview:large';

$precioProductoSeo = (float) ($productoSeo['precio_09'] ?? $productoSeo['precio09'] ?? $productoSeo['precio9'] ?? $productoSeo['precio_oferta'] ?? 0);
if ($precioProductoSeo <= 0) {
    $precioProductoSeo = (float) ($productoSeo['precio_venta'] ?? $productoSeo['precio'] ?? 0);
}
$imagenProductoSeo = trim((string) ($productoSeo['imagen'] ?? ''));
if ($imagenProductoSeo !== '' && !preg_match('~^https?://~i', $imagenProductoSeo)) {
    $imagenProductoSeo = 'https://ssl.sol.sistemasolgt.com/brovic/files/articulos/' . rawurlencode(basename($imagenProductoSeo));
}
if ($imagenProductoSeo !== '') {
    $seo_image = $imagenProductoSeo;
}
$stockProductoSeo = $productoSeo['stock'] ?? null;
$disponibilidadProductoSeo = is_numeric($stockProductoSeo) ? ((float) $stockProductoSeo > 0 ? 'En stock' : 'Agotado') : 'Consultar disponibilidad';
$schemaProducto = [
    '@context' => 'https://schema.org', '@type' => 'Product',
    'name' => $nombreProductoSeo, 'sku' => (string) $idarticulo,
    'url' => $seo_canonical_url,
];
if ($descripcionProductoSeo !== '') {
    $schemaProducto['description'] = strip_tags($descripcionProductoSeo);
}
if ($imagenProductoSeo !== '') {
    $schemaProducto['image'] = $imagenProductoSeo;
}
if ($precioProductoSeo > 0) {
    $schemaProducto['offers'] = ['@type' => 'Offer', 'url' => $seo_canonical_url, 'priceCurrency' => 'GTQ', 'price' => number_format($precioProductoSeo, 2, '.', '')];
    if (is_numeric($stockProductoSeo)) {
        $schemaProducto['offers']['availability'] = 'https://schema.org/' . ((float) $stockProductoSeo > 0 ? 'InStock' : 'OutOfStock');
    }
}
$extra_head_content = '<script type="application/ld+json">' . json_encode($schemaProducto, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';

include 'head.php';

?>

<!-- End Support Area -->
<style>
    .arrivals-products-image {
        width: 100%;
        aspect-ratio: 16 / 9;
        overflow: hidden;
        position: relative;
    }

    .arrivals-products-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center;
        display: block;
    }

    .arrivals-products-content h3 {
        font-size: 18px;
        font-weight: 600;
        white-space: nowrap;
        /* 🔥 Evita salto de línea */
        overflow: hidden;
        /* Oculta texto excesivo */
        text-overflow: ellipsis;
        /* Añade "..." */
        margin-bottom: 5px;
    }

    /* Estilos para tarjetas de producto - imagen compacta y uniforme */
    .shop-products-image {
        width: 100%;
        aspect-ratio: 16 / 9;
        overflow: hidden;
        position: relative;
    }

    .shop-products-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center;
        display: block;
    }

    .shop-products-content h3 {
        font-size: 18px;
        font-weight: 600;
        white-space: nowrap;
        /* 🔥 Evita salto de línea */
        overflow: hidden;
        /* Oculta texto excesivo */
        text-overflow: ellipsis;
        /* Añade "..." */
        margin-bottom: 5px;
    }

    /* Estilos para el botón de limpiar búsqueda */
    .search-form {
        position: relative;
    }

    .btn-limpiar-busqueda {
        position: absolute;
        right: 45px;
        top: 50%;
        transform: translateY(-50%);
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        padding: 0;
        font-size: 16px;
        transition: all 0.3s;
        z-index: 10;
    }

    .btn-limpiar-busqueda:hover {
        background: #c82333;
        transform: translateY(-50%) scale(1.1);
    }

    .btn-limpiar-busqueda i {
        line-height: 1;
    }

    /* Estilos para el slider de imágenes del producto */
    .main-products-image {
        position: relative;
    }

    /* Imagen principal arriba */
    .slider-for {
        margin-bottom: 15px;
    }

    .slider-for img {
        width: 100%;
        height: auto;
        max-height: 500px;
        object-fit: contain;
        display: block;
        border-radius: 8px;
        background: #fff;
    }

    /* Miniaturas horizontales abajo */
    .slider-nav {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: wrap !important;
        gap: 10px;
        justify-content: center;
        align-items: center;
        margin-top: 15px;
        width: 100%;
    }

    .slider-nav>div {
        width: 80px !important;
        height: 80px !important;
        min-width: 80px !important;
        min-height: 80px !important;
        border: 2px solid transparent;
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.3s;
        opacity: 0.7;
        background: #f5f5f5;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .slider-nav>div:hover {
        opacity: 1;
        border-color: #1D1D1B;
        transform: scale(1.05);
    }

    .slider-nav>div.active {
        border-color: #1D1D1B;
        opacity: 1;
        box-shadow: 0 2px 8px rgba(21, 21, 21, 0.24);
    }

    .slider-nav>div img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Responsive para móviles */
    @media (max-width: 768px) {
        .slider-nav {
            overflow-x: auto;
            padding-bottom: 10px;
            justify-content: flex-start;
        }

        .slider-nav>div {
            width: 70px;
            height: 70px;
        }

        .slider-for img {
            max-height: 350px;
        }
    }

    /* Cuando slick está activo, ocultar estilos manuales */
    .slider-for.slick-initialized>div {
        display: block !important;
    }


    /* =========================================
   MINIATURAS PRODUCTO - ESTILO DESEADO
========================================= */

    #slider-nav {
        display: flex !important;
        flex-direction: row !important;
        gap: 10px;
        justify-content: center;
        align-items: center;
        margin-top: 15px;
        width: 100%;
    }

    /* Cada miniatura */
    #slider-nav .thumb-item {
        width: 70px;
        height: 70px;
        border-radius: 10px;
        overflow: hidden;
        border: 2px solid transparent;
        cursor: pointer;
        opacity: 0.6;
        transition: all 0.25s ease;
        background: #f5f5f5;
    }

    /* Imagen dentro */
    #slider-nav .thumb-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Hover */
    #slider-nav .thumb-item:hover {
        opacity: 1;
        transform: scale(1.05);
    }

    /* Activa */
    #slider-nav .thumb-item.active {
        border-color: #1D1D1B;
        opacity: 1;
        box-shadow: 0 2px 8px rgba(21, 21, 21, .24);
    }

    /* ===== CATEGORIAS SIDEBAR ===== */
    .widget.widget_categories .categories li a.nav-link {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 16px 18px;
        border: 1px solid #e9e9e9;
        background: #fff;
        color: #000;
        transition: all 0.3s ease;
    }

    .widget.widget_categories .categories li a.nav-link i {
        color: #000;
    }

    .widget.widget_categories .categories li a.nav-link:hover {
        background: #F3F5F7;
        border-color: #009FE3;
        color: #000;
    }

    .widget.widget_categories .categories li a.nav-link.active {
        background: #F3F5F7;
        border-color: #009FE3;
        color: #000;
        box-shadow: inset 3px 0 0 #1D71B8;
    }

    .widget.widget_categories .categories li a.nav-link:hover i,
    .widget.widget_categories .categories li a.nav-link.active i {
        color: #000;
    }

    /* Responsive del producto: categorías accesibles y contenido completo. */
    @media (max-width: 991px) {
        .shop-area .col-lg-4.col-md-12 {
            display: block !important;
            flex: 0 0 100% !important;
            max-width: 100% !important;
            width: 100% !important;
            margin-bottom: 18px;
        }

        .shop-area .widget-area {
            display: block !important;
            margin-bottom: 0;
        }

        .shop-area .widget {
            margin-bottom: 0;
        }

        .shop-area .widget.widget_price {
            display: none;
        }

        .shop-area .widget-title {
            font-size: 18px;
            margin-bottom: 12px;
            padding-bottom: 8px;
        }

        .widget.widget_categories .categories {
            display: flex;
            gap: 10px;
            margin: 0;
            overflow-x: auto;
            padding: 0 0 8px;
            scroll-snap-type: x proximity;
            -webkit-overflow-scrolling: touch;
        }

        .widget.widget_categories .categories li {
            flex: 0 0 auto;
            margin: 0 !important;
            scroll-snap-align: start;
        }

        .widget.widget_categories .categories li a.nav-link {
            border-radius: 4px;
            font-size: 12px;
            min-height: 42px;
            padding: 10px 12px;
            white-space: nowrap;
        }

        .shop-area .col-lg-8.col-md-12 {
            width: 100% !important;
            flex: 0 0 100% !important;
            max-width: 100% !important;
        }

        .products-details-area {
            padding: 30px 0 !important;
        }

        .slider-for img {
            max-height: 330px;
        }
    }

    @media (max-width: 768px) {
        #slider-nav {
            flex-wrap: nowrap !important;
            overflow-x: auto;
            overflow-y: hidden;
            justify-content: flex-start;
            padding-bottom: 8px;
        }

        #slider-nav .thumb-item,
        .slider-nav>div.thumb-item {
            width: 52px !important;
            height: 52px !important;
            flex-shrink: 0;
            min-width: 52px !important;
            min-height: 52px !important;
        }

        .widget.widget_categories .categories li a.nav-link {
            font-size: 11px;
            min-height: 38px;
            padding: 8px 10px;
        }

    }
</style>

<!-- Start Page Banner -->
<div class="page-title-area">
    <div class="container">
        <div class="page-title-content">
            <h2>Tienda</h2>

            <ul>
                <li><a href="/">Inicio</a></li>
                <li>Tienda</li>
            </ul>
        </div>
    </div>
</div>
<!-- End Page Banner -->
<!-- Start Shop Area -->
<section class="shop-area bg-ffffff pt-50 pb-50">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-12">
                <aside class="widget-area">


                    <div class="widget widget_categories bm-category-filter">
                        <h3 class="widget-title">Categorías</h3>

                        <button class="bm-category-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#bm-product-category-list" aria-expanded="false" aria-controls="bm-product-category-list">
                            <span class="bm-category-toggle-main">
                                <span class="bm-category-toggle-icon">
                                    <i class="bx bx-category-alt" aria-hidden="true"></i>
                                </span>
                                <span class="bm-category-toggle-copy">
                                    <span>Categoría actual</span>
                                    <strong>Seleccionar categoría</strong>
                                </span>
                            </span>
                            <i class="bx bx-chevron-down bm-category-toggle-chevron" aria-hidden="true"></i>
                        </button>

                        <ul class="categories bm-category-menu collapse" id="bm-product-category-list">
                            <?php
                            $index = 0;
                            foreach ($categorias as $cat):
                                // Solo mostrar categorías activas (condicion == 1)
                                if (isset($cat['condicion']) && $cat['condicion'] == 1):
                                    $icono = bm_category_icon_class($cat['nombre'] ?? '');
                            ?>
                                    <li>
                                        <a href="tienda.php?categoria=<?= $cat['idcategoria'] ?>" class="nav-link">
                                            <i class="<?= htmlspecialchars($icono, ENT_QUOTES) ?>" aria-hidden="true"></i>
                                            <span><?= htmlspecialchars($cat['nombre']) ?></span>
                                        </a>
                                    </li>
                                <?php
                                    $index++;
                                endif;
                            endforeach;

                            // Si no hay categorías, mostrar mensaje
                            if (empty($categorias) || $index == 0):
                                ?>
                                <li>
                                    <div class="alert alert-info" style="padding: 10px; margin: 0;">
                                        No hay categorías disponibles.
                                    </div>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="widget widget_price">
                        <h3 class="widget-title">Precio</h3>

                        <form class="price-range-content">
                            <div class="price-range-bar" id="range-slider"></div>
                            <div class="price-range-filter">
                                <div class="price-range-filter-item d-flex align-items-center order-1 order-xl-2">
                                    <h4>Rango:</h4>
                                    <input type="text" id="price-amount" readonly>
                                </div>

                                <div class="price-range-filter-item price-range-filter-button order-2 order-xl-1">
                                    <button type="button" class="btn btn-red btn-icon">Filtrar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </aside>
            </div>

            <div class="col-lg-8 col-md-12">
                <!-- Start Products Details Area -->
                <section class="products-details-area ptb-50">
                    <div class="container">
                        <div id="producto-loading" style="display: none; text-align: center; padding: 50px;">
                            <p>Cargando producto...</p>
                        </div>
                        <div id="producto-error" style="display: none; text-align: center; padding: 50px;">
                            <p style="color: red;">Error al cargar el producto. Por favor, intenta de nuevo.</p>
                        </div>
                        <div id="producto-details" class="products-details-desc">
                            <div class="row align-items-center">
                                <div class="col-lg-6 col-md-6">
                                    <div class="main-products-image">
                                        <div class="slider slider-for" id="slider-for"><?php if ($imagenProductoSeo !== ''): ?><div><img src="<?= bm_product_escape($imagenProductoSeo) ?>" alt="<?= bm_product_escape($nombreProductoSeo) ?>"></div><?php endif; ?></div>
                                        <div class="slider slider-nav" id="slider-nav"></div>
                                    </div>
                                </div>

                                <div class="col-lg-6 col-md-6">
                                    <div class="product-content content-two">
                                        <h1 id="producto-nombre" style="font-size: 24px"><?= bm_product_escape($nombreProductoSeo) ?></h1>

                                        <div class="product-review">
                                            <div class="rating">
                                                <i class='bx bxs-star'></i>
                                                <i class='bx bxs-star'></i>
                                                <i class='bx bxs-star'></i>
                                                <i class='bx bxs-star'></i>
                                                <i class='bx bxs-star'></i>
                                            </div>
                                        </div>

                                        <div class="price">
                                            <span class="new-price" id="producto-precio"><?= $precioProductoSeo > 0 ? 'Precio: Q' . number_format($precioProductoSeo, 2) : 'Consultar precio' ?></span>
                                        </div>
                                        <p id="producto-descripcion"><?= bm_product_escape($descripcionProductoSeo) ?></p>

                                        <ul class="products-info">
                                            <li><span>Disponibilidad:</span> <span id="producto-stock"><?= bm_product_escape($disponibilidadProductoSeo) ?></span></li>
                                            <li><span>SKU:</span> <span id="producto-sku"><?= (int) $idarticulo ?></span></li>
                                            <li><span>Código:</span> <span id="producto-codigo"></span></li>
                                            <li><span>Categoría:</span> <span id="producto-categoria"></span></li>
                                        </ul>

                                        <div class="product-quantities">
                                            <span>Cantidad:</span>

                                            <div class="input-counter">
                                                <span class="minus-btn" onclick="cambiarCantidad(-1)">
                                                    <i class='bx bx-minus'></i>
                                                </span>
                                                <input type="text" id="producto-cantidad" value="1" inputmode="numeric" pattern="[0-9]*" min="1" max="99" aria-label="Cantidad del producto">
                                                <span class="plus-btn" onclick="cambiarCantidad(1)">
                                                    <i class='bx bx-plus'></i>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="product-add-to-cart">
                                            <button type="button" id="producto-add-cart-btn" class="default-btn" onclick="agregarAlCarritoDesdeProducto()">
                                                <i class="flaticon-shopping-cart"></i>
                                                Agregar al carrito
                                                <span></span>
                                            </button>
                                        </div>

                                        <div class="products-share">
                                            <ul class="social">
                                                <li><span>Compartir:</span></li>
                                                <li>
                                                    <button id="btn-compartir-whatsapp-producto" class="mp-btn-compartir mp-whatsapp" onclick="compartirWhatsAppProducto(event)" style="background: #25D366; color: white; border: none; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer;" title="Compartir por WhatsApp">
                                                        <i class='bx bxl-whatsapp'></i>
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="products-details-tabs" id="producto-tabs" style="display: none;">
                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                <li class="nav-item"><a class="nav-link active" id="description-tab" data-bs-toggle="tab" href="#description" role="tab" aria-controls="description">Descripción</a></li>
                                <li class="nav-item"><a class="nav-link" id="information-tab" data-bs-toggle="tab" href="#information" role="tab" aria-controls="information">Información</a></li>
                            </ul>

                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade show active" id="description" role="tabpanel">
                                    <h2>Descripción del Producto</h2>
                                    <div id="producto-descripcion-completa"></div>
                                </div>

                                <div class="tab-pane fade" id="information" role="tabpanel">
                                    <ul class="information-list" id="producto-informacion"></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- End Products Details Area -->

            </div>
        </div>
    </div>   
</section>
<!-- End Shop Area -->














<!-- Start Footer Area -->

<?php include 'footer.php'; ?>
<script type="text/javascript" src="assets/js/sweatlert.js"></script>
<script>
    // Variables globales para el producto
    let productoActual = null;
    let imagenPrincipal = null; // Imagen principal del producto
    let imagenesProducto = []; // Fotos adicionales de la API
    let imagenIndex = 0; // Índice de la imagen actual mostrada

    function resolverImagenProductoDetalle() {
        if (typeof window.BM_resolveFirstProductImage === 'function') {
            return window.BM_resolveFirstProductImage(Array.prototype.slice.call(arguments));
        }

        const fallback = window.BM_PRODUCT_IMAGE_FALLBACK || 'assets/img/ImagenTemporalINDUSTRIALCOLLECTORS.png';
        const base = 'https://ssl.sol.sistemasolgt.com/brovic/files/articulos/';

        for (let index = 0; index < arguments.length; index += 1) {
            const raw = String(arguments[index] ?? '').trim();

            if (!raw || ['0', 'null', 'undefined', 'nofoto', 'nofoto.png', 'n/a'].includes(raw.toLowerCase()) || raw.includes('..')) {
                continue;
            }

            if (/^https?:\/\//i.test(raw)) {
                return raw;
            }

            return encodeURI(base + raw.replace(/^[/\\]+/, ''));
        }

        return fallback;
    }

    function rutaFotoProductoValida(ruta) {
        if (typeof window.BM_isInvalidProductImage === 'function' && window.BM_isInvalidProductImage(ruta)) {
            return false;
        }

        const raw = String(ruta ?? '').trim();
        return raw !== '' && !raw.includes('..');
    }

    function resolverRutaFotoProducto(ruta) {
        if (!rutaFotoProductoValida(ruta)) {
            return '';
        }

        let raw = String(ruta).trim();

        if (/^https?:\/\//i.test(raw)) {
            return raw.replace(/ /g, '%20');
        }

        if (raw.startsWith('/')) {
            return ('https://ssl.sol.sistemasolgt.com' + raw).replace(/ /g, '%20');
        }

        raw = raw.replace(/^\//, '');
        return ('https://ssl.sol.sistemasolgt.com/brovic/' + raw).replace(/ /g, '%20');
    }

    function aplicarImagenTemporalProducto(img) {
        if (typeof window.BM_applyProductImageFallback === 'function') {
            window.BM_applyProductImageFallback(img);
            return;
        }

        if (img) {
            img.onerror = null;
            img.classList.add('bm-product-image-fallback');
            img.src = window.BM_PRODUCT_IMAGE_FALLBACK || 'assets/img/ImagenTemporalINDUSTRIALCOLLECTORS.png';
        }
    }

    // ============================================================
    // 🔒 VALIDAR ID DEL PRODUCTO
    // ============================================================
    function validarIdProducto(id) {
        // Solo permitir números enteros positivos
        if (!id || typeof id !== 'string' && typeof id !== 'number') {
            return false;
        }

        // Convertir a string y validar formato
        const idStr = String(id).trim();

        // Verificar que solo contiene números
        if (!/^[0-9]+$/.test(idStr)) {
            return false;
        }

        // Convertir a número y validar rango
        const idNum = parseInt(idStr, 10);
        if (isNaN(idNum) || idNum < 1 || idNum > 999999) {
            return false;
        }

        return true;
    }

    // ============================================================
    // CARGAR PRODUCTO AL INICIAR LA PÁGINA
    // ============================================================
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const idarticulo = urlParams.get('id');

        // Validar ID antes de procesar
        if (!idarticulo || !validarIdProducto(idarticulo)) {
            document.getElementById('producto-loading').style.display = 'none';
            document.getElementById('producto-error').style.display = 'block';
            document.getElementById('producto-error').innerHTML =
                '<p style="color: red;">ID de producto inválido. Por favor, selecciona un producto válido.</p>' +
                '<p><a href="/" class="default-btn">Volver al inicio</a></p>';

            // Limpiar la URL para evitar que se vea el ID inválido
            if (window.history && window.history.replaceState) {
                window.history.replaceState({}, document.title, 'producto.php');
            }
            return;
        }

        // Sanitizar ID (solo números)
        const idSanitizado = parseInt(idarticulo, 10);
        cargarProducto(idSanitizado);
    });

    // ============================================================
    // CARGAR DATOS DEL PRODUCTO DESDE LA API (con validación)
    // ============================================================
    function formatearPrecioProducto(valor) {
        const numero = typeof window.BM_parseProductPrice === 'function'
            ? window.BM_parseProductPrice(valor)
            : parseFloat(valor);
        return Number.isFinite(numero) ? numero.toFixed(2) : "0.00";
    }

    function obtenerPrecioFinalProductoDetalle(producto) {
        if (typeof window.BM_getProductEffectivePrice === 'function') {
            return window.BM_getProductEffectivePrice(producto);
        }

        const precioOferta = parseFloat(producto && (producto.precio_09 || producto.precio09 || producto.precio9 || producto.precio_oferta)) || 0;
        return precioOferta > 0 ? precioOferta : (parseFloat(producto && (producto.precio_venta || producto.precio)) || 0);
    }

    function obtenerHtmlPrecioProducto(producto) {
        const precioNormal = formatearPrecioProducto(obtenerPrecioFinalProductoDetalle(producto));

        return `
        <span class="product-card-price product-card-price-normal">Precio: Q${precioNormal}</span>
    `;
    }

    function productoSinStock(producto) {
        if (typeof window.BM_isProductOutOfStock === 'function') {
            return window.BM_isProductOutOfStock(producto);
        }

        if (!producto || typeof producto !== 'object') {
            return false;
        }

        const stock = producto.stock ?? producto.stocksucursal ?? producto.stock_sucursal ?? producto.existencia ?? producto.cantidad_stock;

        if (stock === null || stock === undefined || String(stock).trim() === '') {
            return false;
        }

        const texto = String(stock).trim().toLowerCase();
        const numero = parseFloat(texto.replace(',', '.'));

        if (Number.isFinite(numero)) {
            return numero <= 0;
        }

        return texto.includes('agotado') || texto.includes('sin stock') || texto.includes('no disponible');
    }

    function obtenerStockProductoTexto(producto) {
        if (typeof window.BM_getProductStockValue === 'function') {
            const stock = window.BM_getProductStockValue(producto);
            return stock !== '' ? stock : 'N/A';
        }

        if (!producto || typeof producto !== 'object') {
            return 'N/A';
        }

        const stock = producto.stock ?? producto.stocksucursal ?? producto.stock_sucursal ?? producto.existencia ?? producto.cantidad_stock;
        return stock === null || stock === undefined || String(stock).trim() === '' ? 'N/A' : String(stock).trim();
    }

    function actualizarEstadoCompraProducto(agotado) {
        const detalle = document.getElementById('producto-details');
        const stock = document.getElementById('producto-stock');
        const boton = document.getElementById('producto-add-cart-btn');
        const cantidades = document.querySelector('.product-quantities');
        const inputCantidad = document.getElementById('producto-cantidad');

        if (detalle) {
            detalle.classList.toggle('is-out-of-stock', agotado);
        }

        if (stock) {
            stock.textContent = agotado ? 'Agotado' : 'En stock';
            stock.classList.toggle('is-out-of-stock', agotado);
        }

        if (boton) {
            boton.disabled = agotado;
            boton.classList.toggle('is-disabled', agotado);
            boton.setAttribute('aria-disabled', agotado ? 'true' : 'false');
        }

        if (cantidades) {
            cantidades.classList.toggle('is-disabled', agotado);
        }

        if (inputCantidad) {
            inputCantidad.disabled = agotado;
            if (agotado) {
                inputCantidad.value = 1;
            }
        }
    }

    function mostrarAvisoProductoAgotado() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Producto agotado',
                text: 'Este producto no tiene stock disponible por el momento.',
                confirmButtonColor: '#0f1b33'
            });
            return;
        }

        alert('Este producto no tiene stock disponible por el momento.');
    }

    function cargarProducto(idarticulo) {
        // Validar ID nuevamente antes de hacer la petición
        if (!validarIdProducto(idarticulo)) {
            mostrarError('ID de producto inválido.');
            return;
        }

        // Sanitizar ID para la petición
        const idSanitizado = parseInt(idarticulo, 10);

        Promise.resolve(<?= json_encode($producto_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>)
            .then(data => {
                // Validar respuesta de la API
                if (!data || !data.success || !data.data || data.data.length === 0) {
                    mostrarError('No se encontró el producto. El ID puede ser inválido o el producto no existe.');
                    // Limpiar URL si el producto no existe
                    if (window.history && window.history.replaceState) {
                        window.history.replaceState({}, document.title, 'producto.php');
                    }
                    return;
                }

                productoActual = typeof window.BM_normalizeProductPricing === 'function'
                    ? window.BM_normalizeProductPricing(data.data[0])
                    : data.data[0];

                // Validar que el ID del producto coincide con el solicitado
                if (productoActual.idarticulo != idSanitizado) {
                    console.warn('⚠️ El ID del producto no coincide');
                    mostrarError('Error de validación del producto.');
                    return;
                }

                cargarFotosProducto(idSanitizado);
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarError('Error al cargar el producto. Por favor, intenta de nuevo.');
            });
    }

    // ============================================================
    // CARGAR FOTOS DEL PRODUCTO (con validación)
    // ============================================================
    function cargarFotosProducto(idarticulo) {
        // Validar ID antes de cargar fotos
        if (!validarIdProducto(idarticulo)) {
            console.error('ID inválido para cargar fotos');
            return;
        }

        const idSanitizado = parseInt(idarticulo, 10);
        // Guardar imagen principal por separado
        imagenPrincipal = resolverImagenProductoDetalle(productoActual.imagen, productoActual.foto);

        // Limpiar array de fotos adicionales
        imagenesProducto = [];
        imagenIndex = 0; // Empezar con la imagen principal


        // Cargar fotos desde la API
        fetch("https://ssl.sol.sistemasolgt.com/induscollesitio/api/api_tienda_mostrarfotosproducto.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    idarticulo: idSanitizado
                })
            })
            .then(r => r.json())
            .then(data => {

                if (data.success && data.imagenes && Array.isArray(data.imagenes)) {

                    // Procesar y ordenar las imágenes
                    let imagenesProcesadas = [];

                    data.imagenes.forEach((img, index) => {
                        const urlValidada = resolverRutaFotoProducto(img.ruta);

                        if (!urlValidada) {
                            console.warn(`Imagen ${index + 1} no tiene ruta valida:`, img);
                            return;
                        }

                        imagenesProcesadas.push({
                            url: urlValidada,
                            orden: parseInt(img.orden) || index,
                            id: img.id
                        });
                    });

                    // Ordenar por el campo "orden" (ascendente: menor a mayor)
                    imagenesProcesadas.sort((a, b) => {
                        // Primero ordenar por el campo "orden"
                        if (a.orden !== b.orden) {
                            return a.orden - b.orden;
                        }
                        // Si tienen el mismo orden, ordenar por ID
                        return (parseInt(a.id) || 0) - (parseInt(b.id) || 0);
                    });

                    // Limpiar el array antes de agregar las imágenes ordenadas
                    imagenesProducto = [];

                    // Agregar las imágenes ordenadas al array (solo las de la API, no la principal)
                    imagenesProcesadas.forEach(img => {
                        imagenesProducto.push(img.url);
                    });
                }

                mostrarProducto();
            })
            .catch(error => {
                console.error('❌ Error al cargar fotos:', error);
                mostrarProducto(); // Mostrar producto aunque falle la carga de fotos
            });
    }

    // ============================================================
    // MOSTRAR PRODUCTO EN LA PÁGINA
    // ============================================================
    function mostrarProducto() {
        if (!productoActual) return;

        // Ocultar loading y error
        document.getElementById('producto-loading').style.display = 'none';
        document.getElementById('producto-error').style.display = 'none';

        // Mostrar detalles
        document.getElementById('producto-details').style.display = 'block';
        document.getElementById('producto-tabs').style.display = 'block';
        const agotado = productoSinStock(productoActual);
        actualizarEstadoCompraProducto(agotado);

        // Llenar información básica
        document.getElementById('producto-nombre').textContent = productoActual.nombre || 'Sin nombre';
        document.getElementById('producto-precio').innerHTML = obtenerHtmlPrecioProducto(productoActual);
        document.getElementById('producto-descripcion').textContent = productoActual.descripcion || 'Sin descripción disponible.';
        document.getElementById('producto-stock').textContent = agotado ? 'Agotado' : 'En stock';
        document.getElementById('producto-sku').textContent = productoActual.idarticulo || 'N/A';
        document.getElementById('producto-codigo').textContent = productoActual.codigo || 'N/A';
        document.getElementById('producto-categoria').textContent = productoActual.categoria || 'N/A';

        // Llenar descripción completa
        document.getElementById('producto-descripcion-completa').innerHTML =
            productoActual.descripcion ? `<p>${productoActual.descripcion}</p>` : '<p>No hay descripción disponible para este producto.</p>';

        // Llenar información adicional
        const precioFinalProducto = formatearPrecioProducto(obtenerPrecioFinalProductoDetalle(productoActual));
        const precioInfoHtml = `
        <li><strong>Precio:</strong> <span>Q${precioFinalProducto}</span></li>
    `;

        let infoHtml = `
        <li><strong>Nombre:</strong> <span>${productoActual.nombre || 'N/A'}</span></li>
        <li><strong>Código:</strong> <span>${productoActual.codigo || 'N/A'}</span></li>
        <li><strong>Categoría:</strong> <span>${productoActual.categoria || 'N/A'}</span></li>
        <li><strong>Stock:</strong> <span>${obtenerStockProductoTexto(productoActual)}</span></li>
        ${precioInfoHtml}
    `;
        document.getElementById('producto-informacion').innerHTML = infoHtml;

        // Mostrar imágenes
        mostrarImagenes();
    }

    // ============================================================
    // MOSTRAR IMÁGENES DEL PRODUCTO
    // ============================================================
    function mostrarImagenes() {
        if (!imagenPrincipal) {
            return;
        }


        let sliderNav = document.getElementById('slider-nav');
        let sliderFor = document.getElementById('slider-for');

        if (!sliderNav || !sliderFor) {
            console.error('❌ No se encontraron los elementos del slider');
            return;
        }

        // Limpiar contenedores
        sliderNav.innerHTML = '';
        sliderFor.innerHTML = '';

        // ============================================
        // MOSTRAR IMAGEN PRINCIPAL (GRANDE)
        // ============================================
        let imgPrincipal = document.createElement('img');
        imgPrincipal.id = 'imagen-principal-actual';
        imgPrincipal.src = imagenPrincipal;
        imgPrincipal.alt = productoActual.nombre || 'Producto';
        if (typeof window.BM_isProductImageFallbackUrl === 'function' && window.BM_isProductImageFallbackUrl(imagenPrincipal)) {
            imgPrincipal.classList.add('bm-product-image-fallback');
        }
        imgPrincipal.style.width = '100%';
        imgPrincipal.style.height = 'auto';
        imgPrincipal.style.maxHeight = '500px';
        imgPrincipal.style.objectFit = 'contain';
        imgPrincipal.style.display = 'block';
        imgPrincipal.onerror = function() {
            console.error('❌ Error al cargar imagen principal:', imagenPrincipal);
            aplicarImagenTemporalProducto(this);
        };
        sliderFor.appendChild(imgPrincipal);

        if (productoSinStock(productoActual)) {
            if (typeof window.BM_getOutOfStockRibbonHtml === 'function') {
                sliderFor.insertAdjacentHTML('beforeend', window.BM_getOutOfStockRibbonHtml(productoActual, 'bm-stock-ribbon-detail'));
            } else {
                const etiquetaAgotado = document.createElement('span');
                etiquetaAgotado.className = 'bm-stock-ribbon bm-stock-ribbon-detail';
                etiquetaAgotado.setAttribute('aria-label', 'Producto agotado');
                etiquetaAgotado.textContent = 'Agotado';
                sliderFor.appendChild(etiquetaAgotado);
            }
        }

        // ============================================
        // MOSTRAR MINIATURAS (FOTOS DE LA API)
        // ============================================
        // Agregar miniatura de la imagen principal primero
        let divNavPrincipal = document.createElement('div');
        divNavPrincipal.className = 'thumb-item active';
        let imgNavPrincipal = document.createElement('img');
        imgNavPrincipal.src = imagenPrincipal;
        imgNavPrincipal.alt = 'Imagen principal';
        if (typeof window.BM_isProductImageFallbackUrl === 'function' && window.BM_isProductImageFallbackUrl(imagenPrincipal)) {
            imgNavPrincipal.classList.add('bm-product-image-fallback');
        }
        imgNavPrincipal.onclick = function() {
            cambiarImagen(-1); // -1 indica imagen principal
        };
        imgNavPrincipal.onerror = function() {
            aplicarImagenTemporalProducto(this);
        };
        divNavPrincipal.appendChild(imgNavPrincipal);
        sliderNav.appendChild(divNavPrincipal);

        // Agregar miniaturas de las fotos de la API
        imagenesProducto.forEach((img, index) => {
            let divNav = document.createElement('div');
            divNav.className = 'thumb-item';
            let imgNav = document.createElement('img');
            imgNav.src = img;
            imgNav.alt = `Foto ${index + 1}`;
            if (typeof window.BM_isProductImageFallbackUrl === 'function' && window.BM_isProductImageFallbackUrl(img)) {
                imgNav.classList.add('bm-product-image-fallback');
            }
            imgNav.onclick = function() {
                cambiarImagen(index);
            };
            imgNav.onerror = function() {
                console.error('❌ Error al cargar miniatura:', img);
                aplicarImagenTemporalProducto(this);
            };
            divNav.appendChild(imgNav);
            sliderNav.appendChild(divNav);
        });

    }

    // ============================================================
    // CAMBIAR IMAGEN AL HACER CLICK EN MINIATURA
    // ============================================================
    function cambiarImagen(index) {
        let imgPrincipal = document.getElementById('imagen-principal-actual');
        if (!imgPrincipal) return;
        let nuevaImagen = '';

        // index = -1 significa imagen principal, index >= 0 significa foto de la API
        if (index === -1) {
            // Mostrar imagen principal
            nuevaImagen = imagenPrincipal;
            imagenIndex = -1;
        } else if (index >= 0 && index < imagenesProducto.length) {
            // Mostrar foto de la API
            nuevaImagen = imagenesProducto[index];
            imagenIndex = index;
        }

        if (nuevaImagen) {
            imgPrincipal.src = nuevaImagen;
            if (typeof window.BM_isProductImageFallbackUrl === 'function' && window.BM_isProductImageFallbackUrl(nuevaImagen)) {
                imgPrincipal.classList.add('bm-product-image-fallback');
            } else {
                imgPrincipal.classList.remove('bm-product-image-fallback');
            }
        }

        // Actualizar clase activa en miniaturas
        let sliderNav = document.getElementById('slider-nav');
        if (sliderNav) {
            for (let i = 0; i < sliderNav.children.length; i++) {
                let thumbItem = sliderNav.children[i];
                // i = 0 es la imagen principal, i > 0 son las fotos de la API
                if ((index === -1 && i === 0) || (index >= 0 && i === index + 1)) {
                    thumbItem.classList.add('active');
                    thumbItem.style.border = '2px solid #151515';
                    thumbItem.style.opacity = '1';
                } else {
                    thumbItem.classList.remove('active');
                    thumbItem.style.border = '2px solid transparent';
                    thumbItem.style.opacity = '0.7';
                }
            }
        }
    }

    // ============================================================
    // CAMBIAR CANTIDAD
    // ============================================================
    function cambiarCantidad(n) {
        if (typeof cambiarInputCantidad === 'function') {
            cambiarInputCantidad('producto-cantidad', n);
            return;
        }

        const input = document.getElementById('producto-cantidad');
        if (!input) return;

        const paso = parseInt(n, 10) >= 0 ? 1 : -1;
        let val = (parseInt(input.value, 10) || 1) + paso;
        if (val < 1) val = 1;
        if (val > 99) val = 99;
        input.value = val;
    }

    // ============================================================
    // AGREGAR AL CARRITO DESDE PRODUCTO
    // ============================================================
    function agregarAlCarritoDesdeProducto() {
        if (!productoActual) return;

        if (productoSinStock(productoActual)) {
            mostrarAvisoProductoAgotado();
            return;
        }

        const inputCantidad = document.getElementById('producto-cantidad');
        let cantidad = typeof normalizarInputCantidad === 'function' ?
            normalizarInputCantidad(inputCantidad) :
            Math.min(Math.max(parseInt(inputCantidad?.value, 10) || 1, 1), 99);
        let imagen = imagenPrincipal || (window.BM_PRODUCT_IMAGE_FALLBACK || 'assets/img/ImagenTemporalINDUSTRIALCOLLECTORS.png');

        const agregado = agregarAlCarrito(
            productoActual.idarticulo,
            productoActual.nombre,
            formatearPrecioProducto(obtenerPrecioFinalProductoDetalle(productoActual)),
            imagen,
            productoActual.descripcion || '',
            cantidad
        );

        if (!agregado) {
            return;
        }

        if (inputCantidad) {
            inputCantidad.value = 1;
        }

        // Mostrar mensaje de éxito
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: '¡Agregado!',
                text: 'El producto se agregó al carrito',
                timer: 2000,
                showConfirmButton: false
            });
        }
    }

    // ============================================================
    // COMPARTIR POR WHATSAPP
    // ============================================================
    function compartirWhatsAppProducto(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        if (!productoActual) return false;

        const url = window.location.href;
        const precioFinalProducto = formatearPrecioProducto(obtenerPrecioFinalProductoDetalle(productoActual));
        const mensaje = encodeURIComponent(
            `🛍️ *${productoActual.nombre}*\n\n` +
            `💰 Precio: *Q${precioFinalProducto}*\n\n` +
            `📦 Disponibilidad: ${productoSinStock(productoActual) ? 'Agotado' : 'En stock'}\n\n` +
            `🔗 Ver más detalles:\n${url}\n\n` +
            `_Industrial Collectors, S.A._`
        );

        const whatsappUrl = `https://wa.me/?text=${mensaje}`;

        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        if (isMobile) {
            window.location.href = whatsappUrl;
        } else {
            window.open(whatsappUrl, '_blank');
        }

        return false;
    }

    // ============================================================
    // MOSTRAR ERROR
    // ============================================================
    function mostrarError(mensaje) {
        document.getElementById('producto-loading').style.display = 'none';
        document.getElementById('producto-error').style.display = 'block';
        document.getElementById('producto-error').innerHTML = `<p style="color: red;">${mensaje}</p>`;
    }
</script>
