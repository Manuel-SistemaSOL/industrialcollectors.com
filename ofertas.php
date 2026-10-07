<?php
// SEO para la página de promociones
$seo_title = "Promociones | La Broca Ferreterías";

$seo_description = "Descubre promociones y productos seleccionados de La Broca Ferreterías para construcción, herramientas, plomería, electricidad y pintura.";

$seo_canonical_url = "https://labrocaferreterias.com/ofertas.php";

$seo_robots = "index, follow, max-image-preview:large";

include 'head.php';
$current_page = basename($_SERVER['PHP_SELF']);
require_once __DIR__ . '/assets/php/category_visuals.php';

$bmOffersHeroImage = bm_asset_path_if_exists('assets/img/paginaImagenes/ofertas.png') ?: bm_asset_path_if_exists('assets/img/ImagenTemporalLABROCA.png') ?: bm_asset_path_if_exists('assets/img/404.png');
$bmOffersHeroImageVersion = $bmOffersHeroImage !== '' && file_exists(__DIR__ . '/' . $bmOffersHeroImage) ? filemtime(__DIR__ . '/' . $bmOffersHeroImage) : time();
$bmOffersHeroImageSrc = $bmOffersHeroImage . '?v=' . $bmOffersHeroImageVersion;
$bmOffersMoreUrl = 'tienda.php?idsucursal=4&ofertas=1';


?>

<?php bmRenderPageTitle('Promociones'); ?>

<!-- Intro y productos con el diseno de divisiones -->
<section class="category-intro-area bm-special-intro bm-promotions-intro" aria-label="Promociones">
    <div class="container">
        <article class="category-intro-card">
            <div class="category-intro-image">
                <img src="<?php echo htmlspecialchars($bmOffersHeroImageSrc, ENT_QUOTES, 'UTF-8'); ?>" alt="Promociones La Broca Ferreterías" loading="eager" decoding="async">
            </div>
        </article>
    </div>
</section>

<section class="shop-area bg-ffffff pt-50 pb-50 category-shop-area bm-shop-clean bm-shop-division bm-special-products-page" aria-label="Productos en promoción">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="bm-shop-toolbar">
                    <strong>Promociones</strong>
                    <span class="bm-shop-count" id="bm-products-count"></span>
                </div>

                <div id="products-collections-filter" class="row">
                    <!-- Los productos se cargan dinamicamente desde la API -->
                </div>

                <div class="category-more-wrap">
                    <a href="<?php echo htmlspecialchars($bmOffersMoreUrl, ENT_QUOTES); ?>" class="category-more-link">
                        VER M&Aacute;S
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

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
<script type="text/javascript" src="assets/js/bm-special-products.js?v=<?php echo filemtime('assets/js/bm-special-products.js'); ?>"></script>
<script type="text/javascript" src="assets/js/ofertas.js?v=<?php echo filemtime('assets/js/ofertas.js'); ?>"></script>
