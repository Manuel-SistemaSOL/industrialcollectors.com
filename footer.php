<section class="footer-area bm-footer-clean" id="footer">
    <div class="container">
        <div class="bm-footer-grid">
            <div class="bm-footer-column">
                <h2>Cont&aacute;ctanos</h2>
                <p><strong>Ubicaci&oacute;n:</strong> 32 avenida A, 28-31, zona 5, Santa Ana. A unos pasos del Mercado Santa Ana.</p>
                <p><strong>Horario:</strong> Abierto todos los d&iacute;as de 7:00 a. m. a 6:00 p. m.</p>
                <p><strong>Tel&eacute;fono / WhatsApp:</strong> <a href="tel:+50243143784">+502 4314 3784</a> | <a href="https://wa.me/50243143784" target="_blank" rel="noopener">WhatsApp</a></p>
                <p><strong>Email:</strong> <a href="mailto:labrocaferreterias01@gmail.com">labrocaferreterias01@gmail.com</a></p>

                <div class="bm-footer-social" aria-label="Redes sociales">
                    <a href="https://www.facebook.com/share/198josCWCF/" target="_blank" rel="noopener noreferrer" aria-label="Facebook de La Broca Ferreterías"><i class='bx bxl-facebook'></i></a>
                    <a href="https://www.tiktok.com/@la.broca1" target="_blank" rel="noopener noreferrer" aria-label="TikTok de La Broca Ferreterías"><i class='bx bxl-tiktok'></i></a>
                    <a href="https://www.tiktok.com/@la.broca1" target="_blank" rel="noopener noreferrer" aria-label="TikTok de La Broca Ferreterías"><i class='bx bxl-tiktok'></i></a>
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
                <p>&iquest;Necesitas ayuda con herramientas, materiales, pintura, plomer&iacute;a o electricidad? Escr&iacute;benos por WhatsApp.</p>
                <a class="bm-footer-whatsapp" href="https://wa.me/50243143784" target="_blank" rel="noopener">
                    <i class='bx bxl-whatsapp'></i> Chatear ahora
                </a>
            </div>
        </div>

        <div class="bm-footer-copyright">
            <p>Copyright &copy; La Broca Ferreter&iacute;as. Todos los derechos reservados.</p>
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
<a href="https://wa.me/50243143784" target="_blank" class="whatsapp-float" title="Chatear por WhatsApp">
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
