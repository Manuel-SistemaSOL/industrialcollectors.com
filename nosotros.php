<?php
// SEO para la pagina nosotros
$seo_title = "Nosotros - La Broca Ferreterías";
$seo_description = "Conoce La Broca Ferreterías, ferretería fundada en 2012 con productos, herramientas y soluciones para hogares, profesionales y construcción.";
$seo_keywords = "nosotros la broca ferreterías, ferretería, herramientas, construcción, plomería, electricidad, pintura";

include 'head.php';
$current_page = basename($_SERVER['PHP_SELF']);

// Cargar datos desde la API
$apiUrl = "https://ssl.sol.sistemasolgt.com/brovic/api/api_tienda_nosotros.php";
$nosotros = null;

$ch = curl_init($apiUrl);
if ($ch !== false) {
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, []);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_PROXY, '');
    curl_setopt($ch, CURLOPT_NOPROXY, '*');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response !== false && empty($curlError) && $httpCode == 200) {
        $data = json_decode($response, true);

        if ($data !== null && isset($data['success']) && $data['success'] === true && !empty($data['data']) && is_array($data['data'])) {
            $nosotros = $data['data'][0];
        }
    }
}

$rutaImagenes = "https://ssl.sol.sistemasolgt.com/brovic/files/articulos/";
?>

<div class="page-title-area">
    <div class="container">
        <div class="page-title-content">
            <h2>Sobre Nosotros</h2>
            <ul>
                <li><a href="/">Inicio</a></li>
                <li>Nosotros</li>
            </ul>
        </div>
    </div>
</div>

<style>
    .about-layout {
        background: #e5e5e5;
        padding: 32px 0 60px;
    }

    .about-panel,
    .about-mini-card,
    .about-difference-card {
        background: #ffffff;
        border-radius: 28px;
        box-shadow: 0 16px 36px rgba(28, 46, 110, 0.08);
        position: relative;
        overflow: hidden;
    }

    .about-panel::before,
    .about-mini-card::before,
    .about-difference-card::before,
    .about-panel::after,
    .about-mini-card::after,
    .about-difference-card::after {
        content: "";
        position: absolute;
        left: 0;
        width: 100%;
        height: 6px;
        background: #151515;
        pointer-events: none;
    }

    .about-panel::before,
    .about-mini-card::before,
    .about-difference-card::before {
        top: 0;
    }

    .about-panel::after,
    .about-mini-card::after,
    .about-difference-card::after {
        bottom: 0;
    }

    .about-panel {
        padding: 40px 34px 28px;
        margin-bottom: 20px;
    }

    .about-panel-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(320px, .95fr);
        gap: 30px;
        align-items: stretch;
    }

    .about-copy h3,
    .about-mini-card h3,
    .about-difference-card h3 {
        margin: 0 0 22px;
        color: #1b2e69;
        font-size: 34px;
        font-weight: 800;
        line-height: 1.08;
    }

    .about-copy-text {
        color: #24324f;
        font-size: 18px;
        line-height: 1.95;
    }

    .about-copy-text p,
    .about-copy-text div {
        margin-bottom: 16px;
    }

    .about-copy-text p:last-child,
    .about-copy-text div:last-child {
        margin-bottom: 0;
    }

    .about-art-card {
        min-height: 100%;
        padding: 12px;
        position: relative;
        background: transparent;
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .about-art-card::before,
    .about-art-card::after {
        display: none;
    }

    .about-art-card img {
        width: 100%;
        max-width: 420px;
        max-height: 420px;
        object-fit: cover;
        border-radius: 22px;
        display: block;
        box-shadow: none;
        background: transparent;
    }

    .about-info-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .about-mini-card {
        padding: 34px 32px 28px;
        min-height: 100%;
    }

    .about-mini-card p {
        margin-bottom: 0;
        color: #24324f;
        font-size: 17px;
        line-height: 1.9;
    }

    .about-difference-card {
        padding: 34px 32px 26px;
    }

    .about-difference-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        gap: 16px;
    }

    .about-difference-list li {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        color: #22304e;
        font-size: 17px;
        line-height: 1.7;
    }

    .about-difference-list li i {
        color: #ffd000;
        margin-top: 5px;
        font-size: 14px;
    }

    @media (max-width: 991px) {
        .about-layout {
            padding: 26px 0 44px;
        }

        .about-panel-grid,
        .about-info-row {
            grid-template-columns: 1fr;
        }

        .about-panel {
            padding: 28px 22px 24px;
        }

        .about-copy h3,
        .about-mini-card h3,
        .about-difference-card h3 {
            font-size: 28px;
        }

        .about-copy-text,
        .about-mini-card p,
        .about-difference-list li {
            font-size: 16px;
            line-height: 1.85;
        }

        .about-art-card {
            min-height: 320px;
            padding: 20px;
        }
    }

    @media (max-width: 767px) {

        .about-panel,
        .about-mini-card,
        .about-difference-card {
            border-radius: 22px;
        }

        .about-panel {
            padding: 24px 18px 20px;
            margin-bottom: 16px;
        }

        .about-mini-card,
        .about-difference-card {
            padding: 26px 18px 20px;
        }

        .about-copy h3,
        .about-mini-card h3,
        .about-difference-card h3 {
            font-size: 24px;
            margin-bottom: 18px;
        }

        .about-copy-text,
        .about-mini-card p,
        .about-difference-list li {
            font-size: 15px;
            line-height: 1.8;
        }

        .about-art-card {
            min-height: 260px;
            padding: 16px;
        }

        .about-art-card img {
            max-height: 300px;
        }
    }
