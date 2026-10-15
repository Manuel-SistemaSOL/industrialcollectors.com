<?php
if (!headers_sent()) {
    header("X-Frame-Options: SAMEORIGIN");
    header("X-Content-Type-Options: nosniff");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
    header("Content-Security-Policy: default-src 'self' https: data: blob: 'unsafe-inline' 'unsafe-eval'; img-src 'self' https: data: blob:; style-src 'self' https: 'unsafe-inline'; script-src 'self' https: 'unsafe-inline' 'unsafe-eval'; frame-src https://www.google.com https://maps.google.com;");
}

function getApi($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, []);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_PROXY, '');
    curl_setopt($ch, CURLOPT_NOPROXY, '*');
    $response = curl_exec($ch);

    if ($response === false) {
        error_log("Error CURL: " . curl_error($ch));
    }

    curl_close($ch);
    return $response;
}

// Definir variables para evitar errores
if (!isset($current_page)) {
    $current_page = basename($_SERVER['PHP_SELF']);
}

include "assets/php/rutas.php";

$bm_site_logo = 'assets/img/logotipo IC/version horizontal/Logo-Industrial-Collectors-para-fondo-oscuro-horizontal.png';
$bm_site_logo_fallback = 'assets/img/logotipo IC/version horizontal/Logo-Industrial-Collectors-horizontal.png';
$bm_loader_logo = 'assets/img/logotipo IC/version horizontal/Logo-Industrial-Collectors-horizontal.png';
$bm_loader_logo_fallback = $bm_site_logo;
$bm_header_search_value = isset($_GET['buscar']) ? trim((string) $_GET['buscar']) : '';

$response = getApi($url_listar_categorias);
$data = json_decode($response, true);

$categorias = $data["data"] ?? [];

$bmMainMenu = [
    [
        'label' => 'Inicio',
        'url' => 'index.php',
        'pages' => ['index.php'],
    ],
    [
        'label' => 'Productos',
        'url' => 'tienda.php',
        'pages' => ['tienda.php', 'producto.php', 'gifscard.php'],
    ],
    [
        'label' => 'Ofertas',
        'url' => 'ofertas.php',
        'pages' => ['ofertas.php'],
    ],
    [
        'label' => 'Menos de 100',
        'url' => 'menosde100.php',
        'pages' => ['menosde100.php'],
    ],
    [
        'label' => 'Contáctanos',
        'url' => 'contact.php',
        'pages' => ['contact.php'],
    ],
];

$bmDesktopMenu = $bmMainMenu;
$bmDesktopMenu[] = [
    'label' => 'Carrito',
    'url' => 'cart.php',
    'pages' => ['cart.php'],
    'icon' => 'flaticon-shopping-cart',
    'class' => 'bm-menu-cart-link',
];

$bmMobileMenu = $bmMainMenu;
$bmMobileMenu[] = [
    'label' => 'Carrito',
    'url' => 'cart.php',
    'pages' => ['cart.php'],
    'icon' => 'flaticon-shopping-cart',
    'class' => 'cart-icon-navbar bm-menu-cart-link',
];

if (!function_exists('bmRenderPageTitle')) {
    function bmRenderPageTitle($titulo, $actual = null)
    {
        $tituloSeguro = htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8');
        $actualSeguro = htmlspecialchars($actual ?: $titulo, ENT_QUOTES, 'UTF-8');

        echo <<<HTML
<div class="page-title-area bm-page-title-clean">
    <div class="container">
        <div class="page-title-content">
            <h2>{$tituloSeguro}</h2>
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li>{$actualSeguro}</li>
            </ul>
        </div>
    </div>
</div>
HTML;
    }
}

?>

<!doctype html>
<html lang="es-GT">

