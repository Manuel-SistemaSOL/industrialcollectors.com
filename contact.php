<?php
// SEO para la página de contacto
$seo_title = "Contacto | Industrial Collectors, S.A.";

$seo_description = "Comunícate con Industrial Collectors, S.A. para obtener información sobre materiales metálicos, disponibilidad y atención comercial.";

$seo_canonical_url = "https://industrialcollectors.com/contact.php";

$seo_robots = "index, follow, max-image-preview:large";

$current_page = basename($_SERVER['PHP_SELF']);

include 'head.php';

$contactMapsRedirectUrl = 'https://www.google.com/maps/place/Industrial+Collectors,+Sociedad+Anonima/@14.4997744,-90.5727928,17z/data=!3m1!4b1!4m6!3m5!1s0x8589a7d8fa371829:0x595712d23c4afc73!8m2!3d14.4997692!4d-90.5702179!16s%2Fg%2F11s4x7wnjq';
$contactGoogleMapsUrl = $contactMapsRedirectUrl;
$contactWazeUrl = $contactMapsRedirectUrl;
$contactBranch2MapsUrl = 'https://maps.app.goo.gl/3STSkC7LyjqwT6Ai9?g_st=aw';
$contactWhatsappUrl = 'https://wa.me/50230888818';
$contactHeroImage = file_exists(__DIR__ . '/assets/img/paginaImagenes/contactanos.png') ? 'assets/img/paginaImagenes/contactanos.png' : (file_exists(__DIR__ . '/assets/img/ImagenTemporalINDUSTRIALCOLLECTORS.png') ? 'assets/img/ImagenTemporalINDUSTRIALCOLLECTORS.png' : 'assets/img/404.png');
$contactHeroImageVersion = file_exists(__DIR__ . '/' . $contactHeroImage) ? filemtime(__DIR__ . '/' . $contactHeroImage) : '1';
$contactHeroImageSrc = $contactHeroImage . '?v=' . $contactHeroImageVersion;
?>

<?php bmRenderPageTitle('Contacto'); ?>

