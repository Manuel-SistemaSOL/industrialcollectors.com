<?php 
// SEO para la página de tienda
$seo_title = "Tienda | Industrial Collectors, S.A.";

$seo_description = "Explora perfilería, láminas, tubos, vigas, costaneras y hierro de construcción disponibles en Industrial Collectors, S.A.";

$seo_canonical_url = "https://industrialcollectors.com/tienda.php";

$tiene_filtros_seo =
    !empty($_GET['categoria']) ||
    !empty($_GET['buscar']) ||
    isset($_GET['idmarca']) ||
    isset($_GET['marca']) ||
    isset($_GET['idsucursal']) ||
    isset($_GET['vista']) ||
    isset($_GET['page']) ||
    isset($_GET['price_min']) ||
    isset($_GET['price_max']) ||
    (isset($_GET['ofertas']) && $_GET['ofertas'] === '1');

$seo_robots = $tiene_filtros_seo
    ? "noindex, follow"
    : "index, follow, max-image-preview:large";

include 'head.php';
require_once __DIR__ . '/assets/php/category_visuals.php';
$current_page = basename($_SERVER['PHP_SELF']);
$categoriaActiva = isset($_GET['categoria']) ? (string) $_GET['categoria'] : '';
$busquedaActiva = isset($_GET['buscar']) ? trim((string) $_GET['buscar']) : '';
$ofertasActivas = isset($_GET['ofertas']) && (string) $_GET['ofertas'] === '1';

// Cargar categorías desde la API (igual que en head.php)
include "assets/php/rutas.php";
$response = getApi($url_listar_categorias);
$data = json_decode($response, true);
$categorias = $data["data"] ?? [];
if (!function_exists('bm_tienda_category_name_by_id')) {
    function bm_tienda_category_name_by_id($categories, $categoryId) {
        $categoryId = (string) $categoryId;

        foreach ((array) $categories as $category) {
            if ((string) ($category['idcategoria'] ?? '') === $categoryId) {
                return trim((string) ($category['nombre'] ?? ''));
            }
        }

        return '';
    }
}

$categoriaActivaNombre = $categoriaActiva !== '' ? bm_tienda_category_name_by_id($categorias, $categoriaActiva) : '';
$tituloPaginaTienda = 'Tienda';

if ($ofertasActivas) {
    $tituloPaginaTienda = 'Promociones';
} elseif ($busquedaActiva !== '') {
    $tituloPaginaTienda = bm_title_case($busquedaActiva);
} elseif ($categoriaActivaNombre !== '') {
    $tituloPaginaTienda = bm_title_case($categoriaActivaNombre);
} elseif ($categoriaActiva !== '') {
    $tituloPaginaTienda = 'Productos';
}

$vistaGeneralActiva = ($categoriaActiva === '' && $busquedaActiva === '' && !$ofertasActivas);
$tiendaSinFiltrosActivos = ($categoriaActiva === '' && $busquedaActiva === '' && !$ofertasActivas);
$mostrarSidebarTienda = true;
$bmProductsHeroImage = bm_asset_path_if_exists('assets/img/paginaImagenes/productos.png') ?: bm_asset_path_if_exists('assets/img/ImagenTemporalINDUSTRIALCOLLECTORS.png') ?: bm_asset_path_if_exists('assets/img/404.png');
$bmProductsHeroImageVersion = $bmProductsHeroImage !== '' && file_exists(__DIR__ . '/' . $bmProductsHeroImage) ? filemtime(__DIR__ . '/' . $bmProductsHeroImage) : '1';
$bmProductsHeroImageSrc = $bmProductsHeroImage . '?v=' . $bmProductsHeroImageVersion;

$shopAreaClassList = array_filter([
    'shop-area',
    'bg-ffffff',
    'pt-50',
    'pb-50',
    $mostrarSidebarTienda ? 'bm-shop-with-sidebar' : 'bm-shop-no-sidebar',
    $vistaGeneralActiva ? 'bm-shop-general' : 'bm-shop-clean',
]);

if (!function_exists('bm_tienda_category_icon_class')) {
    function bm_tienda_category_icon_class($nombreCategoria) {
        return bm_category_icon_class($nombreCategoria);
    }
}

$categoriaToggleTexto = $categoriaActivaNombre !== '' ? bm_title_case($categoriaActivaNombre) : 'Todos los productos';
$categoriaToggleIcono = $categoriaActivaNombre !== '' ? bm_tienda_category_icon_class($categoriaActivaNombre) : 'bx bx-grid-alt';

