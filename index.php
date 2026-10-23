<?php
// SEO para la página de inicio
$seo_title = "Industrial Collectors, S.A. | Materiales Metálicos";

$seo_description = "Perfilería, láminas, tubos, vigas, costaneras e hierro de construcción para ventas al por mayor y menor.";

$seo_canonical_url = "https://industrialcollectors.com/";

$seo_robots = isset($_GET['error'])
    ? "noindex, follow"
    : "index, follow, max-image-preview:large";

include 'head.php';
require_once __DIR__ . '/assets/php/category_visuals.php';
$current_page = basename($_SERVER['PHP_SELF']);

$apiUrl = "https://ssl.sol.sistemasolgt.com/induscollesitio/api/api_tienda_inicio.php";
$slider = null;
$sliderImageBaseUrl = "https://ssl.sol.sistemasolgt.com/brovic/files/articulos/";
$bmHeroFallbackImage = "assets/img/Sucursal/Fachada de Industrial Collectors.png";

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
            $slider = $data['data'][0];
        }
    }
}

$bmCleanHeroText = function ($value) {
    return trim(preg_replace('/\s+/', ' ', (string) $value));
};

$bmResolveHeroImage = function ($image) use ($sliderImageBaseUrl, $bmHeroFallbackImage) {
    $image = trim((string) $image);

    if ($image === '') {
        return $bmHeroFallbackImage;
    }

    if (preg_match('#^https?://#i', $image)) {
        return $image;
    }

    $image = ltrim(str_replace('\\', '/', $image), '/');
    $parts = array_map('rawurlencode', array_filter(explode('/', $image), 'strlen'));

    if (empty($parts)) {
        return $bmHeroFallbackImage;
    }

    return $sliderImageBaseUrl . implode('/', $parts);
};

$categoriasInicio = bm_build_category_visuals($categorias ?? []);

?>

