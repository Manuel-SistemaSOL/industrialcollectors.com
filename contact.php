<?php
// SEO para la página de contacto
$seo_title = "Contacto | La Broca Ferreterías";

$seo_description = "Contacta con La Broca Ferreterías para consultas, pedidos, herramientas, materiales de construcción, plomería, electricidad y pintura.";

$seo_canonical_url = "https://labrocaferreterias.com/contact.php";

$seo_robots = "index, follow, max-image-preview:large";

$current_page = basename($_SERVER['PHP_SELF']);

include 'head.php';

$contactMapsRedirectUrl = 'https://maps.app.goo.gl/e9zCfSJCzeR73RYs6?g_st=aw';
$contactGoogleMapsUrl = $contactMapsRedirectUrl;
$contactWazeUrl = $contactMapsRedirectUrl;
$contactWhatsappUrl = 'https://wa.me/50243143784';
$contactHeroImage = file_exists(__DIR__ . '/assets/img/paginaImagenes/contactanos.png') ? 'assets/img/paginaImagenes/contactanos.png' : (file_exists(__DIR__ . '/assets/img/ImagenTemporalLABROCA.png') ? 'assets/img/ImagenTemporalLABROCA.png' : 'assets/img/404.png');
$contactHeroImageVersion = file_exists(__DIR__ . '/' . $contactHeroImage) ? filemtime(__DIR__ . '/' . $contactHeroImage) : '1';
$contactHeroImageSrc = $contactHeroImage . '?v=' . $contactHeroImageVersion;
?>

<?php bmRenderPageTitle('Contacto'); ?>

<section class="category-intro-area bm-special-intro bm-promotions-intro bm-contact-intro" aria-label="Contáctanos">
    <div class="container">
        <article class="category-intro-card">
            <div class="category-intro-image">
                <img src="<?php echo htmlspecialchars($contactHeroImageSrc, ENT_QUOTES, 'UTF-8'); ?>" alt="Contacto con La Broca Ferreterías" loading="eager" decoding="async">
            </div>
        </article>
    </div>
</section>