if ($busquedaActiva !== '' || $ofertasActivas) {
    $categoriaToggleTexto = 'Seleccionar categoría';
    $categoriaToggleIcono = 'bx bx-category-alt';
}

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
        white-space: nowrap;        /* 🔥 Evita salto de línea */
        overflow: hidden;           /* Oculta texto excesivo */
        text-overflow: ellipsis;    /* Añade "..." */
        margin-bottom: 5px;
    }

    /* Estilos para tienda.php - imagen compacta y uniforme */
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
        display: -webkit-box;
        font-size: 15px;
        font-weight: 400;
        line-height: 1.3;
        min-height: 39px;
        margin-bottom: 0;
        overflow: hidden;
        text-overflow: clip;
        white-space: normal;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .single-shop-products {
        border: 1px solid rgba(111, 142, 196, 0.18);
        border-radius: 6px;
        padding: 14px;
        background: #fff;
        transition: all 0.3s ease;
        position: relative;
        box-shadow: 0 10px 24px rgba(31, 36, 64, 0.08);
        display: flex;
        flex-direction: column;
        height: 100%;
        margin-bottom: 0;
    }

    .single-shop-products:hover {
        box-shadow: 0 14px 30px rgba(31, 36, 64, 0.11);
        transform: none;
        border-color: rgba(111, 142, 196, 0.28);
    }

    .single-shop-products .shop-products-image {
        background: #ffffff;
        border-radius: 0;
        height: 190px;
        overflow: hidden;
        padding: 0;
    }

    .single-shop-products .shop-products-image .tag {
        position: absolute;
        top: 10px;
        right: 10px;
        border-radius: 50px !important;
        padding: 5px 15px !important;
        width: auto !important;
        height: auto !important;
        min-width: unset !important;
        line-height: normal !important;
        font-size: 12px !important;
        font-weight: 500 !important;
        background: #1D71B8 !important;
        color: #1D1D1B !important;
        box-shadow: 0 4px 12px rgba(29, 113, 184, 0.32);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
    }

    .single-shop-products:hover .shop-products-image .tag {
        background: #1D71B8 !important;
    }

    .single-shop-products .shop-products-content {
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        margin-top: 12px;
        padding: 0;
    }

    .single-shop-products .shop-products-content h3 a,
    .single-shop-products:hover .shop-products-content h3 a {
        color: #1D1D1B !important;
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

    /* Responsive: categorias en carril horizontal */
    @media (max-width: 991px) {
        .shop-area .col-lg-4.col-md-12 {
            display: block !important;
            flex: 0 0 100% !important;
            max-width: 100% !important;
            width: 100% !important;
            margin-bottom: 18px;
        }
        
        /* El contenido mantiene ancho completo debajo del carril */
        .shop-area .col-lg-8.col-md-12 {
            width: 100% !important;
            flex: 0 0 100% !important;
            max-width: 100% !important;
        }

        .shop-area .widget-area {
            display: block !important;
            margin-bottom: 0;
        }

        .shop-area .widget.widget_categories {
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
            align-items: center;
            border-radius: 4px;
            font-size: 12px;
            gap: 8px;
            min-height: 42px;
            padding: 10px 12px;
            white-space: nowrap;
        }

        #products-collections-filter {
            display: flex !important;
            flex-wrap: wrap !important;
            margin-left: -8px;
            margin-right: -8px;
        }

        #products-collections-filter > div:not(.col-12) {
            width: 33.333333% !important;
            flex: 0 0 33.333333% !important;
            max-width: 33.333333% !important;
            padding-left: 8px;
            padding-right: 8px;
            margin-bottom: 18px;
        }

        .single-shop-products {
            margin-bottom: 0 !important;
            min-height: 360px !important;
            padding: 12px;
        }

        .single-shop-products .shop-products-image {
            height: 145px !important;
            min-height: 145px !important;
        }

        .single-shop-products .shop-products-image .tag {
            font-size: 11px !important;
            font-weight: 500 !important;
            padding: 4px 10px !important;
            top: 8px !important;
            right: 8px !important;
        }

        .shop-products-content h3 {
            font-size: 13px;
            min-height: 34px;
        }

    }

    @media (max-width: 767px) {
        #products-collections-filter > div:not(.col-12) {
            width: 50% !important;
            flex: 0 0 50% !important;
            max-width: 50% !important;
        }

        .single-shop-products {
            min-height: 330px !important;
            padding: 10px !important;
        }

        .single-shop-products .shop-products-image {
            height: 126px !important;
            min-height: 126px !important;
        }

        .widget.widget_categories .categories li a.nav-link {
            font-size: 11px;
            min-height: 38px;
            padding: 8px 10px;
        }

        .shop-products-content h3 {
            font-size: 12px;
            min-height: 31px;
        }
    }

    .products-filter-options {
        display: none !important;
    }

    #products-collections-filter > div:not(.col-12) {
        margin-bottom: 20px;
    }

    #products-collections-filter {
        min-height: 190px;
    }

    #products-collections-filter .alert {
        background: #f6f6f3;
        border: 1px solid rgba(21, 21, 21, 0.18);
        border-left: 4px solid #1D1D1B;
        border-radius: 8px;
        color: #3a3a3a;
        font-size: 15px;
        font-weight: 500;
        line-height: 1.55;
        padding: 20px 22px;
        width: 100%;
    }

    /* ===== CATEGORÍAS SIDEBAR ===== */
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

    /* Hover */
    .widget.widget_categories .categories li a.nav-link:hover {
        background: #F3F5F7;
        border-color: #009FE3;
        color: #000;
    }

    /* Activo */
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