<head>
    
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css?v=<?php echo filemtime('assets/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="assets/css/animate.min.css?v=<?php echo filemtime('assets/css/animate.min.css'); ?>">
    <link rel="stylesheet" href="assets/css/meanmenu.css?v=<?php echo filemtime('assets/css/meanmenu.css'); ?>">
    <link rel="stylesheet" href="assets/css/boxicons.min.css?v=<?php echo filemtime('assets/css/boxicons.min.css'); ?>">
    <link rel="stylesheet" href="assets/css/flaticon.css?v=<?php echo filemtime('assets/css/flaticon.css'); ?>">
    <link rel="stylesheet" href="assets/css/owl.carousel.min.css?v=<?php echo filemtime('assets/css/owl.carousel.min.css'); ?>">
    <link rel="stylesheet" href="assets/css/owl.theme.default.min.css?v=<?php echo filemtime('assets/css/owl.theme.default.min.css'); ?>">
    <link rel="stylesheet" href="assets/css/magnific-popup.min.css?v=<?php echo filemtime('assets/css/magnific-popup.min.css'); ?>">
    <link rel="stylesheet" href="assets/css/nice-select.min.css?v=<?php echo filemtime('assets/css/nice-select.min.css'); ?>">
    <link rel="stylesheet" href="assets/css/slick.min.css?v=<?php echo filemtime('assets/css/slick.min.css'); ?>">
    <link rel="stylesheet" href="assets/css/odometer.min.css?v=<?php echo filemtime('assets/css/odometer.min.css'); ?>">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo filemtime('assets/css/style.css'); ?>">
    <link rel="stylesheet" href="assets/css/dark.css?v=<?php echo filemtime('assets/css/dark.css'); ?>">
    <link rel="stylesheet" href="assets/css/responsive.css?v=<?php echo filemtime('assets/css/responsive.css'); ?>">

    <?php
    // Configuración SEO por defecto
    $site_url = 'https://industrialcollectors.com';

    $request_uri = $_SERVER['REQUEST_URI'] ?? '/';
    $current_url = $site_url . $request_uri;

    $seo_title = $seo_title ??
        'Industrial Collectors, S.A. | Materiales Metálicos';

    $seo_description = $seo_description ??
        'Perfilería, láminas, tubos, vigas, costaneras e hierro de construcción para ventas al por mayor y menor.';

    $seo_image = $seo_image ??
        $site_url . '/assets/img/logotipo IC/version horizontal/Logo-Industrial-Collectors-horizontal.png';

    $seo_robots = $seo_robots ??
        'index, follow, max-image-preview:large';

    $canonical_url = $seo_canonical_url ??
        $site_url . '/' . rawurlencode($current_page);

    if ($current_page === 'index.php') {
        $canonical_url = $site_url . '/';
    }
    ?>

    <!-- SEO Meta Tags -->
    <title><?php echo htmlspecialchars($seo_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seo_description); ?>">
    <meta name="author" content="Industrial Collectors, S.A.">
    <meta name="robots" content="<?php echo htmlspecialchars($seo_robots, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical_url, ENT_QUOTES, 'UTF-8'); ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($canonical_url, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($seo_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($seo_description); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($seo_image); ?>">
    <meta property="og:locale" content="es_GT">
    <meta property="og:site_name" content="Industrial Collectors, S.A.">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo htmlspecialchars($canonical_url, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($seo_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($seo_description); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($seo_image); ?>">

    <!-- Schema.org structured data -->
    <script type="application/ld+json">
                {
                "@context": "https://schema.org",
                "@type": "Store",
                "name": "Industrial Collectors, S.A.",
                "url": "https://industrialcollectors.com",
                "logo": "https://industrialcollectors.com/assets/img/logotipo IC/version horizontal/Logo-Industrial-Collectors-horizontal.png",
                "description": "Distribución de materiales metálicos para proyectos de construcción, con ventas al por mayor y menor.",
                "address": {
                    "@type": "PostalAddress",
                    "streetAddress": "6 calle 13-10, zona 10, Finca El Carmen, San Miguel Petapa, Guatemala",
                    "addressLocality": "San Miguel Petapa",
                    "addressCountry": "GT"
                },
                "contactPoint": {
                    "@type": "ContactPoint",
                    "telephone": "+502-3200-1888",
                    "contactType": "Atención al cliente"
                },
                "openingHours": ["Mo-Fr 07:00-18:00", "Sa 07:00-16:00"],
                "sameAs": [
                    "https://www.facebook.com/profile.php?id=100089824832535",
                    "https://www.tiktok.com/@colectoresindustriales",
                    "https://www.google.com/maps/place/Industrial+Collectors,+Sociedad+Anonima/@14.4997744,-90.5727928,17z/data=!3m1!4b1!4m6!3m5!1s0x8589a7d8fa371829:0x595712d23c4afc73!8m2!3d14.4997692!4d-90.5702179!16s%2Fg%2F11s4x7wnjq"
                ]
                }
                </script>


    <script type="application/ld+json">
                    {
                    "@context": "https://schema.org",
                    "@type": "WebSite",
                    "name": "Industrial Collectors, S.A.",
                    "url": "<?php echo $site_url; ?>",
                    "potentialAction": {
                        "@type": "SearchAction",
                        "target": "<?php echo $site_url; ?>/tienda.php?buscar={search_term_string}",
                        "query-input": "required name=search_term_string"
                    }
                    }
                </script>


    <link rel="icon" type="image/png" href="assets/img/logotipo IC/version horizontal/Logo-Industrial-Collectors-horizontal.png">
    <?php
    if (!empty($extra_head_content)) {
        echo $extra_head_content;
    }
    ?>
</head>

<body>
    <noscript><style>.preloader { display: none !important; }</style></noscript>

    <!-- Start Preloader Area -->
    <div class="preloader">
        <div class="loader">
            <img src="<?php echo htmlspecialchars($bm_loader_logo, ENT_QUOTES); ?>" alt="Cargando..." class="bm-loader-logo"
                onerror="this.src='<?php echo htmlspecialchars($bm_loader_logo_fallback, ENT_QUOTES); ?>'; this.onerror=null;">
        </div>
    </div>
    <!-- End Preloader Area -->


    <!-- End Top Header Area -->

    <style>
        .logo-grande {
            width: auto !important;
            height: auto !important;
            max-height: 78px !important;
            max-width: 100% !important;
            object-fit: contain !important;
            display: block;
        }

        .bm-loader-logo {
            width: clamp(190px, 14vw, 320px);
            height: auto;
            max-width: min(56vw, 340px);
            display: block;
        }

        @media (max-width: 768px) {
            .logo-grande {
                max-height: 68px !important;
            }
        }

        @media (max-width: 480px) {
            .logo-grande {
                max-height: 54px !important;
            }
        }

        /* Estilos para el icono del carrito */
        .cart-icon {
            position: relative;
            display: inline-block;
            font-size: 24px;
            color: #333;
            text-decoration: none;
        }

        .cart-icon:hover {
            color: #1D71B8;
        }

        .cart-icon .flaticon-shopping-cart {
            font-size: 24px;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #1D71B8;
            color: #1D1D1B;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            min-width: 20px;
        }

        .cart-count:empty {
            display: none;
        }

        .middle-header-optional li {
            list-style: none;
        }

        /* Logo en el header superior */
        .logo-header {
            max-width: 180px;
            height: auto;
        }

        /* Asegurar que el icono del carrito sea visible */
        .middle-header-optional {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .middle-header-optional .cart-icon {
            font-size: 24px;
            color: #333;
            text-decoration: none;
            padding: 5px;
        }

        .middle-header-optional .cart-icon i {
            font-size: 24px;
        }

        /* Logo en el navbar principal */
        .navbar-brand {
            padding: 6px 10px;
            margin-right: 0;
            display: inline-flex;
            align-items: center;
            flex: 0 0 auto;
            background: #ffffff;
            border: 1px solid rgba(29, 113, 184, 0.72);
            border-radius: 6px;
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.14);
        }

        .main-responsive-logo a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 5px 8px;
            background: #ffffff;
            border: 1px solid rgba(29, 113, 184, 0.72);
            border-radius: 6px;
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.14);
            text-decoration: none;
        }

        .logo-navbar {
            width: auto;
            max-width: 138px;
            max-height: 92px;
            height: auto;
            object-fit: contain;
            display: block;
        }

        .main-navbar > .container {
            max-width: 1680px;
        }

        .main-navbar .navbar {
            position: relative;
            min-height: 78px;
            padding-top: 8px;
            padding-bottom: 8px;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .main-navbar .navbar .collapse.navbar-collapse.mean-menu {
            flex: 1 1 auto;
            min-width: 0;
            align-items: center;
            justify-content: center;
        }

        .main-navbar .navbar .navbar-nav .nav-item a {
            display: flex;
            align-items: center;
            min-height: 46px;
        }

        @media (max-width: 991px) {
            .navbar-brand {
                display: none;
            }
        }

        /* Icono del carrito en el navbar */
        .cart-icon-navbar {
            position: relative;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cart-icon-navbar i {
            font-size: 20px;
        }

        .cart-count-navbar {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
            min-width: 18px;
        }

        .cart-count-navbar:empty {
            display: none;
        }

        .shipping-promo-bar {
            background: #1D1D1B;
            box-sizing: border-box;
            color: #ffffff;
            min-height: 58px;
            display: grid;
            grid-template-columns: minmax(150px, 0.6fr) minmax(460px, 1.35fr) minmax(190px, 0.6fr);
            align-items: center;
            justify-content: center;
            gap: clamp(18px, 2.8vw, 44px);
            text-align: left;
            padding: 8px clamp(18px, 4vw, 64px);
            font-size: 14px;
            font-weight: 500;
            line-height: 1.35;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .shipping-promo-bar strong {
            font-weight: 700;
            margin: 0 0.35em;
        }

        .shipping-promo-message {
            display: inline-flex;
            align-items: center;
            justify-content: flex-start;
            gap: 10px;
            grid-column: auto;
            justify-self: start;
            min-width: 0;
            white-space: normal;
            font-size: clamp(12px, 1.02vw, 15px);
            font-weight: 800;
            line-height: 1.2;
        }

        .shipping-promo-logo-link {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }

        .shipping-promo-logo {
            display: block;
            width: clamp(142px, 12vw, 230px);
            height: auto;
            max-height: 54px;
            object-fit: contain;
        }

        .shipping-promo-delivery {
            align-items: center;
            color: #1D1D1B;
            display: inline-flex;
            font-size: clamp(12px, 0.95vw, 15px);
            font-weight: 800;
            gap: 8px;
            justify-content: flex-end;
            justify-self: end;
            line-height: 1.2;
            min-width: 0;
            text-align: right;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .shipping-promo-delivery-icon {
            display: inline-flex;
            font-size: 19px;
            line-height: 1;
        }

        .shipping-promo-mobile-delivery,
        .mobile-header-search-panel {
            display: none;
        }

        .shipping-promo-bar .header-search-panel {
            justify-self: stretch;
            min-width: 0;
            width: 100%;
        }

        .shipping-promo-bar,
        .shipping-promo-bar * {
            box-sizing: border-box;
        }

        .shipping-promo-emoji {
            display: inline-flex;
            align-items: center;
            font-size: 20px;
            line-height: 1;
        }

        .shipping-promo-flag {
            width: 42px;
            height: 28px;
            display: inline-block;
            flex: 0 0 auto;
            object-fit: contain;
        }

        .shipping-account-link {
            grid-column: 3;
            justify-self: end;
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0;
            text-decoration: none;
        }

        .shipping-account-link:hover,
        .shipping-account-link:focus {
            color: #ffffff;
            text-decoration: none;
        }

        .bm-menu-toggle {
            width: 42px;
            height: 42px;
            border: 0;
            background: transparent;
            color: #1D1D1B;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            cursor: pointer;
        }

        .bm-menu-toggle i {
            font-size: 32px;
            line-height: 1;
        }

        .bm-desktop-nav {
            align-items: center;
            flex: 1 1 auto;
            flex-wrap: nowrap;
            justify-content: center;
            min-width: 0;
            gap: clamp(18px, 3.1vw, 68px);
        }

        .bm-desktop-nav .nav-item {
            position: relative;
        }

        .bm-desktop-nav .nav-link {
            color: #151923 !important;
            font-family: "Manrope", Arial, sans-serif;
            font-size: clamp(18px, 1.55vw, 30px);
            font-weight: 700;
            letter-spacing: 0;
            line-height: 1.15;
            text-transform: none;
            padding: 10px 0 !important;
            white-space: nowrap;
            transition: color 0.22s ease, transform 0.22s ease;
        }

        .bm-desktop-nav .nav-link i {
            color: currentColor;
            flex: 0 0 auto;
            font-size: 1.05em;
            line-height: 1;
            margin-right: 8px;
        }

        .bm-desktop-nav .nav-link:hover,
        .bm-desktop-nav .nav-link:focus,
        .bm-desktop-nav .nav-link.is-active {
            color: #1D1D1B !important;
            outline: none;
        }

        .bm-desktop-nav .nav-link:hover,
        .bm-desktop-nav .nav-link:focus {
            transform: translateY(-1px);
        }

        .header-navbar-actions,
        .main-responsive-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-navbar-actions {
            margin-left: 12px;
            flex-shrink: 0;
        }

        .header-action-btn,
        .header-action-link {
            width: 42px;
            height: 42px;
            border: 0;
            border-radius: 50%;
            background: transparent;
            color: #1D1D1B;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            position: relative;
            cursor: pointer;
            transition: background-color 0.25s ease, color 0.25s ease, transform 0.25s ease;
        }

        .header-action-btn i,
        .header-action-link i {
            font-size: 24px;
            line-height: 1;
        }

        .header-action-btn:hover,
        .header-action-btn:focus,
        .header-action-link:hover,
        .header-action-link:focus {
            background: #F3F5F7;
            color: #1D1D1B;
            transform: translateY(-1px);
            outline: none;
        }

        .cart-icon-navbar {
            position: relative;
            justify-content: center;
            gap: 8px;
        }

        .cart-icon-navbar i {
            font-size: 23px;
            line-height: 1;
        }

        .cart-count-navbar {
            top: 1px;
            right: 0;
            background-color: #1D1D1B;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(21, 21, 21, 0.24);
        }

        .bm-menu-cart-link .cart-count-navbar {
            top: -6px;
            right: -16px;
        }

        .bm-desktop-nav .bm-menu-cart-link {
            align-items: center;
            gap: 8px;
            position: relative;
        }

        .bm-desktop-nav .bm-menu-cart-link i {
            margin-right: 0;
        }

        .main-responsive-actions .cart-icon-navbar {
            gap: 0;
        }

        .main-responsive-actions .cart-icon-navbar .cart-count-navbar {
            top: 1px;
            right: 0;
        }

        .header-search-panel {
            background: transparent;
            border: 0;
            box-shadow: none;
            max-height: none;
            opacity: 1;
            overflow: visible;
            visibility: visible;
            width: 100%;
        }

        .header-search-panel.is-open {
            opacity: 1;
            visibility: visible;
        }

        .header-search-form {
            display: grid;
            grid-template-columns: minmax(260px, 1fr);
            padding: 0;
            align-items: center;
            max-width: none;
            margin: 0 auto;
            width: 100%;
        }

        .header-search-box .form-control {
            height: 44px;
            border-radius: 999px;
            border: 2px solid rgba(29, 113, 184, 0.82);
            background: #ffffff;
            color: #292929;
            font-size: 14px;
            font-weight: 600;
            width: 100%;
        }

        .header-search-box {
            position: relative;
            width: 100%;
        }

        .header-search-box .form-control {
            padding: 8px 54px 8px 18px;
            box-shadow: 0 6px 16px rgba(21, 21, 21, 0.16);
        }

        .header-search-box .form-control:focus {
            border-color: #1D71B8;
            box-shadow: 0 0 0 3px rgba(29, 113, 184, 0.22), 0 6px 16px rgba(21, 21, 21, 0.16);
        }

        .header-search-box button {
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 50%;
            background: #1D1D1B;
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            right: 4px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            transition: background-color 0.25s ease, color 0.25s ease, transform 0.25s ease;
        }

        .header-search-box button:hover,
        .header-search-box button:focus {
            background: #1D71B8;
            color: #1D1D1B;
        }

        .header-search-box button i {
            font-size: 22px;
        }

        .main-responsive-actions {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            z-index: 9992;
        }

        @media (max-width: 991px) {
            .bm-loader-logo {
                width: clamp(128px, 22vw, 190px);
                max-width: 42vw;
            }

            .shipping-promo-bar {
                grid-template-columns: 1fr;
                gap: 5px;
                min-height: 96px;
                padding: 10px 14px 9px;
                text-align: center;
            }

            .shipping-promo-message {
                gap: 0;
                font-size: 11px;
                justify-content: center;
                justify-self: center;
                text-align: center;
                width: 100%;
            }

            .shipping-promo-logo {
                width: clamp(148px, 24vw, 210px);
                max-height: 58px;
            }

            .shipping-promo-mobile-delivery {
                color: #1D1D1B;
                display: block;
                font-size: clamp(12px, 1.8vw, 15px);
                font-weight: 800;
                line-height: 1.15;
                text-align: center;
                width: 100%;
            }

            .shipping-promo-delivery {
                display: none;
            }

            .shipping-promo-bar .shipping-search-panel {
                display: none !important;
            }

            .shipping-promo-flag {
                width: 36px;
                height: 24px;
            }

            .navbar-area {
                padding-top: 0 !important;
                padding-bottom: 0 !important;
            }

            .main-responsive-nav {
                display: block !important;
                padding-top: 12px;
                padding-bottom: 12px;
            }

            .main-responsive-nav .container {
                display: block;
            }

            .main-navbar {
                display: none !important;
            }

            .main-responsive-menu {
                position: relative;
                min-height: 42px;
                display: grid;
                grid-template-columns: 42px minmax(0, 1fr);
                gap: 12px;
                align-items: center;
                width: 100%;
            }

            .main-responsive-logo {
                display: flex;
                justify-content: center;
                max-width: calc(100% - 128px);
            }

            .mean-container .mean-bar,
            .mean-container .mean-nav {
                display: none !important;
            }

            .main-responsive-nav {
                padding-top: 12px;
                padding-bottom: 12px;
            }

            .bm-menu-toggle {
                justify-self: start;
                position: static;
                transform: none;
                z-index: 9993;
            }

            .mobile-header-search-panel {
                display: block;
                grid-column: 2;
                justify-self: stretch;
                min-width: 0;
                width: 100%;
            }

            .mobile-header-search-panel .header-search-form {
                grid-template-columns: minmax(0, 1fr);
                margin: 0;
                max-width: none;
                padding: 0;
                width: 100%;
            }

            .mobile-header-search-panel .header-search-box {
                width: 100%;
            }

            .logo-grande {
                width: auto !important;
                max-width: 116px !important;
                max-height: 82px !important;
            }

            .header-navbar-actions {
                display: none;
            }

            .main-responsive-actions {
                display: none !important;
            }

            .header-search-form {
                grid-template-columns: 1fr;
                padding: 0;
            }

            .header-search-box .form-control {
                height: 42px;
                border-radius: 999px;
                font-size: 13px;
                padding-left: 16px;
                padding-right: 52px;
            }

            .header-search-box button {
                height: 36px;
                width: 36px;
            }

            .header-search-box button i {
                font-size: 21px;
            }
        }

        @media (min-width: 992px) {
            .main-responsive-actions {
                display: none;
            }

            .bm-menu-toggle,
            .bm-mobile-menu-overlay,
            .bm-mobile-menu-panel {
                display: none !important;
            }
        }

        @media (min-width: 992px) and (max-width: 1199px) {
            .navbar-brand {
                margin-right: 0;
            }

            .logo-navbar {
                width: auto;
                max-width: 112px;
                max-height: 76px;
            }

            .main-navbar .navbar .navbar-nav .nav-item a {
                font-size: 17px;
            }

            .header-navbar-actions {
                gap: 6px;
                margin-left: 6px;
            }

            .header-action-btn,
            .header-action-link {
                width: 38px;
                height: 38px;
            }
        }

        @media (max-width: 480px) {
            .bm-loader-logo {
                width: clamp(116px, 36vw, 150px);
                max-width: 48vw;
            }

            .shipping-promo-bar {
                grid-template-columns: 1fr;
                gap: 5px;
                min-height: 92px;
                padding: 8px 12px 9px;
                font-size: 10.5px;
                letter-spacing: 0;
            }

            .shipping-promo-message,
            .shipping-account-link {
                grid-column: auto;
                justify-self: center;
                max-width: 100%;
                white-space: normal;
                text-align: center;
            }

            .shipping-promo-message {
                column-gap: 7px;
                display: inline-flex;
                justify-content: center;
                font-size: 10.5px;
                max-width: 360px;
                width: 100%;
            }

            .shipping-promo-logo {
                width: clamp(140px, 48vw, 190px);
                max-height: 54px;
            }

            .shipping-promo-mobile-delivery {
                font-size: 12px;
            }

            .shipping-promo-emoji {
                font-size: 17px;
            }

            .shipping-promo-flag {
                width: 34px;
                height: 23px;
            }

            .shipping-promo-bar .header-search-panel {
                display: none !important;
                justify-self: center;
                max-width: 360px;
            }

            .header-search-box .form-control {
                height: 38px;
                font-size: 12px;
                padding-left: 14px;
                padding-right: 46px;
            }

            .header-search-box button {
                height: 32px;
                right: 3px;
                width: 32px;
            }

            .header-search-box button i {
                font-size: 19px;
            }

            .shipping-account-link {
                font-size: 12px;
            }

            .main-responsive-nav {
                padding-top: 8px;
                padding-bottom: 8px;
            }

            .main-responsive-nav .container {
                max-width: 100% !important;
                padding-left: 12px;
                padding-right: 12px;
                width: 100% !important;
            }

            .main-responsive-menu {
                display: grid;
                grid-template-columns: 34px minmax(0, 1fr);
                gap: 8px;
                align-items: center;
                min-height: 34px;
                width: 100%;
            }

            .bm-menu-toggle {
                left: auto;
                position: static;
                transform: none;
                width: 34px;
                height: 34px;
            }

            .logo-grande {
                width: auto !important;
                max-width: 84px !important;
                max-height: 60px !important;
            }

            .main-responsive-logo {
                max-width: none;
                min-width: 0;
                justify-content: center;
                overflow: hidden;
            }

            .main-responsive-actions {
                display: none !important;
            }

            .header-action-btn,
            .header-action-link {
                width: 30px;
                height: 30px;
            }

            .header-action-btn i,
            .header-action-link i {
                font-size: 20px;
            }
        }

        @media (max-width: 360px) {
            .shipping-promo-bar {
                gap: 6px;
                min-height: 86px;
                padding: 6px 8px;
                font-size: 9.5px;
                line-height: 1.25;
            }

            .shipping-promo-message {
                gap: 6px;
                font-size: 9.2px;
            }

            .shipping-promo-logo {
                width: clamp(124px, 50vw, 168px);
                max-height: 48px;
            }

            .shipping-promo-mobile-delivery {
                font-size: 11px;
            }

            .shipping-promo-emoji {
                font-size: 15px;
            }

            .shipping-promo-flag {
                width: 30px;
                height: 20px;
            }

            .header-search-box .form-control {
                height: 34px;
                font-size: 11px;
                padding-left: 12px;
                padding-right: 42px;
            }

            .header-search-box button {
                height: 28px;
                width: 28px;
            }

            .main-responsive-nav {
                padding-top: 4px;
                padding-bottom: 4px;
            }

            .main-responsive-nav .container {
                padding-left: 8px;
                padding-right: 8px;
            }

            .main-responsive-menu {
                grid-template-columns: 30px minmax(0, 1fr);
                gap: 6px;
                min-height: 30px;
                align-items: center;
            }

            .bm-menu-toggle {
                width: 30px;
                height: 30px;
            }

            .bm-menu-toggle i {
                font-size: 24px;
            }

            .logo-grande {
                width: auto !important;
                max-width: 66px !important;
                max-height: 48px !important;
            }

            .main-responsive-logo {
                justify-content: center;
                overflow: hidden;
            }

            .main-responsive-actions {
                display: none !important;
            }

            .header-action-btn,
            .header-action-link {
                width: 26px;
                height: 26px;
            }

            .header-action-btn i,
            .header-action-link i {
                font-size: 17px;
            }

            .bm-mobile-pickup-card {
                gap: 10px;
                padding: 15px 13px;
                margin-top: 18px;
            }

            .bm-mobile-pickup-icon {
                flex-basis: 44px;
                width: 44px;
                height: 44px;
            }

            .bm-mobile-pickup-icon img {
                width: 42px;
            }

            .bm-mobile-pickup-content strong {
                font-size: 15px;
            }

            .bm-mobile-pickup-content span {
                font-size: 9px;
            }
        }

        @media (max-width: 280px) {
            .shipping-promo-bar {
                min-height: 82px;
                padding: 5px 6px;
                font-size: 8.5px;
            }

            .shipping-promo-message {
                font-size: 8.4px;
            }

            .header-search-box .form-control {
                font-size: 10px;
            }

            .main-responsive-nav {
                padding-top: 3px;
                padding-bottom: 3px;
            }

            .main-responsive-menu {
                grid-template-columns: 28px minmax(0, 1fr);
                gap: 5px;
                min-height: 28px;
            }

            .logo-grande {
                width: auto !important;
                max-width: 56px !important;
                max-height: 40px !important;
            }

            .header-action-btn,
            .header-action-link {
                width: 24px;
                height: 24px;
            }

            .header-action-btn i,
            .header-action-link i {
                font-size: 17px;
            }
        }

        .bm-mobile-menu-overlay {
            position: fixed;
            inset: 0;
            background: rgba(10, 16, 30, 0.36);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.25s ease, visibility 0.25s ease;
            z-index: 99991;
        }

        .bm-mobile-menu-panel {
            position: fixed;
            top: 0;
            left: 0;
            width: min(86vw, 340px);
            height: 100vh;
            background: #ffffff;
            box-shadow: 18px 0 42px rgba(20, 27, 54, 0.18);
            transform: translateX(-105%);
            transition: transform 0.28s ease;
            z-index: 100000;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        @media (min-width: 768px) and (max-width: 991px) {
            .bm-mobile-menu-panel {
                width: min(58vw, 430px);
            }
        }

        @media (max-width: 480px) {
            .bm-mobile-menu-panel {
                width: min(88vw, 330px);
            }
        }

        body.bm-mobile-menu-open {
            overflow: hidden;
        }

        body.bm-mobile-menu-open .main-responsive-nav {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        body.bm-mobile-menu-open .shipping-promo-bar {
            opacity: 0;
            visibility: hidden;
        }

        body.bm-mobile-menu-open .bm-mobile-menu-overlay {
            opacity: 1;
            visibility: visible;
        }

        body.bm-mobile-menu-open .bm-mobile-menu-panel {
            transform: translateX(0);
        }

        .bm-mobile-menu-inner {
            padding: 18px 20px 26px;
        }
        .bm-mobile-menu-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #1D1D1B;
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        .bm-mobile-menu-close {
            border: 0;
            background: transparent;
            color: #1D1D1B;
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .bm-mobile-menu-close i {
            font-size: 28px;
        }

        .bm-mobile-search {
            position: relative;
            margin-bottom: 20px;
        }

        .bm-mobile-search i {
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            color: #1D1D1B;
            font-size: 20px;
        }

        .bm-mobile-search input {
            width: 100%;
            height: 44px;
            border: 0;
            border-bottom: 1px solid #777b88;
            padding: 0 0 0 34px;
            color: #1D1D1B;
            font-size: 15px;
            outline: none;
        }

        .bm-mobile-nav {
            border-top: 1px solid #eef0f5;
        }

        .bm-mobile-item {
            border-bottom: 1px solid #eef0f5;
        }

        .bm-mobile-row {
            min-height: 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            color: #11172f;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            text-decoration: none;
            width: 100%;
            border: 0;
            background: transparent;
            padding: 0;
        }

        .bm-mobile-row:hover,
        .bm-mobile-row:focus,
        .bm-mobile-row.is-active {
            color: #1D1D1B;
            outline: none;
        }

        .bm-mobile-row-label {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .bm-mobile-row i {
            color: #1D1D1B;
            font-size: 20px;
            flex: 0 0 auto;
            line-height: 1;
        }

        .bm-mobile-row.cart-icon-navbar {
            position: relative;
            justify-content: space-between;
        }

        .bm-mobile-row.cart-icon-navbar .cart-count-navbar {
            position: static;
            margin-left: auto;
        }

        .bm-mobile-pickup-card {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 18px;
            padding: 14px 14px;
            background: #1D1D1B;
            color: #ffffff;
            box-shadow: 0 10px 22px rgba(21, 21, 21, 0.14);
        }

        .bm-mobile-pickup-icon {
            flex: 0 0 58px;
            width: 58px;
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bm-mobile-pickup-icon img {
            width: 48px;
            height: auto;
            display: block;
        }

        .bm-mobile-pickup-content {
            flex: 1;
            min-width: 0;
            text-align: left;
        }

        .bm-mobile-pickup-content strong {
            display: block;
            color: #ffffff;
            font-family: "Manrope", Arial, sans-serif;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.15;
            text-transform: uppercase;
            margin: 0;
        }

        .bm-mobile-pickup-content span {
            display: block;
            margin-top: 5px;
            color: rgba(255, 255, 255, 0.95);
            font-family: "Manrope", Arial, sans-serif;
            font-size: 10px;
            font-weight: 600;
            line-height: 1.25;
        }

        .bm-mobile-account {
            margin: 20px 0 18px;
            color: #1D1D1B;
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-transform: uppercase;
            font-size: 14px;
            text-decoration: none;
        }

        .bm-mobile-account span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .bm-mobile-pickup {
            color: #1D1D1B;
            font-size: 15px;
        }

        .bm-mobile-pickup-card {
            gap: 10px;
            padding: 12px;
            margin-top: 16px;
        }

        .bm-mobile-pickup-icon {
            flex-basis: 48px;
            width: 48px;
            height: 48px;
        }

        .bm-mobile-pickup-icon img {
            width: 40px;
        }

        .bm-mobile-pickup-content strong {
            font-size: 12px;
            line-height: 1.1;
        }

        .bm-mobile-pickup-content span {
            font-size: 9px;
            line-height: 1.2;
        }

    </style>

    <!-- Start Navbar Area -->
    <div class="navbar-area">
        <div class="shipping-promo-bar" role="note">
            <span class="shipping-promo-message">
                <a class="shipping-promo-logo-link" href="index.php" aria-label="Industrial Collectors, S.A.">
                    <img class="shipping-promo-logo" src="<?php echo htmlspecialchars($bm_site_logo, ENT_QUOTES); ?>" alt="Industrial Collectors, S.A." loading="eager" decoding="async"
                        onerror="this.src='<?php echo htmlspecialchars($bm_site_logo_fallback, ENT_QUOTES); ?>'; this.onerror=null;">
                </a>
            </span>
            <span class="shipping-promo-mobile-delivery">Materiales met&aacute;licos</span>
            <div class="header-search-panel shipping-search-panel shipping-search-panel-desktop" id="header-search-panel">
                <form class="header-search-form" onsubmit="event.preventDefault(); buscarProductos(this);" role="search" aria-label="Buscar productos">
                    <div class="header-search-box">
                        <input type="text" id="search" name="search" class="form-control bm-search-input" placeholder="Buscar materiales metálicos..." value="<?php echo htmlspecialchars($bm_header_search_value, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off">
                        <button type="button" onclick="buscarProductos(this)" aria-label="Buscar">
                            <i class='bx bx-search'></i>
                        </button>
                    </div>
                </form>
            </div>
            <span class="shipping-promo-delivery">
                <span>Materiales met&aacute;licos</span>
                <span class="shipping-promo-delivery-icon" aria-hidden="true">🚚</span>
            </span>
        </div>

        <div class="main-responsive-nav">
            <div class="container">
                <div class="main-responsive-menu">
                    <button type="button" class="bm-menu-toggle" aria-label="Abrir men&uacute;" aria-expanded="false">
                        <i class='bx bx-menu'></i>
                    </button>
                    <div class="mobile-header-search-panel">
                        <form class="header-search-form" onsubmit="event.preventDefault(); buscarProductos(this);" role="search" aria-label="Buscar productos">
                            <div class="header-search-box">
                                <input type="text" id="search-mobile" name="search_mobile" class="form-control bm-search-input" placeholder="Buscar materiales metálicos..." value="<?php echo htmlspecialchars($bm_header_search_value, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off">
                                <button type="button" onclick="buscarProductos(this)" aria-label="Buscar">
                                    <i class='bx bx-search'></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="main-navbar">
            <div class="container">
                <nav class="navbar navbar-expand-md navbar-light">
                    <div class="collapse navbar-collapse mean-menu">
                        <ul class="navbar-nav bm-desktop-nav">
                            <?php foreach ($bmDesktopMenu as $bmMenuItem): ?>
                                <?php
                                $bmMenuPages = $bmMenuItem['pages'] ?? [strtok($bmMenuItem['url'], '?')];
                                $bmMenuIsActive = in_array($current_page, $bmMenuPages, true);
                                $bmMenuClasses = implode(' ', array_filter([
                                    'nav-link',
                                    $bmMenuItem['class'] ?? '',
                                    $bmMenuIsActive ? 'is-active' : '',
                                ]));
                                ?>
                                <li class="nav-item">
                                    <a href="<?php echo htmlspecialchars($bmMenuItem['url'], ENT_QUOTES, 'UTF-8'); ?>"
                                        class="<?php echo htmlspecialchars($bmMenuClasses, ENT_QUOTES, 'UTF-8'); ?>"
                                        <?php echo $bmMenuIsActive ? 'aria-current="page"' : ''; ?>>
                                        <?php if (!empty($bmMenuItem['icon'])): ?>
                                            <i class="<?php echo htmlspecialchars($bmMenuItem['icon'], ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i>
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars($bmMenuItem['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php if (($bmMenuItem['url'] ?? '') === 'cart.php'): ?>
                                            <span class="cart-count-navbar"></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>

        <div class="bm-mobile-menu-overlay" data-bm-menu-close></div>
        <aside class="bm-mobile-menu-panel" aria-label="Men&uacute; principal m&oacute;vil">
            <div class="bm-mobile-menu-inner">
                <div class="bm-mobile-menu-head">
                    <span>Men&uacute;</span>
                    <button type="button" class="bm-mobile-menu-close" aria-label="Cerrar men&uacute;" data-bm-menu-close>
                        <i class='bx bx-x'></i>
                    </button>
                </div>
            

                <nav class="bm-mobile-nav">
                    <?php foreach ($bmMobileMenu as $bmMenuItem): ?>
                        <?php
                        $bmMenuPages = $bmMenuItem['pages'] ?? [strtok($bmMenuItem['url'], '?')];
                        $bmMenuIsActive = in_array($current_page, $bmMenuPages, true);
                        $bmMobileClasses = implode(' ', array_filter([
                            'bm-mobile-row',
                            $bmMenuItem['class'] ?? '',
                            $bmMenuIsActive ? 'is-active' : '',
                        ]));
                        ?>
                        <div class="bm-mobile-item">
                            <a href="<?php echo htmlspecialchars($bmMenuItem['url'], ENT_QUOTES, 'UTF-8'); ?>"
                                class="<?php echo htmlspecialchars($bmMobileClasses, ENT_QUOTES, 'UTF-8'); ?>"
                                <?php echo $bmMenuIsActive ? 'aria-current="page"' : ''; ?>>
                                <span class="bm-mobile-row-label">
                                    <?php if (!empty($bmMenuItem['icon'])): ?>
                                        <i class="<?php echo htmlspecialchars($bmMenuItem['icon'], ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i>
                                    <?php endif; ?>
                                    <span><?php echo htmlspecialchars($bmMenuItem['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </span>
                                <?php if (($bmMenuItem['url'] ?? '') === 'cart.php'): ?>
                                    <span class="cart-count-navbar"></span>
                                <?php endif; ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                    
                </nav>

                <div class="bm-mobile-pickup-card">
                        <div class="bm-mobile-pickup-icon">
                            <img src="assets/img/CompraLABROCA.png" alt="Compra fácil en Industrial Collectors, S.A.">
                        </div>

                        <div class="bm-mobile-pickup-content">
                            <strong>Compra, consulta y recibe apoyo.</strong>
                        </div>
                </div>

            </div>
        </aside>

    </div>
    <!-- End Navbar Area -->
    <script>

        document.addEventListener('DOMContentLoaded', function () {
            var body = document.body;
            var toggle = document.querySelector('.bm-menu-toggle');
            var closeTargets = document.querySelectorAll('[data-bm-menu-close]');
            var headerSearchInitialValue = <?php echo json_encode($bm_header_search_value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;

            function syncHeaderSearchValue() {
                if (headerSearchInitialValue === '') {
                    return;
                }

                document.querySelectorAll('.bm-search-input, #search, #search-mobile').forEach(function (input) {
                    input.value = headerSearchInitialValue;
                    input.defaultValue = headerSearchInitialValue;
                    input.setAttribute('value', headerSearchInitialValue);
                });
            }

            if (toggle) {
                toggle.addEventListener('click', function () {
                    var isOpen = body.classList.toggle('bm-mobile-menu-open');
                    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
            }

            closeTargets.forEach(function (target) {
                target.addEventListener('click', function () {
                    body.classList.remove('bm-mobile-menu-open');
                    if (toggle) {
                        toggle.setAttribute('aria-expanded', 'false');
                    }
                });
            });

            syncHeaderSearchValue();
            window.setTimeout(syncHeaderSearchValue, 200);
            window.setTimeout(syncHeaderSearchValue, 900);
            window.addEventListener('load', syncHeaderSearchValue);
            window.addEventListener('pageshow', syncHeaderSearchValue);

            if (headerSearchInitialValue !== '') {
                var headerSearchSyncTimer = window.setInterval(syncHeaderSearchValue, 250);
                window.setTimeout(function () {
                    window.clearInterval(headerSearchSyncTimer);
                }, 2500);
            }
        });
    </script>