<style>
    :root {
        --bm-blue: #1a1a1a;
        --bm-navy: #1D1D1B;
        --bm-hero-ratio: 1971 / 798;
    }

    .bm-home-hero {
        background: #ffffff;
        display: block;
        margin: 0;
        min-height: 0;
        overflow: hidden;
        padding: 0;
    }

    .bm-home-hero-carousel,
    .bm-home-hero-carousel .owl-stage-outer,
    .bm-home-hero-carousel .owl-stage,
    .bm-home-hero-carousel .owl-item {
        height: auto;
        margin: 0;
        max-height: none;
        min-height: 0;
        padding: 0;
    }

    .bm-home-hero-carousel {
        display: block;
        position: relative;
    }

    .navbar-area ~ .bm-home-hero {
        margin-top: 0;
    }

    .bm-hero-slide {
        aspect-ratio: var(--bm-hero-ratio);
        display: block;
        width: 100%;
        min-height: 0;
        position: relative;
        isolation: isolate;
        overflow: hidden;
        background: #ffffff;
    }

    .bm-hero-slide::before {
        display: none;
    }

    .bm-hero-media {
        aspect-ratio: var(--bm-hero-ratio);
        position: relative;
        width: 100%;
        height: auto;
        z-index: 0;
        line-height: 0;
        background: #ffffff;
    }

    .bm-hero-bg {
        aspect-ratio: var(--bm-hero-ratio);
        width: 100%;
        height: auto;
        display: block;
        object-fit: contain;
        object-position: center center;
    }

    .bm-hero-slide > .container {
        position: absolute;
        inset: 0;
        z-index: 2;
        display: flex;
        align-items: center;
        pointer-events: none;
    }

    .bm-hero-content {
        max-width: 540px;
        width: min(42%, 540px);
        padding: 0;
        position: relative;
        z-index: 2;
        pointer-events: auto;
    }

    .bm-hero-kicker {
        color: var(--bm-blue);
        display: inline-block;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0;
        line-height: 1.4;
        margin: 0 0 13px;
        max-width: 440px;
        text-transform: uppercase;
        text-shadow: 0 1px 10px rgba(255, 255, 255, 0.42);
    }

    .bm-hero-content h1 {
        color: var(--bm-navy);
        font-size: 42px;
        font-weight: 600;
        line-height: 1.12;
        margin: 0 0 16px;
        letter-spacing: 0;
        text-shadow: 0 1px 12px rgba(255, 255, 255, 0.5);
    }

    .bm-hero-content p {
        color: var(--bm-navy);
        font-size: 16px;
        line-height: 1.65;
        margin: 0 0 26px;
        max-width: 420px;
        text-shadow: 0 1px 10px rgba(255, 255, 255, 0.46);
    }

    .bm-home-btn {
        min-height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--bm-blue);
        color: #ffffff !important;
        padding: 13px 30px;
        border-radius: 0;
        font-size: 15px;
        font-weight: 700;
        letter-spacing: 0;
        text-transform: uppercase;
        transition: background .25s ease, transform .25s ease;
    }

    .bm-home-btn:hover {
        background: #1D1D1B;
        transform: translateY(-1px);
    }

    .bm-home-hero-carousel.owl-theme .owl-dots {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 16px;
        margin: 0;
    }

    .bm-home-hero-carousel.owl-theme .owl-dots .owl-dot span {
        width: 11px;
        height: 11px;
        margin: 4px;
        background: rgba(111, 142, 196, 0.35);
    }

    .bm-home-hero-carousel.owl-theme .owl-dots .owl-dot.active span {
        background: var(--bm-blue);
    }

    .bm-home-products {
        background: #ffffff;
        padding: 22px 0 28px;
    }

    .bm-home-section-title {
        color: var(--bm-navy);
        font-size: 28px;
        font-weight: 600;
        line-height: 1.2;
        margin: 0 0 18px;
        letter-spacing: 0;
    }

    .bm-home-products .home-carousel-footer {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        margin-top: 32px !important;
    }

    .bm-home-products-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 22px;
    }

    .bm-home-products-grid .carousel-product-item {
        width: 100%;
        height: 100%;
    }

    @media (max-width: 991px) {
        .bm-home-products-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }
    }

    @media (max-width: 767px) {
        .bm-home-products-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }
    }

    @media (max-width: 360px) {
        .bm-home-products-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }
    }

    .bm-home-featured-categories {
        background: #ffffff;
        padding: 34px 0 18px;
    }

    .bm-home-featured-tabs {
        align-items: stretch;
        border: 2px solid rgba(21, 21, 21, .88);
        border-radius: 8px;
        box-shadow: 0 16px 32px rgba(21, 21, 21, .12);
        display: inline-flex;
        justify-content: center;
        margin: 0 auto 28px;
        max-width: 680px;
        overflow: hidden;
        position: relative;
    }

    .bm-home-featured-tabs-wrap {
        display: flex;
        justify-content: center;
        width: 100%;
    }

    .bm-home-featured-tab {
        align-items: center;
        background: #ffffff;
        border: 0;
        border-right: 1px solid rgba(21, 21, 21, .18);
        color: var(--bm-navy);
        cursor: pointer;
        display: inline-flex;
        gap: 10px;
        justify-content: center;
        min-height: 58px;
        min-width: 205px;
        padding: 12px 24px;
        text-align: center;
        transition: background .22s ease, color .22s ease, transform .22s ease;
    }

    .bm-home-featured-tab:last-child {
        border-right: 0;
    }

    .bm-home-featured-tab i {
        color: #1D71B8;
        font-size: 28px;
        line-height: 1;
        transition: color .22s ease;
    }

    .bm-home-featured-tab span {
        font-size: 15px;
        font-weight: 800;
        letter-spacing: 0;
        line-height: 1.1;
        text-transform: uppercase;
    }

    .bm-home-featured-tab.is-active,
    .bm-home-featured-tab:hover,
    .bm-home-featured-tab:focus {
        background: var(--bm-navy);
        color: #FFFFFF;
        outline: 0;
    }

    .bm-home-featured-tab.is-active i,
    .bm-home-featured-tab:hover i,
    .bm-home-featured-tab:focus i {
        color: #1D71B8;
    }

    .bm-home-featured-tab:focus-visible {
        box-shadow: inset 0 0 0 3px rgba(29, 113, 184, .42);
    }

    .bm-home-featured-stage {
        background: #f4f5f8;
        border: 1px solid rgba(21, 21, 21, .06);
        border-radius: 8px;
        box-shadow: 0 18px 38px rgba(21, 21, 21, .08);
        padding: 28px;
    }

    .bm-home-featured-products {
        display: grid;
        gap: 22px;
        grid-auto-columns: calc((100% - 66px) / 4);
        grid-auto-flow: column;
        overflow-x: auto;
        padding: 0 0 12px;
        scroll-behavior: smooth;
        scroll-snap-type: x proximity;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .bm-home-featured-products::-webkit-scrollbar {
        display: none;
        height: 0;
        width: 0;
    }

    .bm-home-featured-products::-webkit-scrollbar-track {
        background: #d8dce5;
        border-radius: 999px;
    }

    .bm-home-featured-products::-webkit-scrollbar-thumb {
        background: var(--bm-navy);
        border-radius: 999px;
    }

    .bm-home-featured-products .carousel-product-item {
        min-width: 0;
        scroll-snap-align: start;
    }

    .bm-home-featured-products .alert {
        margin: 0;
        min-width: 260px;
    }

    .bm-home-featured-footer {
        align-items: center;
        display: flex;
        gap: 14px;
        justify-content: center;
        margin-top: 20px;
    }

    .bm-home-featured-more,
    .bm-home-featured-nav {
        align-items: center;
        border: 1px solid rgba(21, 21, 21, .12);
        border-radius: 8px;
        display: inline-flex;
        font-weight: 800;
        justify-content: center;
        min-height: 52px;
        text-decoration: none;
        text-transform: uppercase;
        transition: background .22s ease, color .22s ease, transform .22s ease, border-color .22s ease;
    }

    .bm-home-featured-more {
        background: var(--bm-navy);
        color: #ffffff;
        font-size: 13px;
        min-width: 128px;
        padding: 12px 24px;
    }

    .bm-home-featured-nav {
        background: var(--bm-navy);
        color: #ffffff;
        cursor: pointer;
        font-size: 28px;
        line-height: 1;
        padding: 0;
        width: 58px;
    }

    .bm-home-featured-more:hover,
    .bm-home-featured-more:focus,
    .bm-home-featured-nav:hover,
    .bm-home-featured-nav:focus {
        background: #1D71B8;
        border-color: #1D71B8;
        color: var(--bm-navy);
        outline: 0;
        transform: translateY(-2px);
    }

    @media (max-width: 991px) {
        .bm-home-featured-categories {
            padding: 26px 0 14px;
        }

        .bm-home-featured-tabs {
            margin-bottom: 20px;
            max-width: 100%;
        }

        .bm-home-featured-tab {
            min-height: 54px;
            min-width: 184px;
            padding: 10px 16px;
        }

        .bm-home-featured-tab i {
            font-size: 24px;
        }

        .bm-home-featured-tab span {
            font-size: 13px;
        }

        .bm-home-featured-stage {
            padding: 20px;
        }

        .bm-home-featured-products {
            gap: 16px;
            grid-auto-columns: calc((100% - 32px) / 3);
        }
    }

    @media (max-width: 767px) {
        .bm-home-featured-tabs-wrap {
            justify-content: flex-start;
            overflow-x: auto;
            padding-bottom: 8px;
            scrollbar-width: none;
        }

        .bm-home-featured-tabs-wrap::-webkit-scrollbar {
            display: none;
        }

        .bm-home-featured-tabs {
            flex: 0 0 auto;
            margin-bottom: 14px;
        }

        .bm-home-featured-tab {
            gap: 7px;
            min-height: 46px;
            min-width: 154px;
            padding: 8px 11px;
        }

        .bm-home-featured-tab i {
            font-size: 20px;
        }

        .bm-home-featured-tab span {
            font-size: 10.5px;
        }

        .bm-home-featured-stage {
            padding: 12px;
        }

        .bm-home-featured-products {
            gap: 10px;
            grid-auto-columns: calc((100% - 10px) / 2);
            padding-bottom: 10px;
        }

        .bm-home-featured-footer {
            gap: 10px;
            margin-top: 14px;
        }

        .bm-home-featured-more,
        .bm-home-featured-nav {
            min-height: 42px;
        }

        .bm-home-featured-more {
            font-size: 11px;
            min-width: 112px;
            padding: 10px 18px;
        }

        .bm-home-featured-nav {
            font-size: 24px;
            width: 46px;
        }
    }

    .bm-home-products .single-arrivals-products {
        border: 1px solid rgba(111, 142, 196, 0.18) !important;
        border-radius: 6px !important;
        box-shadow: 0 10px 24px rgba(31, 36, 64, 0.08) !important;
        background: #ffffff !important;
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 510px !important;
        padding: 16px !important;
    }

    .bm-home-products .arrivals-products-image {
        background: #ffffff;
        height: 220px !important;
        min-height: 220px !important;
    }

    .bm-home-products .arrivals-products-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .bm-home-products .arrivals-action,
    .bm-home-products .arrivals-products-image .tag {
        display: none !important;
    }

    .bm-home-products .arrivals-products-content {
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        padding: 16px 0 0 !important;
        text-align: left !important;
    }

    .bm-home-products .arrivals-products-content h3,
    .bm-home-products .arrivals-products-content h3 a {
        color: #1D1D1B !important;
        display: -webkit-box !important;
        font-size: 17px !important;
        font-weight: 400 !important;
        line-height: 1.28 !important;
        margin: 0 0 16px !important;
        min-height: 44px !important;
        max-height: 44px !important;
        overflow: hidden !important;
        text-transform: none !important;
        white-space: normal !important;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2 !important;
        letter-spacing: 0;
    }

    .bm-home-products .rating {
        margin: 0 0 18px !important;
    }

    .bm-home-products .rating i {
        color: var(--bm-blue) !important;
        font-size: 20px !important;
    }

    .bm-home-products .product-card-price-normal {
        color: #111111 !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        line-height: 1.3 !important;
        margin: 0 !important;
        white-space: nowrap;
    }

    .bm-home-products .product-card-price-normal::after {
        content: none !important;
        display: none !important;
    }

    .bm-home-products .product-card-actions {
        margin-top: auto !important;
        padding-top: 24px !important;
    }

    .bm-home-products .product-card-actions .product-card-action-cart {
        width: 100%;
        min-height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0;
        background: #1D71B8 !important;
        border-color: #1D71B8 !important;
        color: #ffffff !important;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0;
        min-height: 48px;
        text-transform: uppercase;
    }

    .bm-home-products .product-card-actions .product-card-action-details {
        display: none !important;
    }

    .bm-home-promo {
        min-height: 430px;
        display: flex;
        align-items: center;
        background-color: #ffffff;
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center center;
        position: relative;
        isolation: isolate;
    }

    .bm-home-promo-content {
        padding: 46px 0;
    }

    .bm-home-promo-number {
        color: var(--bm-blue);
        display: block;
        font-size: 82px;
        font-weight: 700;
        line-height: 0.9;
        margin-bottom: 12px;
    }

    .bm-home-promo h2 {
        color: #1D1D1B;
        font-size: 25px;
        font-weight: 700;
        line-height: 1.05;
        margin: 0 0 18px;
        text-transform: uppercase;
    }

    .bm-home-promo h2 span {
        color: var(--bm-blue);
        display: block;
        text-transform: none;
    }

    .bm-home-masglo {
        background: #ffffff;
        padding: 18px 0 22px;
    }

    .bm-home-masglo-card {
        min-height: 430px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #ffffff;
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center center;
        padding: 42px 28px;
        position: relative;
        overflow: hidden;
        isolation: isolate;
    }

    .bm-home-masglo-content {
        max-width: 470px;
        text-align: center;
    }

    .bm-home-masglo-content h2 {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 128px;
        color: var(--bm-blue);
        font-size: 54px;
        font-weight: 700;
        line-height: 1.05;
        margin: 0;
    }

    .bm-home-masglo-content p {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 94px;
        color: var(--bm-blue);
        font-size: 24px;
        line-height: 1.25;
        margin: 0;
    }

    .bm-home-final-offer {
        background: #ffffff;
        padding: 18px 0 42px;
    }

    .bm-home-final-card {
        min-height: 300px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        align-items: center;
        background-color: #ffffff;
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center center;
        color: #ffffff;
        border-radius: 8px;
        padding: 34px 42px;
        overflow: hidden;
        position: relative;
        isolation: isolate;
    }

    .bm-home-final-card>div {
        position: relative;
        z-index: 1;
    }

    .bm-home-final-card h2 {
        color: #ffffff;
        font-size: 40px;
        font-weight: 700;
        line-height: 0.95;
        margin: 0 0 8px;
        text-transform: uppercase;
    }

    .bm-home-final-card p {
        color: #ffffff;
        font-size: 14px;
        line-height: 1.45;
        margin: 0;
    }

    .bm-home-final-discount {
        border-left: 1px solid rgba(255, 255, 255, 0.55);
        padding-left: 32px;
    }

    .bm-home-final-discount strong {
        display: block;
        color: #ffffff;
        font-size: 44px;
        font-weight: 700;
        line-height: 1;
        text-transform: uppercase;
    }

    .bm-home-final-discount .bm-home-btn {
        background: #ffffff;
        color: var(--bm-blue) !important;
        min-height: 38px;
        margin-top: 14px;
        padding: 9px 18px;
        font-size: 12px;
    }

    @media (max-width: 767px) {

        .bm-home-hero-carousel,
        .bm-home-hero-carousel .owl-stage-outer,
        .bm-home-hero-carousel .owl-stage,
        .bm-home-hero-carousel .owl-item {
            height: auto;
            min-height: 0;
        }

        .bm-hero-slide {
            display: block;
            min-height: 0;
        }

        .bm-hero-media {
            position: relative;
            width: 100%;
            height: auto;
        }

        .bm-hero-bg {
            width: 100%;
            height: auto;
            object-fit: contain;
            object-position: center center;
        }

        .bm-hero-content {
            max-width: 146px;
            width: 35%;
            padding: 0;
            position: relative;
            z-index: 2;
            transform: translate(13px, -3px);
        }

        .bm-hero-kicker {
            font-size: 6.4px;
            line-height: 1.12;
            max-width: 126px;
            margin-bottom: 2px;
        }

        .bm-hero-content h1 {
            font-size: 12.5px;
            line-height: 1.04;
            max-width: 132px;
            margin-bottom: 3px;
        }

        .bm-hero-content p {
            font-size: 6.4px;
            line-height: 1.14;
            max-width: 130px;
            margin-bottom: 4px;
        }

        .bm-home-btn {
            min-height: 18px;
            padding: 3px 7px;
            font-size: 6.4px;
            line-height: 1;
            position: relative;
            top: -2px;
        }

        .bm-home-section-title {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .bm-home-products {
            padding: 14px 0 28px;
        }

        .bm-home-products .container,
        .bm-home-products .row,
        .bm-home-products [class*="col-"] {
            max-width: 100% !important;
            min-width: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            width: 100% !important;
        }

        .bm-home-products .container {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .bm-home-products .row {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .bm-home-products-grid {
            display: grid !important;
            gap: 10px !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            max-width: 100% !important;
            width: 100% !important;
        }

        .bm-home-products .carousel-product-item {
            max-width: 100% !important;
            min-width: 0 !important;
            width: 100%;
        }

        .bm-home-products .single-arrivals-products {
            min-height: 286px !important;
            padding: 8px !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        .bm-home-products .arrivals-products-image {
            height: clamp(92px, 30vw, 128px) !important;
            min-height: clamp(92px, 30vw, 128px) !important;
        }

        .bm-home-products .arrivals-products-content h3,
        .bm-home-products .arrivals-products-content h3 a {
            font-size: 9.5px !important;
            line-height: 1.18 !important;
            min-height: 36px !important;
            max-height: 36px !important;
        }

        .bm-home-products .product-card-actions .product-card-action-cart {
            min-height: 29px !important;
            font-size: 7.2px !important;
            padding: 7px 4px !important;
        }

        .bm-home-products .product-card-price-normal {
            font-size: 7.8px !important;
            line-height: 1.18 !important;
            white-space: normal !important;
        }

        .bm-home-promo {
            min-height: 300px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center center;
        }

        .bm-home-promo-number {
            font-size: 82px;
        }

        .bm-home-promo h2 {
            font-size: 24px;
        }

        .bm-home-masglo-card {
            min-height: 300px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center center;
            padding: 24px 20px;
        }

        .bm-home-masglo-content h2 {
            font-size: 34px;
        }

        .bm-home-masglo-content p {
            font-size: 17px;
        }

        .bm-home-final-card {
            min-height: 260px;
            grid-template-columns: 1fr;
            gap: 18px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center center;
            padding: 24px 26px;
        }

        .bm-home-final-card h2 {
            font-size: 32px;
        }

        .bm-home-final-discount {
            border-left: 0;
            border-top: 1px solid rgba(255, 255, 255, 0.55);
            padding-left: 0;
            padding-top: 18px;
        }

        .bm-home-final-discount strong {
            font-size: 36px;
        }
    }

    @media (max-width: 280px) {
        .bm-home-products .single-arrivals-products {
            min-height: 238px !important;
            padding: 6px !important;
        }

        .bm-home-products .arrivals-products-image {
            height: 70px !important;
            min-height: 70px !important;
        }

        .bm-home-products .arrivals-products-content h3,
        .bm-home-products .arrivals-products-content h3 a {
            font-size: 9px !important;
            min-height: 22px !important;
            max-height: 22px !important;
        }

        .bm-home-products .product-card-price-normal {
            font-size: 8px !important;
        }

    }

    /* ===========================
    BANNER FINAL - OFERTAS SEMANA
    =========================== */

    .bm-home-final-full {
        width: 100%;
        background: #ffffff;
        padding: 24px 24px 36px;
        overflow: hidden;
    }

    .bm-home-final-image-link {
        position: relative;
        display: block;
        width: 100%;
        max-width: 1120px;
        margin: 0 auto;
        text-decoration: none;
        overflow: hidden;
        border-radius: 18px;
    }

    .bm-home-final-img {
        width: 100%;
        height: auto;
        display: block;
        object-fit: contain;
        object-position: center center;
    }

    /* Texto encima del banner final, limitado al área izquierda */
    .bm-home-final-overlay-text {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        z-index: 2;
        width: 61%;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 20px;
        padding: 0 18px 0 36px;
        color: #ffffff;
        pointer-events: none;
        box-sizing: border-box;
    }

    /* Lado izquierdo */
    .bm-home-final-left {
        flex: 0 1 48%;
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .bm-home-final-icon {
        width: 66px;
        height: 66px;
        flex: 0 0 66px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        color: var(--bm-blue);
        border-radius: 50%;
        font-size: 34px;
    }

    .bm-home-final-left h2 {
        color: #ffffff;
        font-size: 31px;
        font-weight: 800;
        line-height: 0.96;
        margin: 0 0 8px;
        text-transform: uppercase;
    }

    .bm-home-final-left h2 span {
        display: block;
    }

    .bm-home-final-left p {
        color: #ffffff;
        font-size: 13px;
        line-height: 1.25;
        margin: 0;
        max-width: 220px;
    }

    /* Línea central */
    .bm-home-final-line {
        width: 2px;
        height: 118px;
        flex: 0 0 2px;
        display: block;
        background: rgba(255, 255, 255, 0.85);
    }

    /* Lado derecho, reducido para no tapar productos */
    .bm-home-final-right {
        flex: 0 1 36%;
        min-width: 0;
    }

    .bm-home-final-right span {
        display: block;
        color: #ffffff;
        font-size: 17px;
        font-weight: 800;
        line-height: 1;
        text-transform: uppercase;
    }

    .bm-home-final-right strong {
        display: block;
        color: #ffffff;
        font-size: 42px;
        font-weight: 800;
        line-height: 0.95;
        margin: 3px 0 6px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .bm-home-final-right p {
        color: #ffffff;
        font-size: 12px;
        line-height: 1.25;
        margin: 0 0 9px;
        white-space: nowrap;
    }

    .bm-home-final-right em {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        background: #ffffff;
        color: var(--bm-blue);
        padding: 7px 18px;
        border-radius: 6px;
        font-size: 11px;
        font-style: normal;
        font-weight: 800;
        text-transform: uppercase;
        white-space: nowrap;
    }

    /* ===========================
    RESPONSIVE TABLET
    =========================== */

    @media (max-width: 991px) {
        .bm-home-final-full {
            padding: 20px 18px 30px;
        }

        .bm-home-final-image-link {
            max-width: 940px;
            border-radius: 16px;
        }

        .bm-home-final-overlay-text {
            width: 62%;
            gap: 14px;
            padding: 0 14px 0 24px;
        }

        .bm-home-final-left {
            gap: 12px;
        }

        .bm-home-final-icon {
            width: 48px;
            height: 48px;
            flex-basis: 48px;
            font-size: 26px;
        }

        .bm-home-final-left h2 {
            font-size: 22px;
            margin-bottom: 5px;
        }

        .bm-home-final-left p {
            font-size: 10px;
            max-width: 160px;
        }

        .bm-home-final-line {
            height: 86px;
        }

        .bm-home-final-right span {
            font-size: 13px;
        }

        .bm-home-final-right strong {
            font-size: 32px;
            margin: 2px 0 4px;
        }

        .bm-home-final-right p {
            font-size: 10px;
            margin-bottom: 6px;
        }

        .bm-home-final-right em {
            min-height: 28px;
            padding: 6px 13px;
            font-size: 9px;
        }
    }

    /* ===========================
    RESPONSIVE MÓVIL
    =========================== */

    @media (max-width: 767px) {
        .bm-home-final-full {
            padding: 18px 12px 26px;
        }

        .bm-home-final-image-link {
            max-width: 100%;
            border-radius: 12px;
        }

        .bm-home-final-overlay-text {
            width: 62%;
            gap: 7px;
            padding: 0 8px 0 12px;
        }

        .bm-home-final-icon {
            display: none;
        }

        .bm-home-final-left {
            flex-basis: 43%;
            gap: 0;
        }

        .bm-home-final-left h2 {
            font-size: 14px;
            line-height: 0.96;
            margin-bottom: 3px;
        }

        .bm-home-final-left p {
            font-size: 7px;
            line-height: 1.15;
            max-width: 90px;
        }

        .bm-home-final-line {
            width: 1px;
            height: 52px;
            flex-basis: 1px;
        }

        .bm-home-final-right {
            flex-basis: 38%;
        }

        .bm-home-final-right span {
            font-size: 8px;
        }

        .bm-home-final-right strong {
            font-size: 17px;
            margin: 1px 0 2px;
        }

        .bm-home-final-right p {
            font-size: 7px;
            margin-bottom: 4px;
        }

        .bm-home-final-right em {
            min-height: 20px;
            padding: 4px 7px;
            font-size: 6px;
            border-radius: 4px;
        }
    }
</style>

<!-- Mensaje de error si viene de redirección -->
<?php if (isset($_GET['error'])): ?>
    <div class="container" style="margin-top: 20px;">
        <div class="alert alert-danger" role="alert" style="padding: 15px; margin-bottom: 20px; border-radius: 5px;">
            <?php
            $error = $_GET['error'];
            switch ($error) {
                case 'producto_no_encontrado':
                    echo '<strong>⚠️ Producto no encontrado:</strong> El producto que buscas no existe o ha sido eliminado.';
                    break;
                case 'id_invalido':
                    echo '<strong>⚠️ ID inválido:</strong> El ID del producto no es válido. Por favor, selecciona un producto desde la tienda.';
                    break;
                case 'id_faltante':
                    echo '<strong>⚠️ ID faltante:</strong> No se especificó un ID de producto. Por favor, selecciona un producto desde la tienda.';
                    break;
                default:
                    echo '<strong>⚠️ Error:</strong> Ha ocurrido un error al cargar el producto.';
            }
            ?>
        </div>
    </div>
<?php endif; ?>


<!-- Start Main Slider Area -->
<?php
$sliderItems = [];
$bmHeroCtas = [
    1 => [
        'label' => 'Ver productos',
        'url' => 'tienda.php',
    ],
    2 => [
        'label' => 'Ver ofertas',
        'url' => 'ofertas.php',
    ],
    3 => [
        'label' => 'Contáctanos',
        'url' => 'contact.php',
    ],
];

if ($slider) {
    for ($i = 1; $i <= 3; $i++) {
        if (!empty($slider["bloque_$i"])) {
            $sliderItems[] = [
                'index' => $i,
                'data' => $slider["bloque_$i"]
            ];
        }
    }
}
?>

<section class="bm-home-hero">
    <div class="bm-home-hero-carousel owl-carousel owl-theme">
        <?php if (!empty($sliderItems)): ?>
            <?php foreach ($sliderItems as $slideIndex => $item): ?>
                <?php
                $i = $item['index'];
                $b = $item['data'];
                $tituloApi = $bmCleanHeroText($b['titulo'] ?? '');
                $subtituloApi = $bmCleanHeroText($b['sub_titulo'] ?? '');
                $descripcionHero = $bmCleanHeroText($b['descripcion'] ?? '');
                $tituloHero = $subtituloApi !== '' ? $subtituloApi : $tituloApi;
                $kickerHero = $tituloApi !== $tituloHero ? $tituloApi : '';
                $imagenUrl = $bmResolveHeroImage($b['imagen'] ?? '');
                $altHero = $tituloHero !== '' ? $tituloHero : ($kickerHero !== '' ? $kickerHero : 'Industrial Collectors, S.A.');
                $cta = $bmHeroCtas[$i] ?? $bmHeroCtas[1];
                $ctaTexto = $cta['label'];
                $ctaUrl = $cta['url'];
                ?>
                <article class="bm-hero-slide">
                    <div class="bm-hero-media">
                        <img
                            class="bm-hero-bg"
                            src="<?php echo htmlspecialchars($imagenUrl, ENT_QUOTES); ?>"
                            alt="<?php echo htmlspecialchars($altHero, ENT_QUOTES); ?>"
                            loading="<?php echo $slideIndex === 0 ? 'eager' : 'lazy'; ?>"
                            decoding="async"
                            onerror="this.onerror=null;this.src='<?php echo htmlspecialchars($bmHeroFallbackImage, ENT_QUOTES); ?>';">
                    </div>
                    <div class="container">
                        <div class="bm-hero-content">
                            <?php if ($kickerHero !== ''): ?>
                                <span class="bm-hero-kicker"><?php echo htmlspecialchars($kickerHero); ?></span>
                            <?php endif; ?>
                            <?php if ($tituloHero !== ''): ?>
                                <h1><?php echo htmlspecialchars($tituloHero); ?></h1>
                            <?php endif; ?>
                            <?php if ($descripcionHero !== ''): ?>
                                <p><?php echo htmlspecialchars($descripcionHero); ?></p>
                            <?php endif; ?>
                            <a href="<?php echo htmlspecialchars($ctaUrl); ?>" class="bm-home-btn"><?php echo htmlspecialchars($ctaTexto); ?></a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <article class="bm-hero-slide">
                <div class="bm-hero-media">
                    <img class="bm-hero-bg" src="<?php echo htmlspecialchars($bmHeroFallbackImage, ENT_QUOTES); ?>" alt="Industrial Collectors, S.A.">
                </div>
                <div class="container">
                    <div class="bm-hero-content">
                        <span class="bm-hero-kicker">Industrial Collectors, S.A.</span>
                        <h1>Materiales que construyen grandes proyectos</h1>
                        <p>Perfilería, láminas, tubos, vigas, costaneras y hierro de construcción para proyectos de toda escala.</p>
                        <a href="tienda.php" class="bm-home-btn">Comprar ahora</a>
                    </div>
                </div>
            </article>
        <?php endif; ?>
    </div>
    <aside class="bm-hero-benefits" aria-label="Lo que encontrarás en Industrial Collectors">
        <div class="container">
            <ul class="bm-hero-benefits-list">
                <li>
                    <span class="bm-hero-benefit-icon" aria-hidden="true"><i class="bx bx-layer"></i></span>
                    <div class="bm-hero-benefit-copy">
                        <strong class="bm-hero-benefit-title">Materiales metálicos</strong>
                        <span class="bm-hero-benefit-text">Perfiles, tubos y láminas</span>
                    </div>
                </li>
                <li>
                    <span class="bm-hero-benefit-icon" aria-hidden="true"><i class="bx bx-store-alt"></i></span>
                    <div class="bm-hero-benefit-copy">
                        <strong class="bm-hero-benefit-title">Mayor y menor</strong>
                        <span class="bm-hero-benefit-text">Para obras de toda escala</span>
                    </div>
                </li>
                <li>
                    <span class="bm-hero-benefit-icon" aria-hidden="true"><i class="bx bx-support"></i></span>
                    <div class="bm-hero-benefit-copy">
                        <strong class="bm-hero-benefit-title">Asesoría</strong>
                        <span class="bm-hero-benefit-text">Atención personalizada</span>
                    </div>
                </li>
            </ul>
        </div>
    </aside>
</section>
<!-- End Main Slider Area -->


<!-- End Overview Area -->
<style>
    @media (max-width: 767px) {
        .products-after-banner {
            padding-top: 0 !important;
        }
    }
</style>
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

    /* Estilo Tarjeta Producto */
    .single-arrivals-products {
        border: 1px solid rgba(255, 255, 255, 0.95);
        /* Contorno suave tipo tarjeta */
        border-radius: 15px;
        /* Bordes un poco más redondeados */
        padding: 15px;
        background: #fff;
        box-shadow: 0 12px 32px rgba(33, 45, 95, 0.10), 0 0 0 1px rgba(255, 255, 255, 0.85);
        transition: all 0.3s ease;
        position: relative;
    }

    .single-arrivals-products:hover {
        box-shadow: 0 18px 38px rgba(33, 45, 95, 0.16), 0 0 0 1px rgba(255, 255, 255, 0.95);
        transform: translateY(-5px);
        border-color: rgba(255, 255, 255, 1);
        /* Mantener contorno limpio al hover */
    }

    /* Estilo Tag - Rediseño tipo Ovalado Azul */
    .single-arrivals-products .arrivals-products-image .tag {
        border-radius: 50px !important;
        padding: 5px 15px !important;
        width: auto !important;
        height: auto !important;
        min-width: unset !important;
        line-height: normal !important;
        font-size: 12px !important;
        font-weight: 500 !important;
        top: 10px !important;
        right: 10px !important;
        background: #1D71B8 !important;
        color: #1D1D1B !important;
        box-shadow: 0 4px 12px rgba(29, 113, 184, 0.32);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Hover card mantiene el acento industrial de la marca */
    .single-arrivals-products:hover .arrivals-products-image .tag {
        background: #1D71B8 !important;
    }

    /* Ajuste imagen para que no se salga del borde redondeado */
    .single-arrivals-products .arrivals-products-image {
        border-radius: 8px;
        overflow: hidden;
    }

    .home-carousel-footer {
        position: relative;
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        gap: 16px;
        margin-top: 24px;
    }

    .home-carousel-more {
        justify-self: end;
        background: #1D1D1B;
        color: #FFFFFF;
        font-size: 12px;
        font-weight: 700;
        line-height: 1;
        min-height: 38px;
        padding: 9px 24px;
        border: 1px solid rgba(29, 113, 184, 0.58);
        border-radius: 50px;
        text-transform: uppercase;
        box-shadow: 0 10px 20px rgba(7, 17, 41, .22);
        transition: background .25s ease, border-color .25s ease, color .25s ease, transform .25s ease, box-shadow .25s ease;
    }

    .home-carousel-more:hover,
    .home-carousel-more:focus {
        background: #1D71B8;
        border-color: #1D71B8;
        color: #1D1D1B;
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(29, 113, 184, .26);
    }

    .home-carousel-more:focus-visible {
        outline: 3px solid rgba(29, 113, 184, .35);
        outline-offset: 3px;
    }

    @media (max-width: 991px) {
        .home-carousel-footer {
            grid-template-columns: 1fr;
            justify-items: center;
            margin-top: 18px;
        }

        .bm-home-products .home-carousel-more {
            justify-self: center !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }
    }

    @media (min-width: 768px) and (max-width: 991px) {

        .bm-home-hero-carousel,
        .bm-home-hero-carousel .owl-stage-outer,
        .bm-home-hero-carousel .owl-stage,
        .bm-home-hero-carousel .owl-item {
            height: auto;
            min-height: 0;
        }

        .bm-hero-slide {
            display: block;
            min-height: 0;
        }

        .bm-hero-bg {
            object-position: center center;
        }

        .bm-hero-content {
            max-width: 270px;
            width: 35%;
            padding: 0;
            transform: translate(14px, -3px);
        }

        .bm-hero-kicker {
            font-size: 8px;
            line-height: 1.15;
            max-width: 190px;
            margin-bottom: 4px;
        }

        .bm-hero-content h1 {
            font-size: 20px;
            line-height: 1.05;
            max-width: 212px;
            margin-bottom: 6px;
        }

        .bm-hero-content p {
            font-size: 10px;
            line-height: 1.18;
            max-width: 205px;
            margin-bottom: 8px;
        }

        .bm-home-btn {
            min-height: 24px;
            padding: 6px 12px;
            font-size: 8px;
            line-height: 1;
            position: relative;
            top: -3px;
        }
    }
</style>

<style>
    @media (max-width: 360px) {

        /* Slider principal mobile */
        .bm-home-hero-carousel,
        .bm-home-hero-carousel .owl-stage-outer,
        .bm-home-hero-carousel .owl-stage,
        .bm-home-hero-carousel .owl-item,
        .bm-hero-slide {
            height: auto !important;
            min-height: 0 !important;
        }

        .bm-hero-media {
            position: relative !important;
            width: 100% !important;
            height: auto !important;
        }

        .bm-hero-bg {
            width: 100% !important;
            height: auto !important;
            object-fit: contain !important;
            object-position: center center !important;
        }

        .bm-hero-content {
            max-width: 126px !important;
            width: 34% !important;
            padding: 0 !important;
            position: relative !important;
            z-index: 2 !important;
            transform: translate(10px, -3px) !important;
        }

        .bm-hero-kicker {
            font-size: 6px !important;
            line-height: 1.12 !important;
            max-width: 106px !important;
            margin-bottom: 2px !important;
        }

        .bm-hero-content h1 {
            font-size: 11.5px !important;
            line-height: 1.06 !important;
            max-width: 112px !important;
            margin-bottom: 3px !important;
        }

        .bm-hero-content p {
            font-size: 6px !important;
            line-height: 1.16 !important;
            max-width: 110px !important;
            margin-bottom: 4px !important;
        }

        .bm-home-btn {
            font-size: 6px !important;
            min-height: 17px !important;
            padding: 3px 6px !important;
            line-height: 1 !important;
            position: relative !important;
            top: -2px !important;
        }

        .bm-home-hero-carousel.owl-theme .owl-dots {
            bottom: 5px !important;
            line-height: 1 !important;
        }

        .bm-home-hero-carousel.owl-theme .owl-dots .owl-dot span {
            width: 6px !important;
            height: 6px !important;
            margin: 2px !important;
        }

        /* Título productos */
        .bm-home-section-title {
            font-size: 18px !important;
            line-height: 1.15 !important;
            margin-bottom: 8px !important;
        }

        /* Productos más vendidos */
        .bm-home-products {
            padding: 10px 0 22px !important;
        }

        .bm-home-products .single-arrivals-products {
            min-height: 258px !important;
            padding: 7px !important;
        }

        .bm-home-products .arrivals-products-image {
            height: 82px !important;
            min-height: 82px !important;
        }

        .bm-home-products .arrivals-products-content {
            padding-top: 8px !important;
        }

        .bm-home-products .arrivals-products-content h3,
        .bm-home-products .arrivals-products-content h3 a {
            font-size: 10px !important;
            line-height: 1.18 !important;
            margin-bottom: 6px !important;
            min-height: 24px !important;
            max-height: 24px !important;
        }

        .bm-home-products .rating {
            gap: 2px !important;
            margin-bottom: 6px !important;
            min-height: 12px !important;
        }

        .bm-home-products .rating i {
            font-size: 12px !important;
        }

        .bm-home-products .product-card-price-normal {
            font-size: 9px !important;
            line-height: 1.2 !important;
            white-space: normal !important;
        }

        .bm-home-products .product-card-actions {
            padding-top: 8px !important;
        }

        .bm-home-products .product-card-action-cart {
            font-size: 7.5px !important;
            min-height: 28px !important;
            padding: 7px 4px !important;
        }

        .bm-home-promo {
            min-height: 240px !important;
            background-size: contain !important;
            background-repeat: no-repeat !important;
            background-position: center center !important;
        }

        .bm-home-promo-content {
            max-width: 150px;
            padding: 26px 0 !important;
        }

        .bm-home-promo-number {
            font-size: 52px !important;
            margin-bottom: 6px !important;
        }

        .bm-home-promo h2 {
            font-size: 16px !important;
            margin-bottom: 10px !important;
        }

        .bm-home-masglo-card {
            min-height: 240px !important;
            background-size: contain !important;
            background-repeat: no-repeat !important;
            background-position: center center !important;
            padding: 18px 14px !important;
        }

        .bm-home-masglo-content h2 {
            font-size: 22px !important;
        }

        .bm-home-masglo-content p {
            font-size: 12px !important;
            margin-bottom: 10px !important;
        }

        /* Oferta final */
        .bm-home-final-card {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) !important;
            gap: 10px !important;
            min-height: 220px !important;
            background-size: contain !important;
            background-repeat: no-repeat !important;
            background-position: center center !important;
            padding: 16px 14px !important;
        }

        .bm-home-final-card h2 {
            font-size: 18px !important;
            line-height: 1 !important;
        }

        .bm-home-final-card p {
            font-size: 10px !important;
            line-height: 1.25 !important;
        }

        .bm-home-final-discount {
            border-left: 1px solid rgba(255, 255, 255, 0.55) !important;
            border-top: 0 !important;
            padding-left: 10px !important;
            padding-top: 0 !important;
        }

        .bm-home-final-discount strong {
            font-size: 20px !important;
            line-height: 1.05 !important;
        }

        .bm-home-final-discount .bm-home-btn {
            font-size: 8px !important;
            min-height: 28px !important;
            margin-top: 8px !important;
            padding: 7px 9px !important;
        }
    }

    @media (max-width: 280px) {
        .bm-home-products .single-arrivals-products {
            min-height: 238px !important;
            padding: 6px !important;
        }

        .bm-home-products .arrivals-products-image {
            height: 70px !important;
            min-height: 70px !important;
        }

        .bm-home-products .arrivals-products-content h3,
        .bm-home-products .arrivals-products-content h3 a {
            font-size: 9px !important;
            min-height: 22px !important;
            max-height: 22px !important;
        }

        .bm-home-products .product-card-price-normal {
            font-size: 8px !important;
        }

    }
</style>

<?php
$bmFindHomeCategoryId = function (array $categoryNames) use ($categorias) {
    $needles = array_values(array_filter(array_map('bm_normalize_visual_key', $categoryNames)));

    foreach ((array) $categorias as $category) {
        if (isset($category['condicion']) && (string) $category['condicion'] !== '1') {
            continue;
        }

        $categoryKey = bm_normalize_visual_key($category['nombre'] ?? '');
        if ($categoryKey === '') {
            continue;
        }

        foreach ($needles as $needle) {
            if ($categoryKey === $needle || strpos($categoryKey, $needle) !== false || strpos($needle, $categoryKey) !== false) {
                return (string) ($category['idcategoria'] ?? '');
            }
        }
    }

    return '';
};

$bmHomeFeaturedCategories = [
    [
        'label' => 'Grifería',
        'icon' => 'bx bx-water',
        'search' => 'griferia',
        'names' => ['griferia'],
    ],
    [
        'label' => 'Materiales de construcción',
        'icon' => 'bx bx-building-house',
        'search' => 'construccion',
        'names' => ['construccion', 'materiales de construccion'],
    ],
    [
        'label' => 'Materiales eléctricos',
        'icon' => 'bx bx-bulb',
        'search' => 'materiales electricos',
        'names' => ['materiales electricos e iluminacion', 'materiales electricos', 'electricidad'],
    ],
];

foreach ($bmHomeFeaturedCategories as &$bmHomeFeaturedCategory) {
    $bmHomeFeaturedCategory['id'] = $bmFindHomeCategoryId($bmHomeFeaturedCategory['names']);
    $bmHomeFeaturedCategory['mode'] = $bmHomeFeaturedCategory['id'] !== '' ? 'categoria' : 'busqueda';
    $bmHomeFeaturedCategory['href'] = $bmHomeFeaturedCategory['id'] !== ''
        ? 'tienda.php?idsucursal=4&vista=tienda&categoria=' . rawurlencode($bmHomeFeaturedCategory['id'])
        : 'tienda.php?idsucursal=4&vista=tienda&buscar=' . rawurlencode($bmHomeFeaturedCategory['search']);
}
unset($bmHomeFeaturedCategory);
?>

<section class="bm-home-featured-categories bm-home-products" aria-label="Categorías destacadas de Industrial Collectors, S.A.">
    <div class="container">
        <div class="bm-home-featured-tabs-wrap">
            <div class="bm-home-featured-tabs" role="tablist" aria-label="Categorías destacadas">
                <?php foreach ($bmHomeFeaturedCategories as $index => $category): ?>
                    <button
                        class="bm-home-featured-tab<?php echo $index === 0 ? ' is-active' : ''; ?>"
                        type="button"
                        role="tab"
                        aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                        aria-controls="bm-home-category-products"
                        data-category-mode="<?php echo htmlspecialchars($category['mode'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-category-id="<?php echo htmlspecialchars($category['id'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-category-search="<?php echo htmlspecialchars($category['search'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-category-href="<?php echo htmlspecialchars($category['href'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-category-label="<?php echo htmlspecialchars($category['label'], ENT_QUOTES, 'UTF-8'); ?>">
                        <i class="<?php echo htmlspecialchars($category['icon'], ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i>
                        <span><?php echo htmlspecialchars($category['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="bm-home-featured-stage">
            <div class="bm-home-featured-products" id="bm-home-category-products" role="tabpanel" aria-live="polite">
                <div class="carousel-product-item">
                    <div class="alert alert-info">Cargando productos...</div>
                </div>
            </div>

            <div class="bm-home-featured-footer">
                <a id="bm-home-category-more" class="bm-home-featured-more" href="<?php echo htmlspecialchars($bmHomeFeaturedCategories[0]['href'], ENT_QUOTES, 'UTF-8'); ?>">
                    Ver todo
                </a>
                <button class="bm-home-featured-nav" type="button" data-featured-scroll="-1" aria-label="Productos anteriores">
                    <i class='bx bx-chevron-left' aria-hidden="true"></i>
                </button>
                <button class="bm-home-featured-nav" type="button" data-featured-scroll="1" aria-label="Productos siguientes">
                    <i class='bx bx-chevron-right' aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>
</section>

<?php
$bmPromoImage = is_file(__DIR__ . '/assets/img/paginaImagenes/material metalico.png')
    ? 'assets/img/paginaImagenes/material metalico.png'
    : 'assets/img/ImagenTemporalINDUSTRIALCOLLECTORS.png';
$bmNaturalNailsImage = is_file(__DIR__ . '/assets/img/paginaImagenes/Tus ideas toman forma.png')
    ? 'assets/img/paginaImagenes/Tus ideas toman forma.png'
    : 'assets/img/ImagenTemporalINDUSTRIALCOLLECTORS.png';
?>

<section class="bm-home-promo-section" aria-label="Catálogo de ferretería destacado">
    <div class="container">
        <div class="bm-home-promo bm-home-image-banner bm-home-image-banner--catalog">
            <img
                class="bm-home-banner-img"
                src="<?php echo htmlspecialchars($bmPromoImage, ENT_QUOTES); ?>"
                alt="Ferretería para hogares y profesionales"
                loading="lazy"
                decoding="async">

            <div class="bm-home-promo-content">
                <a href="tienda.php?idsucursal=4&vista=tienda" class="bm-home-btn">Ver cat&aacute;logo</a>
            </div>
        </div>
    </div>
</section>

<!-- Start New Products Area -->
<section class="special-products-area products-after-banner pb-70 bm-home-products bm-home-new-products" aria-label="Productos nuevos">
    <div class="container">
        <h2 class="bm-home-section-title">Productos nuevos</h2>
        <div class="row">


            <div class="col-lg-12 col-md-12">

                <div class="bm-home-products-grid" id="productos-nuevos">
                    <!-- Aqui se llenaran los productos dinamicamente -->
                </div>

                <div class="home-carousel-footer">
                    <a href="tienda.php?idsucursal=4&vista=tienda" class="home-carousel-more">
                        VER M&Aacute;S
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>
<!-- End New Products Area -->

<section class="bm-home-masglo" aria-label="Construcción, plomería, electricidad y pintura">
    <div class="container">
        <div class="bm-home-masglo-card bm-home-image-banner--solutions">
            <img
                class="bm-home-banner-img"
                src="<?php echo htmlspecialchars($bmNaturalNailsImage, ENT_QUOTES); ?>"
                alt="Soluciones para tus proyectos: construcción, plomería, electricidad y pintura"
                loading="lazy"
                decoding="async">

            <div class="bm-home-masglo-content">
                <a href="tienda.php?idsucursal=4&vista=tienda" class="bm-home-btn">Comprar ahora</a>
            </div>
        </div>
    </div>
</section>
<!-- Start Special Products Area -->
<section class="special-products-area products-after-banner pb-70 bm-home-products" aria-label="Los m&aacute;s vendidos">
    <div class="container">
        <h2 class="bm-home-section-title">Los m&aacute;s vendidos</h2>
        <div class="row">


            <div class="col-lg-12 col-md-12">

                <div class="bm-home-products-grid" id="productos-mas-vendidos">
                    <!-- Aquí se llenarán los productos dinámicamente -->
                </div>

                <div class="home-carousel-footer">
                    <a href="tienda.php?idsucursal=4&vista=tienda" class="home-carousel-more">
                        VER M&Aacute;S
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>
<!-- End Special Products Area -->

<style>


    /* ===========================
    IMÁGENES HOME AL ANCHO DEL CONTENIDO
    =========================== */

    .bm-home-image-banner,
    .bm-home-masglo-card {
        width: 100%;
        max-width: 100%;
        min-height: 0 !important;
        display: block !important;
        position: relative;
        overflow: hidden;
        background: none !important;
        padding: 0 !important;
        isolation: auto !important;
    }

    .bm-home-banner-img {
        width: 100%;
        height: auto;
        display: block;
        object-fit: contain;
        object-position: center center;
    }

    /* Boton catalogo destacado */
    .bm-home-promo-content {
        position: absolute;
        inset: 0;
        z-index: 2;
        max-width: none;
        padding: 0 !important;
        pointer-events: none;
        transform: none;
    }

    .bm-home-image-banner--catalog .bm-home-promo-content .bm-home-btn {
        left: 15%;
        position: absolute;
        top: 78%;
        pointer-events: auto;
    }

    /* Contenedor del texto de categorias principales */
    .bm-home-masglo-content {
        position: absolute;
        inset: 0;
        z-index: 2;
        width: 100%;
        max-width: none;
        text-align: center;
        transform: none;
        pointer-events: none;
    }

    /* Boton de categorias principales */
    .bm-home-image-banner--solutions .bm-home-masglo-content .bm-home-btn {
        position: absolute;
        left: 12%;
        top: 84%;
        bottom: auto;
        pointer-events: auto;
    }

    /* Evitar capas encima de las imágenes */
    .bm-home-promo::before,
    .bm-home-masglo-card::before {
        display: none !important;
        content: none !important;
    }

    @media (max-width: 767px) {
        .bm-home-promo-content {
            inset: 0;
            max-width: none;
        }

        .bm-home-promo-number {
            font-size: 46px !important;
            margin-bottom: 6px !important;
        }

        .bm-home-promo h2 {
            font-size: 15px !important;
            margin-bottom: 10px !important;
        }

        .bm-home-promo .bm-home-btn {
            min-height: 21px !important;
            padding: 4px 8px !important;
            font-size: 7px !important;
            line-height: 1 !important;
            white-space: nowrap !important;
        }

        .bm-home-image-banner--catalog .bm-home-promo-content .bm-home-btn {
            left: 17% !important;
            min-height: 18px !important;
            padding: 3px 7px !important;
            top: 75% !important;
            font-size: 6.3px !important;
            box-shadow: 0 7px 14px rgba(21, 21, 21, 0.22) !important;
        }

        .bm-home-masglo-content {
            inset: 0;
            width: 100%;
            max-width: none;
            transform: none;
        }

        .bm-home-image-banner--solutions .bm-home-masglo-content .bm-home-btn {
            left: 12%;
            top: 84%;
            bottom: auto;
            min-height: 20px !important;
            padding: 4px 8px !important;
            font-size: 7px !important;
            line-height: 1 !important;
            white-space: nowrap !important;
        }
    }

    /* Ancho visual de los banners del inicio: igual al contenedor de productos */
    .bm-home-image-banner,
    .bm-home-masglo-card {
        width: 100% !important;
        max-width: 100% !important;
        margin-left: auto !important;
        margin-right: auto !important;
    }

    .bm-home-final-image-link {
        width: calc(100% - 48px) !important;
        max-width: 990px !important;
        margin-left: auto !important;
        margin-right: auto !important;
    }

    .bm-home-image-banner,
    .bm-home-masglo,
    .bm-home-final-full {
        background: #ffffff !important;
    }

    .bm-home-banner-img,
    .bm-home-final-img {
        width: 100% !important;
        height: auto !important;
        display: block !important;
        object-fit: contain !important;
        object-position: center center !important;
    }

    @media (max-width: 991px) {

        .bm-home-image-banner,
        .bm-home-masglo-card {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }

        .bm-home-final-image-link {
            width: calc(100% - 36px) !important;
            max-width: 540px !important;
        }
    }

    @media (max-width: 767px) {

        .bm-home-image-banner,
        .bm-home-masglo-card {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }

        .bm-home-final-image-link {
            width: calc(100% - 42px) !important;
        }
    }

    /* Categorias destacadas: carrusel limpio por dispositivo. */
    .bm-home-featured-categories {
        padding: 26px 0 18px !important;
    }

    .bm-home-featured-categories .container {
        max-width: 1240px;
    }

    .bm-home-featured-tabs-wrap {
        justify-content: center !important;
        overflow: visible !important;
        padding: 0 0 18px !important;
    }

    .bm-home-featured-tabs {
        display: flex !important;
        margin: 0 auto !important;
        max-width: 760px !important;
        width: 100% !important;
    }

    .bm-home-featured-products .bm-products-state {
        grid-column: span 4;
    }

    .bm-home-featured-tab {
        flex: 1 1 0 !important;
        gap: 10px !important;
        min-height: 58px !important;
        min-width: 0 !important;
        padding: 10px 16px !important;
    }

    .bm-home-featured-tab span {
        font-size: 14px !important;
        line-height: 1.12 !important;
        overflow-wrap: normal !important;
        white-space: normal !important;
    }

    .bm-home-featured-stage {
        padding: 22px !important;
    }

    .bm-home-featured-products {
        display: grid !important;
        gap: 18px !important;
        grid-auto-columns: calc((100% - 54px) / 4) !important;
        grid-auto-flow: column !important;
        grid-template-columns: none !important;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        padding: 0 0 10px !important;
        scroll-snap-type: x mandatory !important;
        -ms-overflow-style: none !important;
        scrollbar-width: none !important;
        width: 100% !important;
    }

    .bm-home-featured-products::-webkit-scrollbar {
        display: none !important;
        height: 0 !important;
        width: 0 !important;
    }

    .bm-home-featured-products .carousel-product-item {
        box-sizing: border-box !important;
        display: block !important;
        margin: 0 !important;
        max-width: 100% !important;
        min-width: 0 !important;
        scroll-snap-align: start !important;
        width: 100% !important;
    }

    .bm-home-featured-products .single-arrivals-products {
        min-height: 380px !important;
        padding: 12px !important;
    }

    .bm-home-featured-products .arrivals-products-image {
        height: 148px !important;
        min-height: 148px !important;
    }

    .bm-home-featured-products .arrivals-products-image img,
    .bm-home-featured-products .arrivals-products-image img.bm-product-image-fallback,
    .bm-home-featured-products .arrivals-products-image img[src*="ImagenTemporalINDUSTRIALCOLLECTORS.png"] {
        height: 100% !important;
        max-height: 100% !important;
        max-width: 92% !important;
        object-fit: contain !important;
        width: 92% !important;
    }

    .bm-home-featured-products .arrivals-products-content {
        padding-top: 10px !important;
    }

    .bm-home-featured-products .arrivals-products-content h3,
    .bm-home-featured-products .arrivals-products-content h3 a {
        font-size: 13px !important;
        line-height: 1.2 !important;
        margin-bottom: 8px !important;
        max-height: 46px !important;
        min-height: 46px !important;
        -webkit-line-clamp: 3 !important;
    }

    .bm-home-featured-products .rating {
        margin-bottom: 8px !important;
    }

    .bm-home-featured-products .rating i {
        color: #f4c430 !important;
        font-size: 15px !important;
    }

    .bm-home-featured-products .product-card-price-normal {
        font-size: 12px !important;
        line-height: 1.2 !important;
        white-space: normal !important;
    }

    .bm-home-featured-products .product-card-actions {
        gap: 7px !important;
        padding-top: 10px !important;
    }

    .bm-home-featured-products .product-card-actions .product-card-action {
        min-height: 44px !important;
        padding: 10px 8px !important;
    }

    .bm-home-featured-products .product-card-actions .product-card-action-cart,
    .bm-home-featured-products .product-card-actions .product-card-action-details {
        font-size: 12px !important;
        letter-spacing: 0 !important;
    }

    .bm-home-featured-footer {
        gap: 12px !important;
        margin-top: 16px !important;
    }

    .bm-home-featured-more,
    .bm-home-featured-nav {
        min-height: 46px !important;
    }

    .bm-home-featured-more {
        font-size: 12px !important;
        min-width: 126px !important;
        padding: 10px 20px !important;
    }

    .bm-home-featured-nav {
        font-size: 24px !important;
        width: 48px !important;
    }

    @media (min-width: 768px) and (max-width: 991.98px) {
        .bm-home-featured-categories {
            padding: 22px 0 16px !important;
        }

        .bm-home-featured-tabs {
            max-width: 690px !important;
        }

        .bm-home-featured-tab {
            min-height: 54px !important;
            padding: 9px 12px !important;
        }

        .bm-home-featured-tab span {
            font-size: 12px !important;
        }

        .bm-home-featured-stage {
            padding: 18px !important;
        }

        .bm-home-featured-products {
            gap: 14px !important;
            grid-auto-columns: calc((100% - 28px) / 3) !important;
        }

        .bm-home-featured-products .bm-products-state {
            grid-column: span 3;
        }

        .bm-home-featured-products .single-arrivals-products {
            min-height: 330px !important;
            padding: 10px !important;
        }

        .bm-home-featured-products .arrivals-products-image {
            height: 124px !important;
            min-height: 124px !important;
        }

        .bm-home-featured-products .arrivals-products-content h3,
        .bm-home-featured-products .arrivals-products-content h3 a {
            font-size: 14px !important;
            max-height: 51px !important;
            min-height: 51px !important;
        }

        .bm-home-featured-products .product-card-actions .product-card-action {
            min-height: 44px !important;
            padding: 10px 8px !important;
        }

        .bm-home-featured-products .product-card-actions .product-card-action-cart,
        .bm-home-featured-products .product-card-actions .product-card-action-details {
            font-size: 12px !important;
        }
    }

    @media (max-width: 767.98px) {
        .bm-home-featured-categories {
            padding: 18px 0 14px !important;
        }

        .bm-home-featured-categories .container {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .bm-home-featured-tabs-wrap {
            overflow: visible !important;
            padding-bottom: 12px !important;
        }

        .bm-home-featured-tabs {
            display: grid !important;
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            max-width: none !important;
            width: 100% !important;
        }

        .bm-home-featured-tab {
            flex-direction: column !important;
            gap: 4px !important;
            min-height: 76px !important;
            padding: 10px 4px !important;
        }

        .bm-home-featured-tab i {
            font-size: 18px !important;
        }

        .bm-home-featured-tab span {
            font-size: 12px !important;
            line-height: 1.2 !important;
        }

        .bm-home-featured-stage {
            padding: 10px !important;
        }

        .bm-home-featured-products {
            gap: 10px !important;
            grid-auto-columns: calc((100% - 10px) / 2) !important;
            padding-bottom: 8px !important;
        }

        .bm-home-featured-products .bm-products-state {
            grid-column: span 2;
        }

        .bm-home-featured-products .single-arrivals-products {
            min-height: 268px !important;
            padding: 8px !important;
        }

        .bm-home-featured-products .arrivals-products-image {
            height: 88px !important;
            min-height: 88px !important;
        }

        .bm-home-featured-products .arrivals-products-content {
            padding-top: 8px !important;
        }

        .bm-home-featured-products .arrivals-products-content h3,
        .bm-home-featured-products .arrivals-products-content h3 a {
            font-size: 13px !important;
            line-height: 1.25 !important;
            margin-bottom: 6px !important;
            max-height: 49px !important;
            min-height: 49px !important;
        }

        .bm-home-featured-products .rating {
            margin-bottom: 5px !important;
            min-height: 12px !important;
        }

        .bm-home-featured-products .rating i {
            font-size: 12px !important;
        }

        .bm-home-featured-products .product-card-price-normal {
            font-size: 12px !important;
        }

        .bm-home-featured-products .product-card-actions {
            gap: 5px !important;
            padding-top: 7px !important;
        }

        .bm-home-featured-products .product-card-actions .product-card-action {
            min-height: 44px !important;
            padding: 10px 6px !important;
        }

        .bm-home-featured-products .product-card-actions .product-card-action-cart,
        .bm-home-featured-products .product-card-actions .product-card-action-details {
            font-size: 11.5px !important;
        }

        .bm-home-featured-footer {
            gap: 8px !important;
            margin-top: 12px !important;
        }

        .bm-home-featured-more,
        .bm-home-featured-nav {
            min-height: 44px !important;
        }

        .bm-home-featured-more {
            font-size: 12px !important;
            min-width: 104px !important;
            padding: 8px 13px !important;
        }

        .bm-home-featured-nav {
            font-size: 21px !important;
            width: 44px !important;
        }
    }

    @media (max-width: 360px) {
        .bm-home-featured-tab span {
            font-size: 12px !important;
        }

        .bm-home-featured-products {
            gap: 8px !important;
            grid-auto-columns: calc((100% - 8px) / 2) !important;
        }

        .bm-home-featured-products .single-arrivals-products {
            min-height: 248px !important;
            padding: 7px !important;
        }

        .bm-home-featured-products .arrivals-products-image {
            height: 76px !important;
            min-height: 76px !important;
        }

        .bm-home-featured-products .arrivals-products-content h3,
        .bm-home-featured-products .arrivals-products-content h3 a {
            font-size: 13px !important;
            max-height: 49px !important;
            min-height: 49px !important;
        }

        .bm-home-featured-products .product-card-actions .product-card-action-cart,
        .bm-home-featured-products .product-card-actions .product-card-action-details {
            font-size: 11.5px !important;
        }
    }
</style>



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
<script type="text/javascript" src="assets/js/index.js?v=<?php echo filemtime('assets/js/index.js'); ?>"></script>
<script>
    // Limpiar parámetros de error de la URL después de mostrar el mensaje
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('error')) {
            // Limpiar la URL después de 5 segundos
            setTimeout(function() {
                if (window.history && window.history.replaceState) {
                    const newUrl = window.location.pathname;
                    window.history.replaceState({}, document.title, newUrl);
                }
            }, 5000);
        }
    });
</script>