</style>

<style>
    .bm-shop-title-area {
        display: block !important;
    }

    .shop-area,
    .shop-area.bg-ffffff,
    .shop-area.category-shop-area {
        background: #ffffff !important;
        padding-top: 52px !important;
    }

    .shop-area.bm-shop-clean,
    .shop-area.category-shop-area.bm-shop-clean {
        background: #ffffff !important;
        padding-top: 42px !important;
    }

    .bm-shop-clean #products-collections-filter {
        background: #ffffff !important;
    }

    .bm-shop-clean .bm-shop-toolbar {
        margin-bottom: 26px;
    }

    .shop-area .widget-area {
        display: block !important;
    }

    .shop-area .products-filter-options {
        display: block !important;
        margin-bottom: 26px;
    }

    .shop-area .widget {
        background: transparent;
        margin-bottom: 28px;
    }

    .shop-area .widget-title {
        color: #1D1D1B;
        font-size: 26px;
        font-weight: 600;
        line-height: 1.2;
        margin-bottom: 28px;
        padding-bottom: 14px;
        position: relative;
    }

    .shop-area .widget-title::before {
        background: #1D1D1B;
        bottom: 0;
        content: "";
        height: 2px;
        left: 0;
        position: absolute;
        width: 70px;
    }

    .bm-shop-toolbar {
        align-items: center;
        display: flex;
        justify-content: space-between;
        margin-bottom: 22px;
    }

    .bm-shop-toolbar strong {
        color: #1D1D1B;
        font-size: 18px;
        font-weight: 600;
        letter-spacing: 0;
    }

    .bm-shop-count {
        color: #1D1D1B;
        font-size: 12px;
        font-weight: 500;
    }

    #products-collections-filter {
        display: flex !important;
        flex-wrap: wrap !important;
        margin-left: -15px;
        margin-right: -15px;
        min-height: 220px;
    }

    #products-collections-filter > div:not(.col-12) {
        flex: 0 0 25% !important;
        max-width: 25% !important;
        width: 25% !important;
        padding-left: 15px;
        padding-right: 15px;
        margin-bottom: 34px;
    }

    .bm-shop-clean #products-collections-filter > div:not(.col-12) {
        flex: 0 0 25% !important;
        max-width: 25% !important;
        width: 25% !important;
    }

    @media (max-width: 991px) {
        .bm-shop-clean #products-collections-filter > div:not(.col-12) {
            flex: 0 0 33.333333% !important;
            max-width: 33.333333% !important;
            width: 33.333333% !important;
        }
    }

    @media (max-width: 767px) {
        .bm-shop-clean #products-collections-filter > div:not(.col-12) {
            flex: 0 0 50% !important;
            max-width: 50% !important;
            width: 50% !important;
        }

    }

    .bm-shop-clean .products-filter-options {
        display: none !important;
    }

    .widget.widget_categories .categories li a.nav-link.bm-shop-all-products {
        font-weight: 700;
    }

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
            flex: 0 0 100% !important;
            max-width: 100% !important;
            width: 100% !important;
        }

        .shop-area .products-filter-options {
            display: block !important;
            margin-bottom: 14px;
        }

        #products-collections-filter {
            margin-left: -8px;
            margin-right: -8px;
        }

        #products-collections-filter > div:not(.col-12) {
            flex-basis: 33.333333% !important;
            max-width: 33.333333% !important;
            padding-left: 8px;
            padding-right: 8px;
            margin-bottom: 18px;
        }
    }

    @media (max-width: 767px) {
        #products-collections-filter {
            margin-left: -8px;
            margin-right: -8px;
        }

        #products-collections-filter > div:not(.col-12) {
            flex-basis: 50% !important;
            max-width: 50% !important;
            padding-left: 8px;
            padding-right: 8px;
            margin-bottom: 18px;
        }
    }

    @media (max-width: 360px) {
        .bm-shop-title-area .page-title-content h2 {
            font-size: 24px;
        }

        .shop-area,
        .shop-area.bg-ffffff,
        .shop-area.category-shop-area {
            padding-top: 30px !important;
        }

        .shop-area .widget-title {
            font-size: 15px;
            margin-bottom: 8px;
        }

        .widget.widget_categories .categories {
            gap: 6px;
            padding-bottom: 6px;
        }

        .widget.widget_categories .categories li a.nav-link {
            font-size: 9px;
            min-height: 32px;
            padding: 6px 8px;
        }

        .bm-shop-toolbar {
            margin-bottom: 10px;
        }

        .bm-shop-toolbar strong {
            font-size: 13px;
        }

        .bm-shop-count {
            font-size: 10px;
        }

        #products-collections-filter {
            margin-left: -4px;
            margin-right: -4px;
        }

        #products-collections-filter > div:not(.col-12) {
            padding-left: 4px;
            padding-right: 4px;
            margin-bottom: 10px;
        }

        .single-shop-products {
            min-height: 258px !important;
            padding: 7px !important;
        }

        .single-shop-products .shop-products-image {
            height: 82px !important;
            min-height: 82px !important;
        }

        .shop-products-content h3 {
            font-size: 10px;
            min-height: 24px;
        }
    }