</style>

<section class="about-layout">
    <div class="container">
        <div class="about-panel">
            <div class="about-panel-grid">
                <div class="about-copy">
                    <h3>Historia de la Empresa</h3>

                    <div class="about-copy-text">
                        <?php if ($nosotros && !empty($nosotros['historia_empresa'])): ?>
                            <div><?= nl2br(htmlspecialchars($nosotros['historia_empresa'])) ?></div>
                        <?php else: ?>
                            <p>La Broca Ferreter&iacute;as inici&oacute; en 2012 con un objetivo claro: abrirse paso en el mercado y convertirse en un aliado de confianza para cada proyecto.</p>
                            <p>Su crecimiento ha sido resultado del esfuerzo diario, la superaci&oacute;n constante de retos y el compromiso con sus clientes. Con el paso de los a&ntilde;os ha construido una trayectoria basada en la calidad, la resiliencia, la atenci&oacute;n personalizada y el buen servicio.</p>
                            <p>Actualmente contin&uacute;a creciendo con la misma pasi&oacute;n de sus inicios y busca ampliar su presencia tambi&eacute;n mediante su plataforma digital.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="about-art-card">
                    <?php if ($nosotros && !empty($nosotros['imagen_nosotros'])): ?>
                        <img src="<?= $rutaImagenes . htmlspecialchars($nosotros['imagen_nosotros']) ?>" alt="La Broca Ferreterías - Nosotros">
                    <?php else: ?>
                        <img src="assets/img/Sucursal/labroca_Sucursal.png" alt="Sucursal de La Broca Ferreterías">
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="about-info-row">
            <div class="about-mini-card">
                <h3>Misión</h3>
                <?php if ($nosotros && !empty($nosotros['mision_nosotros'])): ?>
                    <p><?= nl2br(htmlspecialchars($nosotros['mision_nosotros'])) ?></p>
                <?php else: ?>
                    <p>Brindar a nuestros clientes productos de ferreter&iacute;a, herramientas y soluciones de calidad, ofreciendo una experiencia de compra f&aacute;cil, r&aacute;pida y confiable tanto en nuestra tienda f&iacute;sica como online. Trabajamos para satisfacer las necesidades de hogares, profesionales y proyectos de construcci&oacute;n, mediante atenci&oacute;n personalizada, precios competitivos y un servicio comprometido.</p>
                <?php endif; ?>
            </div>

            <div class="about-mini-card">
                <h3>Visión</h3>
                <?php if ($nosotros && !empty($nosotros['vision_nosotros'])): ?>
                    <p><?= nl2br(htmlspecialchars($nosotros['vision_nosotros'])) ?></p>
                <?php else: ?>
                    <p>Ser una ferreter&iacute;a reconocida y de confianza, destac&aacute;ndonos por nuestra amplia variedad de productos, precios competitivos, atenci&oacute;n de calidad y rapidez en el servicio. Buscamos crecer constantemente y convertirnos en una de las opciones preferidas para quienes necesitan herramientas, materiales y soluciones para sus proyectos.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="about-difference-card">
            <h3>¿Qué nos diferencia de la competencia?</h3>

            <ul class="about-difference-list">
                <?php if ($nosotros && !empty($nosotros['diferencia_nosotros'])): ?>
                    <?php
                    $diferencias = preg_split("/\r\n|\n|\r/", $nosotros['diferencia_nosotros']);
                    foreach ($diferencias as $diferencia):
                        $diferencia = trim($diferencia);
                        if (!empty($diferencia)):
                    ?>
                            <li><i class="flaticon-check"></i><span><?= htmlspecialchars($diferencia) ?></span></li>
                    <?php
                        endif;
                    endforeach;
                    ?>
                <?php else: ?>
                    <li><i class="flaticon-check"></i><span>Productos de ferreter&iacute;a y herramientas para hogares, profesionales y construcci&oacute;n.</span></li>
                    <li><i class="flaticon-check"></i><span>Atenci&oacute;n personalizada y servicio comprometido.</span></li>
                    <li><i class="flaticon-check"></i><span>Precios competitivos y experiencia de compra confiable.</span></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