<section class="category-intro-area bm-special-intro bm-promotions-intro bm-contact-intro" aria-label="Contáctanos">
    <div class="container">
        <article class="category-intro-card">
            <div class="category-intro-image">
                <img src="<?php echo htmlspecialchars($contactHeroImageSrc, ENT_QUOTES, 'UTF-8'); ?>" alt="Contacto con Industrial Collectors, S.A." loading="eager" decoding="async">
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
                        <h2>Nuestras sucursales</h2>
                    </div>

                    <article class="contact-branch-card">
                        <div class="contact-branch-main">
                            <div class="contact-branch-photo">
                                <img src="assets/img/Sucursal/Fachada de Industrial Collectors.png" alt="Fachada de Industrial Collectors, S.A.">
                            </div>

                            <div class="contact-branch-info">
                                <h3>Industrial Collectors, S.A. — Sucursal 1</h3>
                                <p class="contact-branch-address">6 calle 13-10, zona 10, Finca El Carmen, San Miguel Petapa, Guatemala</p>

                                <ul class="contact-branch-list">
                                    <li><span class="branch-status-dot"></span>Atenci&oacute;n en tienda</li>
                                    <li><i class='bx bx-phone-call'></i><a href="tel:+50232001888">+502 3200 1888</a><span>/</span><a href="https://wa.me/50230888818" target="_blank" rel="noopener">WhatsApp: 3088-8818</a></li>
                                    <li><i class='bx bx-envelope'></i><a href="mailto:industrialcollectorsventas@gmail.com">industrialcollectorsventas@gmail.com</a></li>
                                    <li><i class='bx bx-time-five'></i>Lunes a viernes: 7:00 a. m. a 6:00 p. m. Sábado: 7:00 a. m. a 4:00 p. m.</li>
                                    <li><i class='bx bx-map'></i>Finca El Carmen, San Miguel Petapa.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="contact-branch-actions">
                            <a class="contact-location-action contact-location-action-maps" href="<?php echo htmlspecialchars($contactGoogleMapsUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" aria-label="Abrir ubicación de sucursal 1 en Google Maps">
                                Ir con Maps
                                <img src="assets/img/Sucursal/GoogleMaps.png" alt="" aria-hidden="true">
                            </a>
                            <a class="contact-location-action contact-location-action-waze" href="<?php echo htmlspecialchars($contactWazeUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" aria-label="Abrir ubicación de sucursal 1 en Waze">
                                Ir con Waze
                                <img src="assets/img/Sucursal/Waze.png" alt="" aria-hidden="true">
                            </a>
                            <a class="contact-location-action contact-location-action-whatsapp" href="<?php echo htmlspecialchars($contactWhatsappUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" aria-label="Chatear por WhatsApp">
                                WhatsApp
                                <i class='bx bxl-whatsapp'></i>
                            </a>
                        </div>
                    </article>

                    <article class="contact-branch-card">
                        <div class="contact-branch-main">
                            <div class="contact-branch-photo">
                                <img src="assets/img/Sucursal/Fachada de Industrial Collectors.png" alt="Fachada de Industrial Collectors, S.A.">
                            </div>

                            <div class="contact-branch-info">
                                <h3>Industrial Collectors, S.A. — Sucursal 2</h3>
                                <p class="contact-branch-address">Calle Real a San Miguel Petapa, 21-20 zona 10, La Enriqueta, Guatemala</p>

                                <ul class="contact-branch-list">
                                    <li><span class="branch-status-dot"></span>Atenci&oacute;n en tienda</li>
                                    <li><i class='bx bx-phone-call'></i><a href="tel:+50232001888">+502 3200 1888</a><span>/</span><a href="https://wa.me/50230888818" target="_blank" rel="noopener">WhatsApp: 3088-8818</a></li>
                                    <li><i class='bx bx-envelope'></i><a href="mailto:industrialcollectorsventas@gmail.com">industrialcollectorsventas@gmail.com</a></li>
                                    <li><i class='bx bx-time-five'></i>Lunes a viernes: 7:00 a. m. a 6:00 p. m. Sábado: 7:00 a. m. a 4:00 p. m.</li>
                                    <li><i class='bx bx-map'></i>La Enriqueta, Guatemala.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="contact-branch-actions">
                            <a class="contact-location-action contact-location-action-maps" href="<?php echo htmlspecialchars($contactBranch2MapsUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" aria-label="Abrir ubicación de sucursal 2 en Google Maps">
                                Ir con Maps
                                <img src="assets/img/Sucursal/GoogleMaps.png" alt="" aria-hidden="true">
                            </a>
                            <a class="contact-location-action contact-location-action-waze" href="<?php echo htmlspecialchars($contactBranch2MapsUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" aria-label="Abrir ubicación de sucursal 2 en Waze">
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
        background: #1D71B8;
        bottom: -1px;
        content: "";
        height: 2px;
        left: 0;
        position: absolute;
        width: 96px;
    }

    .contact-section-title h2 {
        color: #1D1D1B;
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
        color: #1D1D1B;
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
        color: #1D1D1B;
        font-size: 15px;
        height: 58px;
        padding: 12px 14px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .contact-clean-form .form-control:focus {
        border-color: #1D71B8;
        box-shadow: 0 0 0 3px rgba(29, 113, 184, 0.18) !important;
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
        background: #1D71B8;
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
        background: #1D71B8;
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
        border: 1.5px solid #1D71B8;
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
        color: #1D71B8;
        font-size: 22px;
        font-weight: 700;
        line-height: 1.2;
        margin: 0 0 11px;
    }

    .contact-branch-address {
        border-bottom: 1px solid #d7deeb;
        color: #1D1D1B;
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
        color: #1D1D1B;
        display: flex;
        flex-wrap: wrap;
        font-size: 12px;
        gap: 7px;
        line-height: 1.3;
        margin-bottom: 7px;
    }

    .contact-branch-list i {
        color: #1D71B8;
        font-size: 15px;
        line-height: 1;
    }

    .contact-branch-list a {
        color: #1D1D1B;
    }

    .contact-branch-list a:hover {
        color: #1D71B8;
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
        background: #F3F5F7;
        border-color: #1D71B8;
        box-shadow: 0 12px 24px rgba(29, 113, 184, 0.14);
        color: #1D1D1B;
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