</style>

        <!-- Start Page Banner -->
        <div class="page-title-area bm-shop-title-area">
            <div class="container">
                <div class="page-title-content">
                    <h2><?php echo htmlspecialchars($tituloPaginaTienda); ?></h2>

                    <ul>
                        <li><a href="/">Inicio</a></li>
                        <?php if ($tituloPaginaTienda !== 'Tienda'): ?>
                            <li><a href="tienda.php?idsucursal=4">Tienda</a></li>
                        <?php endif; ?>
                        <li><?php echo htmlspecialchars($tituloPaginaTienda); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- End Page Banner -->

        <section class="category-intro-area bm-special-intro bm-products-intro" aria-label="Productos Industrial Collectors, S.A.">
            <div class="container">
                <article class="category-intro-card">
                    <div class="category-intro-image">
                        <img src="<?php echo htmlspecialchars($bmProductsHeroImageSrc, ENT_QUOTES, 'UTF-8'); ?>" alt="Materiales metálicos de Industrial Collectors, S.A." loading="eager" decoding="async">
                    </div>
                </article>
            </div>
        </section>

        <!-- Start Shop Area -->
        <section class="<?php echo htmlspecialchars(implode(' ', $shopAreaClassList), ENT_QUOTES); ?>">
            <div class="container">
                <div class="row">

                    <?php if ($mostrarSidebarTienda): ?>
                        <div class="col-lg-4 col-md-12">
                            <aside class="widget-area">

                                <div class="widget widget_categories bm-category-filter">
                                    <h3 class="widget-title">Categor&iacute;as</h3>

                                    <button class="bm-category-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#bm-shop-category-list" aria-expanded="false" aria-controls="bm-shop-category-list">
                                        <span class="bm-category-toggle-main">
                                            <span class="bm-category-toggle-icon">
                                                <i class="<?php echo htmlspecialchars($categoriaToggleIcono, ENT_QUOTES); ?>" aria-hidden="true"></i>
                                            </span>
                                            <span class="bm-category-toggle-copy">
                                                <span>Categoría actual</span>
                                                <strong><?php echo htmlspecialchars($categoriaToggleTexto); ?></strong>
                                            </span>
                                        </span>
                                        <i class="bx bx-chevron-down bm-category-toggle-chevron" aria-hidden="true"></i>
                                    </button>

                                    <ul class="categories bm-category-menu collapse" id="bm-shop-category-list">
                                        <li>
                                            <a href="tienda.php?idsucursal=4" class="nav-link bm-shop-all-products <?php echo $tiendaSinFiltrosActivos ? 'active' : ''; ?>">
                                                <i class="bx bx-grid-alt" aria-hidden="true"></i>
                                                <span>Todos los productos</span>
                                            </a>
                                        </li>

                                        <?php
                                        $index = 0;

                                        foreach ($categorias as $cat):
                                            if (isset($cat['condicion']) && $cat['condicion'] == 1):
                                                $icono = bm_tienda_category_icon_class($cat['nombre'] ?? '');
                                        ?>
                                            <li>
                                                <a href="tienda.php?categoria=<?= $cat['idcategoria'] ?>&idsucursal=4" class="nav-link <?= ($categoriaActiva == $cat['idcategoria']) ? 'active' : '' ?>">
                                                    <i class="<?= htmlspecialchars($icono, ENT_QUOTES) ?>" aria-hidden="true"></i>
                                                    <span><?= htmlspecialchars($cat['nombre']) ?></span>
                                                </a>
                                            </li>
                                        <?php
                                                $index++;
                                            endif;
                                        endforeach;

                                        if (empty($categorias) || $index == 0):
                                        ?>
                                            <li>
                                                <div class="alert alert-info" style="padding: 10px; margin: 0;">
                                                    No hay categor&iacute;as disponibles.
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
                    <?php endif; ?>

                    <div class="<?php echo $mostrarSidebarTienda ? 'col-lg-8 col-md-12' : 'col-lg-12 col-md-12'; ?>">

                        <div class="bm-shop-toolbar">
                            <strong>
                                <?php
                                echo ($busquedaActiva !== '' || $categoriaActiva !== '' || $ofertasActivas)
                                    ? htmlspecialchars($tituloPaginaTienda)
                                    : 'Todos los productos';
                                ?>
                            </strong>
                            <span class="bm-shop-count" id="bm-products-count"></span>
                        </div>

                        <div id="products-collections-filter" class="row">
                            <!-- Los productos se cargarán dinámicamente desde la API -->
                        </div>

                        <nav id="products-pagination" class="bm-products-pagination" aria-label="Paginaci&oacute;n de productos" hidden></nav>

                    </div>

                </div>
            </div>
        </section>
        <!-- End Shop Area -->

