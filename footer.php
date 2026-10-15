<section class="footer-area bm-footer-clean" id="footer">
    <div class="container">
        <div class="bm-footer-grid">
            <div class="bm-footer-column">
                <h2>Cont&aacute;ctanos</h2>
                <p><strong>Ubicaci&oacute;n:</strong> Sucursal 1: 6 calle 13-10, zona 10, Finca El Carmen, San Miguel Petapa, Guatemala. Sucursal 2: Calle Real a San Miguel Petapa, 21-20 zona 10, La Enriqueta, Guatemala.</p>
                <p><strong>Horario:</strong> Lunes a viernes: 7:00 a. m. a 6:00 p. m. Sábado: 7:00 a. m. a 4:00 p. m.</p>
                <p><strong>Llamadas / WhatsApp:</strong> <a href="tel:+50232001888">+502 3200 1888</a> | <a href="https://wa.me/50230888818" target="_blank" rel="noopener">3088-8818</a></p>
                <p><strong>Email:</strong> <a href="mailto:industrialcollectorsventas@gmail.com">industrialcollectorsventas@gmail.com</a></p>

                <div class="bm-footer-social" aria-label="Redes sociales">
                    <a href="https://www.facebook.com/profile.php?id=100089824832535" target="_blank" rel="noopener noreferrer" aria-label="Facebook de Industrial Collectors, S.A."><i class='bx bxl-facebook'></i></a>
                    <a href="https://www.instagram.com/industrialcollectors" target="_blank" rel="noopener noreferrer" aria-label="Instagram de Industrial Collectors, S.A."><i class='bx bxl-instagram'></i></a>
                    <a href="https://www.tiktok.com/@colectoresindustriales" target="_blank" rel="noopener noreferrer" aria-label="TikTok de Industrial Collectors, S.A."><i class='bx bxl-tiktok'></i></a>
                </div>
            </div>

            <div class="bm-footer-column">
                <h2>Informaci&oacute;n</h2>
                <ul>
                    <li><a href="catalogo.php">Catálogo de productos</a></li>
                    <li><a href="nosotros.php">Qui&eacute;nes somos</a></li>
                    <li><a href="terminos.php">T&eacute;rminos y condiciones</a></li>
                    <li><a href="politicas_de_garantia.php">Pol&iacute;ticas de garant&iacute;a</a></li>
                    <li><a href="metodos_de_pago.php">M&eacute;todos de pago</a></li>
                    <li><a href="preguntas_frecuentes.php">Preguntas frecuentes</a></li>
                </ul>
            </div>

            <div class="bm-footer-column bm-footer-support">
                <h2>Soporte y atenci&oacute;n</h2>
                <p>Comunícate con nuestro equipo para obtener información sobre productos, disponibilidad y atención comercial.</p>
                <a class="bm-footer-whatsapp" href="https://wa.me/50230888818" target="_blank" rel="noopener">
                    <i class='bx bxl-whatsapp'></i> Chatear ahora
                </a>
            </div>
        </div>

        <div class="bm-footer-copyright">
            <p>Copyright &copy; Industrial Collectors, S.A. Todos los derechos reservados.</p>
        </div>
    </div>
</section>
<!-- End Footer Area -->

<!-- Start Go Top Area -->
<div class="go-top">
    <i class='bx bx-up-arrow-alt'></i>
</div>
<!-- End Go Top Area -->

<!-- Start WhatsApp Float Button -->
<a href="https://wa.me/50230888818" target="_blank" class="whatsapp-float" title="Chatear por WhatsApp">
    <i class='bx bxl-whatsapp'></i>
</a>
<!-- End WhatsApp Float Button -->

<?php
$bmResponsiveCssPath = __DIR__ . '/assets/css/bm-responsive-store.css';
$bmResponsiveCssVersion = file_exists($bmResponsiveCssPath) ? filemtime($bmResponsiveCssPath) : time();
?>
<link rel="stylesheet" href="assets/css/bm-responsive-store.css?v=<?php echo $bmResponsiveCssVersion; ?>">

<?php
$tjdcThemeCssPath = __DIR__ . '/assets/css/tjdc-theme.css';
$tjdcThemeCssVersion = file_exists($tjdcThemeCssPath) ? filemtime($tjdcThemeCssPath) : time();
?>
<link rel="stylesheet" href="assets/css/tjdc-theme.css?v=<?php echo $tjdcThemeCssVersion; ?>">



<!-- Jquery Slim JS -->
<script src="assets/js/jquery.min.js"></script>
<!-- Bootstrap JS -->
<script src="assets/js/bootstrap.bundle.min.js"></script>
<!-- Meanmenu JS -->
<script src="assets/js/jquery.meanmenu.js"></script>
<!-- Owl Carousel JS -->
<script src="assets/js/owl.carousel.min.js"></script>
<!-- Magnific Popup JS -->
<script src="assets/js/jquery.magnific-popup.min.js"></script>
<!-- Nice Select JS -->
<script src="assets/js/jquery.nice-select.min.js"></script>
<!-- Slick JS -->
<script src="assets/js/slick.min.js"></script>

<!-- Odometer JS -->
<script src="assets/js/odometer.min.js"></script>
<!-- Appear JS -->
<script src="assets/js/jquery.appear.js"></script>
<!-- Jquery Ui JS -->
<script src="assets/js/jquery-ui.min.js"></script>
<!-- Ajaxchimp JS -->
<script src="assets/js/jquery.ajaxchimp.min.js"></script>
<!-- Form Validator JS -->
<script src="assets/js/form-validator.min.js"></script>
<!-- Contact JS -->
<script src="assets/js/contact-form-script.js"></script>
<!-- Wow JS -->
<script src="assets/js/wow.min.js"></script>
<!-- Product Image Resolver -->
<?php
$productImageJsPath = __DIR__ . '/assets/js/product-image.js';
$productImageJsVersion = file_exists($productImageJsPath) ? filemtime($productImageJsPath) : time();
?>
<script src="assets/js/product-image.js?v=<?php echo $productImageJsVersion; ?>"></script>
<!-- Carrito JS -->
<?php
$carritoJsPath = __DIR__ . '/assets/js/carrito.js';
$carritoJsVersion = file_exists($carritoJsPath) ? filemtime($carritoJsPath) : time();
?>
<script src="assets/js/carrito.js?v=<?php echo $carritoJsVersion; ?>"></script>

<!-- Custom JS -->
<?php
$mainJsPath = __DIR__ . '/assets/js/main.js';
$mainJsVersion = file_exists($mainJsPath) ? filemtime($mainJsPath) : time();
?>
<script src="assets/js/main.js?v=<?php echo $mainJsVersion; ?>"></script>

<?php
if (!empty($extra_footer_scripts)) {
    echo $extra_footer_scripts;
}
?>

<script>
(function () {
    function removeGlobalLoader() {
        document.querySelectorAll('.preloader, .loader, #preloader, #loader, .preloader-area').forEach(function(loader) {
            loader.style.display = 'none';
            loader.style.visibility = 'hidden';
            loader.style.opacity = '0';

            if (loader.parentNode) {
                loader.parentNode.removeChild(loader);
            }
        });

        if (document.body) {
            document.body.classList.remove('loading', 'preloader-active');
            document.body.style.overflow = '';
        }
    }

    window.addEventListener('load', removeGlobalLoader);

    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(removeGlobalLoader, 500);
    });

    setTimeout(removeGlobalLoader, 1500);
})();
</script>

</body>
</html>
