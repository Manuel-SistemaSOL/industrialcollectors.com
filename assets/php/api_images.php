<?php

// Shared by server-rendered API images. Existing local image references do not
// pass through this resolver and remain unchanged.
function bm_api_image_fallback()
{
    return 'assets/img/ImagenTemporalINDUSTRIALCOLLECTORS.png';
}

function bm_resolve_api_image($value, $baseUrl)
{
    $fallback = bm_api_image_fallback();
    if (!is_scalar($value) || is_bool($value)) {
        return $fallback;
    }

    $image = trim((string) $value);
    $image = preg_replace('/^["\']+|["\']+$/', '', $image);
    $image = str_replace('\\', '/', $image);
    $path = preg_split('/[?#]/', $image, 2)[0];
    $comparable = strtolower(trim($path));
    $fileName = basename(rtrim($comparable, '/'));
    $stem = preg_replace('/\.(?:avif|gif|jpe?g|png|svg|webp)$/i', '', $fileName);
    $sentinel = preg_replace('/[\s_-]+/', '', $stem);
    $invalid = ['0', '404', 'default', 'null', 'undefined', 'false', 'nan', 'n/a',
        'noimage', 'nofoto', 'nopicture', 'placeholder', 'placeholderimage',
        'imageplaceholder', 'sinimagen', 'noaplica'];

    if ($image === '' || $comparable === '' || substr($comparable, -1) === '/'
        || strpos($image, '..') !== false || in_array($sentinel, $invalid, true)
        || in_array($comparable, $invalid, true)) {
        return $fallback;
    }

    if (preg_match('#^(?:data:image/|blob:)#i', $image)) {
        return $image;
    }
    if (strpos($image, '//') === 0) {
        $image = 'https:' . $image;
    }
    if (preg_match('#^https?://#i', $image)) {
        $url = str_replace(' ', '%20', $image);
        return filter_var($url, FILTER_VALIDATE_URL) ? $url : $fallback;
    }
    if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $image)) {
        return $fallback;
    }

    if (preg_match('#^(?:\./|/)?assets/#i', $image)) {
        return $image;
    }

    $baseUrl = rtrim((string) $baseUrl, '/') . '/';
    $baseParts = parse_url($baseUrl);
    if (empty($baseParts['scheme']) || empty($baseParts['host'])) {
        return $fallback;
    }
    $origin = $baseParts['scheme'] . '://' . $baseParts['host'];
    if (isset($baseParts['port'])) {
        $origin .= ':' . $baseParts['port'];
    }
    $relativePath = ltrim($path, '/');
    if (preg_match('#^[^/]+/files/#i', $relativePath)) {
        $baseUrl = $origin . '/';
    } elseif (preg_match('#^files/#i', $relativePath)) {
        $filesPosition = strpos($baseUrl, '/files/');
        if ($filesPosition !== false) {
            $baseUrl = substr($baseUrl, 0, $filesPosition + 1);
        }
    }

    $segments = array_filter(explode('/', $relativePath), 'strlen');
    $encodedPath = implode('/', array_map(function ($segment) {
        return rawurlencode(rawurldecode($segment));
    }, $segments));
    return $encodedPath === '' ? $fallback : $baseUrl . $encodedPath . substr($image, strlen($path));
}