<main class="contact-redesign-page">
    <section class="contact-redesign-area" id="dejanos-tu-mensaje">
        <div class="container">
            <div class="contact-redesign-shell">
                <div class="contact-form-panel">
                    <div class="contact-section-title">
                        <h2>D&eacute;janos tu mensaje</h2>
                    </div>
                    <p class="contact-form-note">Tu email no ser&aacute; publicado. Los campos requeridos est&aacute;n marcados con *</p>

                    <form id="contactForm" class="contact-clean-form" action="assets/php/form-process.php" method="post" novalidate>
                        <div class="contact-hidden-field" aria-hidden="true">
                            <label for="website">Sitio web</label>
                            <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="form-group contact-message-field">
                            <label for="message">Mensaje*</label>
                            <textarea name="message" id="message" cols="30" rows="5" required data-error="Por favor ingrese su mensaje" class="form-control"></textarea>
                            <div class="help-block with-errors"></div>
                        </div>

                        <div class="contact-form-grid">
                            <div class="form-group">
                                <label for="name">Nombre*</label>
                                <input type="text" name="name" id="name" class="form-control" required data-error="Por favor ingrese su nombre">
                                <div class="help-block with-errors"></div>
                            </div>

                            <div class="form-group">
                                <label for="email">Email*</label>
                                <input type="email" name="email" id="email" class="form-control" required data-error="Por favor ingrese su email">
                                <div class="help-block with-errors"></div>
                            </div>

                            <div class="form-group">
                                <label for="phone_number">Teléfono*</label>
                                <input type="text" name="phone_number" id="phone_number" class="form-control" required data-error="Por favor ingrese su teléfono">
                                <div class="help-block with-errors"></div>
                            </div>

                            <div class="form-group">
                                <label for="msg_subject">Asunto*</label>
                                <input type="text" name="msg_subject" id="msg_subject" class="form-control" required data-error="Por favor ingrese el asunto">
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>

                        <button type="submit" class="contact-submit-btn">Enviar mensaje</button>
                        <div id="msgSubmit" class="h3 text-center hidden"></div>
                    </form>
                </div>

                <div class="contact-branch-panel">
                    <div class="contact-section-title">
                        <h2>Nuestra tienda</h2>
                    </div>

                    <article class="contact-branch-card">
                        <div class="contact-branch-main">
                            <div class="contact-branch-photo">
                                <img src="assets/img/Sucursal/labroca_Sucursal.png" alt="Fachada de La Broca Ferreterías">
                            </div>

                            <div class="contact-branch-info">
                                <h3>La Broca Ferreter&iacute;as</h3>
                                <p class="contact-branch-address">32 avenida A, 28-31, zona 5, Santa Ana. A unos pasos del Mercado Santa Ana.</p>

                                <ul class="contact-branch-list">
                                    <li><span class="branch-status-dot"></span>Atenci&oacute;n en tienda</li>
                                    <li><i class='bx bx-phone-call'></i><a href="tel:+50243143784">+502 4314 3784</a><span>/</span><a href="https://wa.me/50243143784" target="_blank" rel="noopener">WhatsApp</a></li>
                                    <li><i class='bx bx-envelope'></i><a href="mailto:labrocaferreterias01@gmail.com">labrocaferreterias01@gmail.com</a></li>
                                    <li><i class='bx bx-time-five'></i>Abierto todos los d&iacute;as de 7:00 a. m. a 6:00 p. m.</li>
                                    <li><i class='bx bx-map'></i>A unos pasos del Mercado Santa Ana.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="contact-branch-actions">
                            <a class="contact-location-action contact-location-action-maps" href="<?php echo htmlspecialchars($contactGoogleMapsUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" aria-label="Abrir ubicación en Google Maps">
                                Ir con Maps
                                <img src="assets/img/Sucursal/GoogleMaps.png" alt="" aria-hidden="true">
                            </a>
                            <a class="contact-location-action contact-location-action-waze" href="<?php echo htmlspecialchars($contactWazeUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" aria-label="Abrir ubicación en Waze">
                                Ir con Waze
                                <img src="assets/img/Sucursal/Waze.png" alt="" aria-hidden="true">
                            </a>
                            <a class="contact-location-action contact-location-action-whatsapp" href="<?php echo htmlspecialchars($contactWhatsappUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" aria-label="Chatear por WhatsApp">
                                WhatsApp
                                <i class='bx bxl-whatsapp'></i>
                            </a>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
    .contact-redesign-page {
        background: #ffffff;
    }

    .contact-redesign-area {
        background: #ffffff;
        padding: 54px 0 66px;
    }

    .contact-redesign-shell {
        max-width: 1050px;
        margin: 0 auto;
    }

    .contact-form-panel {
        max-width: 945px;
    }

    .contact-section-title {
        border-bottom: 1px solid #cbd3e2;
        margin-bottom: 20px;
        position: relative;
    }

    .contact-section-title::after {
        background: #24376d;
        bottom: -1px;
        content: "";
        height: 2px;
        left: 0;
        position: absolute;
        width: 96px;
    }

    .contact-section-title h2 {
        color: #151515;
        font-size: 26px;
        font-weight: 700;
        line-height: 1.25;
        margin: 0;
        padding-bottom: 17px;
    }

    .contact-form-note {
        color: #283b61;
        font-size: 13px;
        line-height: 1.6;
        margin: 0 0 28px;
    }

    .contact-clean-form .form-group {
        margin-bottom: 22px;
    }

    .contact-hidden-field {
        position: absolute;
        left: -9999px;
        width: 1px;
        height: 1px;
        opacity: 0;
        overflow: hidden;
        pointer-events: none;
    }

    .contact-clean-form label {
        color: #151515;
        display: block;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.35;
        margin-bottom: 7px;
    }

    .contact-clean-form .form-control {
        background: #ffffff;
        border: 1px solid #d6deec;
        border-radius: 5px;
        box-shadow: none !important;
        color: #151515;
        font-size: 15px;
        height: 58px;
        padding: 12px 14px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .contact-clean-form .form-control:focus {
        border-color: #ffd000;
        box-shadow: 0 0 0 3px rgba(255, 208, 0, 0.18) !important;
    }

    .contact-clean-form textarea.form-control {
        height: 112px;
        min-height: 112px;
        padding-top: 12px;
        resize: vertical;
    }

    .contact-form-grid {
        display: grid;
        gap: 0 22px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .contact-submit-btn {
        align-items: center;
        background: #24376d;
        border: 0;
        border-radius: 4px;
        color: #ffffff;
        display: inline-flex;
        font-size: 14px;
        font-weight: 600;
        height: 52px;
        justify-content: center;
        min-width: 166px;
        padding: 0 28px;
        transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }

    .contact-submit-btn:hover {
        background: #ffd000;
        box-shadow: 0 10px 22px rgba(21, 21, 21, 0.16);
        color: #ffffff;
        transform: translateY(-1px);
    }

    .contact-submit-btn.disabled {
        opacity: 0.72;
    }

    .contact-clean-form #msgSubmit {
        margin: 16px 0 0;
        text-align: left !important;
    }

    .contact-branch-panel {
        margin-top: 44px;
    }

    .contact-branch-card {
        background: #ffffff;
        border: 1.5px solid #24376d;
        border-radius: 18px;
        box-shadow: 0 18px 45px rgba(36, 55, 109, 0.08);
        margin: 34px auto 0;
        max-width: 720px;
        padding: 30px 34px 28px;
    }

    .contact-branch-main {
        align-items: center;
        display: grid;
        gap: 24px;
        grid-template-columns: 285px minmax(0, 1fr);
    }

    .contact-branch-photo {
        align-self: center;
        border-radius: 14px;
        height: 180px;
        overflow: hidden;
    }

    .contact-branch-photo img {
        display: block;
        height: 100%;
        object-fit: cover;
        object-position: center;
        width: 100%;
    }

    .contact-branch-info h3 {
        color: #ffd000;
        font-size: 22px;
        font-weight: 700;
        line-height: 1.2;
        margin: 0 0 11px;
    }

    .contact-branch-address {
        border-bottom: 1px solid #d7deeb;
        color: #151515;
        font-size: 12px;
        line-height: 1.4;
        margin: 0;
        padding-bottom: 10px;
    }

    .contact-branch-list {
        list-style: none;
        margin: 10px 0 0;
        padding: 0;
    }

    .contact-branch-list li {
        align-items: center;
        color: #151515;
        display: flex;
        flex-wrap: wrap;
        font-size: 12px;
        gap: 7px;
        line-height: 1.3;
        margin-bottom: 7px;
    }

    .contact-branch-list i {
        color: #24376d;
        font-size: 15px;
        line-height: 1;
    }

    .contact-branch-list a {
        color: #151515;
    }

    .contact-branch-list a:hover {
        color: #ffd000;
    }

    .branch-status-dot {
        background: #3f6b54;
        border-radius: 50%;
        display: inline-block;
        height: 8px;
        margin-right: 5px;
        width: 8px;
    }

    .contact-branch-actions {
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        margin-top: 24px;
    }

    .contact-branch-actions a {
        align-items: center;
        background: #ffffff;
        border: 1px solid #d7dde8;
        border-radius: 999px;
        box-shadow: 0 8px 18px rgba(21, 21, 21, 0.04);
        color: #253044;
        display: inline-flex;
        font-size: 15px;
        font-weight: 600;
        gap: 10px;
        min-height: 54px;
        justify-content: center;
        line-height: 1;
        padding: 0 18px;
        text-decoration: none;
        transition: background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, color 0.2s ease, transform 0.2s ease;
    }

    .contact-branch-actions a:hover {
        background: #fffaf0;
        border-color: #ffd000;
        box-shadow: 0 12px 24px rgba(255, 208, 0, 0.14);
        color: #151515;
        transform: translateY(-1px);
    }

    .contact-branch-actions img {
        height: 24px;
        object-fit: contain;
        width: 24px;
    }

    .contact-branch-actions i {
        color: #1f8f5a;
        font-size: 23px;
    }

    @media (max-width: 991px) {
        .contact-redesign-area {
            padding: 42px 0 56px;
        }

        .contact-branch-card {
            max-width: 100%;
        }

        .contact-branch-main {
            grid-template-columns: 1fr;
        }

        .contact-branch-photo {
            height: 230px;
        }
    }

    @media (max-width: 767px) {
        .contact-redesign-area {
            padding: 34px 0 44px;
        }

        .contact-section-title h2 {
            font-size: 24px;
        }

        .contact-form-grid {
            grid-template-columns: 1fr;
        }

        .contact-submit-btn {
            width: 100%;
        }

        .contact-branch-card {
            border-radius: 16px;
            margin-top: 24px;
            padding: 22px;
        }

        .contact-branch-photo {
            height: 190px;
        }

        .contact-branch-actions {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include 'footer.php'; ?>