<!-- ===========================
      MODAL VISTA RÁPIDA
=========================== -->
<!-- 🟦 MODAL PRODUCTO - SIMPLE Y PROPIO -->
<div id="modalProducto" class="mp-overlay">
    <div class="mp-modal">

        <button class="mp-close" onclick="cerrarModal()">✕</button>

        <div class="mp-content">

            <!-- Imagen + Thumbs -->
            <div class="mp-left">
                <img id="mp-imagen-principal" class="mp-img-main" src="" alt="">

                <div id="mp-thumbs" class="mp-thumbs"></div>

                <button class="mp-prev" onclick="imgPrev()">←</button>
                <button class="mp-next" onclick="imgNext()">→</button>
            </div>

            <!-- Info -->
            <div class="mp-right">
                <h2 id="mp-titulo"></h2>
                <h3 id="mp-precio" class="mp-precio"></h3>

                <p id="mp-descripcion"></p>

                <p><b>Disponibilidad:</b> <span id="mp-stock"></span></p>
                <p><b>SKU:</b> <span id="mp-sku"></span></p>

                <div class="mp-cantidad-box">
                    <button onclick="mpCambiarCantidad(-1)">–</button>
                    <input id="mp-cantidad" type="text" value="1">
                    <button onclick="mpCambiarCantidad(1)">+</button>
                </div>

                <!-- Botones en fila -->
                <div class="mp-botones-container">
                    <button class="mp-btn-agregar" onclick="agregarAlCarritoDesdeModal()">🛒 Agregar al carrito</button>
                    <button id="btn-compartir-whatsapp" class="mp-btn-compartir mp-whatsapp" onclick="return compartirWhatsApp(event);" title="Compartir por WhatsApp">
                        <i class='bx bxl-whatsapp'></i> Compartir por WhatsApp
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>


        <!-- Start Footer Area -->

<?php include 'footer.php'; ?>
<script type="text/javascript" src="assets/js/sweatlert.js"></script> 
<script type="text/javascript" src="assets/js/tienda.js?v=<?php echo filemtime('assets/js/tienda.js'); ?>"></script>
