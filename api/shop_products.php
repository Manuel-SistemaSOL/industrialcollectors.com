<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/assets/php/category_visuals.php';

if (!defined('BM_SHOP_LIBRARY_ONLY')) {
    header('Content-Type: application/json; charset=utf-8');
    header('X-Robots-Tag: noindex');
}

const BM_SHOP_API_BASE = 'https://ssl.sol.sistemasolgt.com/induscollesitio/api/';
const BM_SHOP_CACHE_TTL = 120;
const BM_SHOP_STALE_CACHE_TTL = 86400;
const BM_SHOP_MULTI_BATCH_SIZE = 8;

function bm_shop_json(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function bm_shop_read_json(): array
{
    $raw = file_get_contents('php://input') ?: '';
    if (trim($raw) === '') {
        return $_POST ?: [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function bm_shop_cache_dir(): string
{
    return dirname(__DIR__) . '/storage/shop-products-cache';
}

function bm_shop_cache_key(string $prefix, string $endpoint, array $payload = []): string
{
    ksort($payload);
    return $prefix . '-' . hash('sha256', $endpoint . '|' . json_encode($payload, JSON_UNESCAPED_UNICODE));
}

function bm_shop_cache_path(string $key): string
{
    return bm_shop_cache_dir() . '/' . preg_replace('/[^a-zA-Z0-9_-]/', '', $key) . '.json';
}

function bm_shop_cache_read(string $key, int $ttl): ?array
{
    $file = bm_shop_cache_path($key);

    if (!is_file($file) || (time() - (int) filemtime($file)) > $ttl) {
        return null;
    }

    $decoded = json_decode((string) file_get_contents($file), true);
    return is_array($decoded) ? $decoded : null;
}

function bm_shop_cache_write(string $key, array $payload): void
{
    if (!is_dir(bm_shop_cache_dir())) {
        @mkdir(bm_shop_cache_dir(), 0775, true);
    }

    $file = bm_shop_cache_path($key);
    $tempFile = $file . '.tmp.' . getmypid();
    $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    if ($json === false || @file_put_contents($tempFile, $json, LOCK_EX) === false) {
        return;
    }

    @rename($tempFile, $file);
}

function bm_shop_is_cacheable_response(array $response): bool
{
    return !array_key_exists('success', $response) || $response['success'] !== false;
}

function bm_shop_create_external_curl(string $endpoint, array $payload = [])
{
    $ch = curl_init(BM_SHOP_API_BASE . $endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_PROXY => '',
        CURLOPT_NOPROXY => '*',
        CURLOPT_CONNECTTIMEOUT => 6,
        CURLOPT_TIMEOUT => 18,
    ]);

    return $ch;
}

function bm_shop_decode_external_response($response, string $error, int $status): array
{
    if ($response === false || $status >= 500) {
        throw new RuntimeException($error !== '' ? $error : 'No fue posible conectar con la API externa.');
    }

    $decoded = json_decode((string) $response, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('La API externa devolvio una respuesta no valida.');
    }

    return $decoded;
}

function bm_shop_post_external_uncached(string $endpoint, array $payload = []): array
{
    $ch = bm_shop_create_external_curl($endpoint, $payload);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    return bm_shop_decode_external_response($response, $error, $status);
}

function bm_shop_post_external(string $endpoint, array $payload = []): array
{
    $cacheKey = bm_shop_cache_key('external', $endpoint, $payload);
    $cached = bm_shop_cache_read($cacheKey, BM_SHOP_CACHE_TTL);

    if ($cached !== null) {
        return $cached;
    }

    try {
        $response = bm_shop_post_external_uncached($endpoint, $payload);
        if (bm_shop_is_cacheable_response($response)) {
            bm_shop_cache_write($cacheKey, $response);
        }
        return $response;
    } catch (Throwable $exception) {
        $stale = bm_shop_cache_read($cacheKey, BM_SHOP_STALE_CACHE_TTL);
        if ($stale !== null) {
            return $stale;
        }
        throw $exception;
    }
}

function bm_shop_post_external_multi(array $requests): array
{
    $results = [];
    $misses = [];

    foreach ($requests as $request) {
        $key = (string) ($request['key'] ?? '');
        $endpoint = (string) ($request['endpoint'] ?? '');
        $payload = is_array($request['payload'] ?? null) ? $request['payload'] : [];

        if ($key === '' || $endpoint === '') {
            continue;
        }

        $cacheKey = bm_shop_cache_key('external', $endpoint, $payload);
        $cached = bm_shop_cache_read($cacheKey, BM_SHOP_CACHE_TTL);

        if ($cached !== null) {
            $results[$key] = $cached;
            continue;
        }

        $request['cache_key'] = $cacheKey;
        $misses[] = $request;
    }

    foreach (array_chunk($misses, BM_SHOP_MULTI_BATCH_SIZE) as $batch) {
        $multi = curl_multi_init();
        $handles = [];

        foreach ($batch as $request) {
            $ch = bm_shop_create_external_curl((string) $request['endpoint'], is_array($request['payload'] ?? null) ? $request['payload'] : []);
            curl_multi_add_handle($multi, $ch);
            $handles[] = ['handle' => $ch, 'request' => $request];
        }

        $active = null;
        do {
            $status = curl_multi_exec($multi, $active);
            if ($active) {
                curl_multi_select($multi, 1.0);
            }
        } while ($active && $status === CURLM_OK);

        foreach ($handles as $item) {
            $ch = $item['handle'];
            $request = $item['request'];
            $response = curl_multi_getcontent($ch);
            $error = curl_error($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

            try {
                $decoded = bm_shop_decode_external_response($response, $error, $status);
                $results[(string) $request['key']] = $decoded;
                if (bm_shop_is_cacheable_response($decoded)) {
                    bm_shop_cache_write((string) $request['cache_key'], $decoded);
                }
            } catch (Throwable $exception) {
                $stale = bm_shop_cache_read((string) $request['cache_key'], BM_SHOP_STALE_CACHE_TTL);
                if ($stale !== null) {
                    $results[(string) $request['key']] = $stale;
                }
            }

            curl_multi_remove_handle($multi, $ch);
            curl_close($ch);
        }

        curl_multi_close($multi);
    }

    return $results;
}

function bm_shop_text($value): string
{
    return trim(preg_replace('/\s+/', ' ', (string) ($value ?? '')));
}

function bm_shop_int($value, int $fallback = 0, int $min = 0, int $max = PHP_INT_MAX): int
{
    $number = filter_var($value, FILTER_VALIDATE_INT);
    if ($number === false) {
        return $fallback;
    }

    return max($min, min($max, (int) $number));
}

function bm_shop_float_or_null($value): ?float
{
    if ($value === null || $value === '') {
        return null;
    }

    $number = filter_var($value, FILTER_VALIDATE_FLOAT);
    return $number === false ? null : (float) $number;
}

function bm_shop_product_id(array $product): string
{
    return bm_shop_text($product['idarticulo'] ?? $product['id'] ?? $product['codigo'] ?? '');
}

function bm_shop_collect_category_ids(): array
{
    try {
        $response = bm_shop_post_external('api_tienda_categorias_listar.php');
    } catch (Throwable $exception) {
        return [];
    }

    $items = $response['data'] ?? [];
    if (!is_array($items)) {
        return [];
    }

    $ids = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        if (isset($item['condicion']) && (int) $item['condicion'] !== 1) {
            continue;
        }

        $id = bm_shop_text($item['idcategoria'] ?? '');
        if ($id !== '' && !in_array($id, $ids, true)) {
            $ids[] = $id;
        }
    }

    return $ids;
}

function bm_shop_collect_products_by_search_terms(string $idsucursal): array
{
    $products = [];
    $terms = ['e', 'a', 'i', 'o', 'u'];

    foreach ($terms as $term) {
        try {
            $response = bm_shop_post_external('api_tienda_articulos_listarProductosxSearch.php', [
                'search' => $term,
                'idsucursal' => $idsucursal,
            ]);
        } catch (Throwable $exception) {
            continue;
        }

        if (is_array($response['data'] ?? null)) {
            $products = array_merge($products, $response['data']);
        }
    }

    return $products;
}

function bm_shop_normalize_search_key(string $value): string
{
    $value = strtr($value, [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u', 'Ñ' => 'n',
        '×' => 'x',
    ]);
    $value = strtr($value, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
    $value = preg_replace('/\p{Mn}+/u', '', $value) ?? '';
    $value = preg_replace('~(?<=\d)\s*([x/])\s*(?=\d)~u', '$1', $value);
    $value = preg_replace('~(?<=\d)\s*[.,]\s*(?=\d)~u', '.', $value);
    $value = preg_replace_callback('/\d+\.\d+/', function (array $match): string {
        return rtrim(rtrim($match[0], '0'), '.');
    }, $value);
    $value = preg_replace('/(?<=\d)\s+(?=(?:mm|cm|m|mts?|pies?|lbs?)\b)/u', '', $value);
    $value = preg_replace('~(?<!\d)[./]|[./](?!\d)~', ' ', $value);
    return trim((string) preg_replace('~[^a-z0-9./]+~', ' ', $value));
}

function bm_shop_match_products(array $products, string $key): array
{
    if ($key === '') {
        return [];
    }

    $patterns = array_map(function (string $term): string {
        $pattern = preg_quote($term, '~');
        // Keep measurements such as 1/8 and 3x6 from matching 1/80 or 13x60.
        return preg_match('/^\d/', $term) ? '~(?<![\d./])' . $pattern . '(?![\d./])~' : '~' . $pattern . '~';
    }, array_unique(explode(' ', $key)));
    $containsTerms = function (string $text) use ($patterns): bool {
        foreach ($patterns as $pattern) {
            if (!preg_match($pattern, $text)) {
                return false;
            }
        }
        return true;
    };

    $codeQuery = strpbrk($key, './') === false ? str_replace(' ', '', $key) : '';
    $allowPartialCode = $codeQuery !== '' && !preg_match('/^\d+(?:x\d+|(?:mm|cm|m|mts?|pies?|lbs?)\b)/', $key);
    $matches = [];
    foreach (bm_shop_dedupe_products($products) as $product) {
        $name = bm_shop_normalize_search_key(bm_shop_text($product['nombre'] ?? ''));
        $id = bm_shop_text($product['idarticulo'] ?? $product['id'] ?? '');
        $category = bm_shop_text($product['categoria'] ?? '');
        $codes = [];
        foreach (['codigo', 'codigobarra', 'codigo_barras', 'sku'] as $field) {
            if (isset($product[$field]) && is_scalar($product[$field])) {
                $codes[] = (string) $product[$field];
            }
        }

        $exactCode = $id !== '' && $key === $id;
        $partialCode = false;
        foreach ($codes as $code) {
            $codeKey = bm_shop_normalize_search_key($code);
            $compactCode = preg_replace('/[^a-z0-9]/', '', $codeKey);
            $exactCode = $exactCode || $codeKey === $key || ($codeQuery !== '' && $compactCode === $codeQuery);
            if ($allowPartialCode && strpos($compactCode, $codeQuery) !== false) {
                $partialCode = true;
            }
        }

        if ($exactCode) {
            $rank = 0;
        } elseif ($containsTerms($name) || $partialCode) {
            $rank = 1;
        } else {
            $details = bm_shop_normalize_search_key(implode(' ', array_merge($codes, [
                $name, $product['descripcion'] ?? '', $category, bm_category_display_name($category),
            ])));
            if (!$containsTerms($details)) {
                continue;
            }
            $rank = 2;
        }

        $matches[] = ['order' => [$rank, $name, $id], 'product' => $product];
    }

    usort($matches, function (array $left, array $right): int {
        return $left['order'] <=> $right['order'];
    });
    return array_column($matches, 'product');
}

function bm_shop_collect_catalog_products(string $idsucursal, string $search = ''): array
{
    $requests = [];
    foreach (bm_shop_collect_category_ids() as $idcategoria) {
        $requests[] = [
            'key' => $idcategoria,
            'endpoint' => 'api_tienda_articulos_listarProductosxCategoria.php',
            'payload' => ['idcategoria' => $idcategoria, 'idsucursal' => $idsucursal],
        ];
    }
    if ($search !== '') {
        $requests[] = [
            'key' => 'search',
            'endpoint' => 'api_tienda_articulos_listarProductosxSearch.php',
            'payload' => ['search' => $search, 'idsucursal' => $idsucursal],
        ];
    }

    $responses = bm_shop_post_external_multi($requests);
    $products = [];
    $hasResponse = false;
    foreach ($requests as $request) {
        $response = $responses[$request['key']] ?? [];
        if (bm_shop_is_cacheable_response($response) && is_array($response['data'] ?? null)) {
            $products = array_merge($products, $response['data']);
            $hasResponse = true;
        }
    }
    if ($search !== '' && !$hasResponse) {
        throw new RuntimeException('No fue posible consultar el catalogo para la busqueda.');
    }

    return $products;
}

function bm_shop_collect_products(array $input): array
{
    $mode = bm_shop_text($input['mode'] ?? 'nuevos');
    $idsucursal = bm_shop_text($input['idsucursal'] ?? '4') ?: '4';
    $priceMax = bm_shop_float_or_null($input['price_max'] ?? null);

    if ($mode === 'categoria') {
        $idcategoria = bm_shop_text($input['idcategoria'] ?? '');
        if ($idcategoria === '') {
            return [];
        }

        $response = bm_shop_post_external('api_tienda_articulos_listarProductosxCategoria.php', [
            'idcategoria' => $idcategoria,
            'idsucursal' => $idsucursal,
        ]);

        return is_array($response['data'] ?? null) ? $response['data'] : [];
    }

    if ($mode === 'busqueda') {
        $search = is_scalar($input['search'] ?? null) ? bm_shop_text($input['search']) : '';
        $key = bm_shop_normalize_search_key($search);
        if ($key === '') {
            return [];
        }

        return bm_shop_match_products(bm_shop_collect_catalog_products($idsucursal, $search), $key);
    }

    if ($mode === 'ofertas') {
        $response = bm_shop_post_external('api_tienda_ofertas.php', [
            'idsucursal' => $idsucursal,
        ]);

        return is_array($response['data'] ?? null) ? $response['data'] : [];
    }

    if ($mode === 'inicio_nuevos') {
        $response = bm_shop_post_external('api_tienda_articulos_listarProductosnuevos.php', [
            'idsucursal' => $idsucursal,
        ]);

        return is_array($response['data'] ?? null) ? $response['data'] : [];
    }

    if ($mode === 'mas_vendidos') {
        $response = bm_shop_post_external('api_tienda_articulos_listarProductosnuevos_lomasvendido.php', [
            'idsucursal' => $idsucursal,
        ]);

        return is_array($response['data'] ?? null) ? $response['data'] : [];
    }

    if ($priceMax !== null && $priceMax <= 100) {
        $response = bm_shop_post_external('api_tienda_menosde100.php', [
            'idsucursal' => $idsucursal,
        ]);

        return is_array($response['data'] ?? null) ? $response['data'] : [];
    }

    $products = bm_shop_collect_catalog_products($idsucursal);
    if ($products !== []) {
        return $products;
    }

    $products = bm_shop_collect_products_by_search_terms($idsucursal);
    if ($products !== []) {
        return $products;
    }

    $response = bm_shop_post_external('api_tienda_articulos_listarProductosnuevos.php', [
        'idsucursal' => $idsucursal,
    ]);

    return is_array($response['data'] ?? null) ? $response['data'] : [];
}

function bm_shop_dedupe_products(array $products): array
{
    $seen = [];
    $result = [];

    foreach ($products as $index => $product) {
        if (!is_array($product)) {
            continue;
        }

        $id = bm_shop_product_id($product);
        $key = $id !== '' ? $id : bm_shop_text($product['nombre'] ?? '') . '-' . $index;
        if (isset($seen[$key])) {
            continue;
        }

        $seen[$key] = true;
        $result[] = $product;
    }

    return $result;
}

function bm_shop_price(array $product): float
{
    $offerPrice = filter_var(
        $product['precio_09']
            ?? $product['precio09']
            ?? $product['precio9']
            ?? $product['precio_oferta']
            ?? 0,
        FILTER_VALIDATE_FLOAT
    );

    if ($offerPrice !== false && (float) $offerPrice > 0) {
        return (float) $offerPrice;
    }

    $price = filter_var($product['precio_venta'] ?? $product['precio'] ?? 0, FILTER_VALIDATE_FLOAT);
    return $price === false ? 0.0 : (float) $price;
}

function bm_shop_price_range(array $products): array
{
    if ($products === []) {
        return ['min' => 0, 'max' => 0];
    }

    $prices = array_map('bm_shop_price', $products);
    return [
        'min' => floor(min($prices)),
        'max' => ceil(max($prices)),
    ];
}

function bm_shop_filter_price(array $products, ?float $min, ?float $max): array
{
    return array_values(array_filter($products, function (array $product) use ($min, $max): bool {
        $price = bm_shop_price($product);
        if ($min !== null && $price < $min) {
            return false;
        }

        if ($max !== null && $price > $max) {
            return false;
        }

        return true;
    }));
}

if (defined('BM_SHOP_LIBRARY_ONLY')) {
    return;
}

try {
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        bm_shop_json(['success' => false, 'message' => 'Metodo no permitido.'], 405);
    }

    $input = bm_shop_read_json();
    $page = bm_shop_int($input['page'] ?? 1, 1, 1);
    $perPage = bm_shop_int($input['per_page'] ?? 30, 30, 1, 60);
    $mode = bm_shop_text($input['mode'] ?? 'nuevos') ?: 'nuevos';
    $priceMin = bm_shop_float_or_null($input['price_min'] ?? null);
    $priceMax = bm_shop_float_or_null($input['price_max'] ?? null);

    $products = bm_shop_dedupe_products(bm_shop_collect_products($input));
    $range = bm_shop_price_range($products);
    $products = bm_shop_filter_price($products, $priceMin, $priceMax);

    $total = count($products);
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;
    $pageProducts = array_slice($products, $offset, $perPage);
    $from = $total > 0 ? $offset + 1 : 0;
    $to = $total > 0 ? $offset + count($pageProducts) : 0;

    bm_shop_json([
        'success' => true,
        'data' => $pageProducts,
        'meta' => [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
            'from' => $from,
            'to' => $to,
            'mode' => $mode,
        ],
        'filters' => [
            'price_range' => $range,
            'selected_price_min' => $priceMin,
            'selected_price_max' => $priceMax,
        ],
    ]);
} catch (Throwable $exception) {
    error_log('[La Broca shop_products] ' . $exception->getMessage());

    bm_shop_json([
        'success' => false,
        'message' => 'Error al cargar los productos.',
    ], 500);
}
