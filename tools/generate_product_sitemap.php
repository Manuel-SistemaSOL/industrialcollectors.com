<?php
// Run from CLI after catalog changes. Never publish an incomplete API response.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
define('BM_SHOP_LIBRARY_ONLY', true);
require dirname(__DIR__) . '/api/shop_products.php';
try {
    $categories = bm_shop_post_external_uncached('api_tienda_categorias_listar.php');
    if (empty($categories['success']) || empty($categories['data'])) {
        throw new RuntimeException('No se pudo obtener el catálogo de categorías.');
    }
    $ids = [];
    foreach ($categories['data'] as $category) {
        if (isset($category['condicion']) && (int) $category['condicion'] !== 1) {
            continue;
        }
        $response = bm_shop_post_external_uncached('api_tienda_articulos_listarProductosxCategoria.php', ['idcategoria' => (string) $category['idcategoria'], 'idsucursal' => '4']);
        if (empty($response['success']) || !isset($response['data']) || !is_array($response['data'])) {
            throw new RuntimeException('Catálogo incompleto; se conserva el sitemap anterior.');
        }
        foreach ($response['data'] as $product) {
            $id = filter_var($product['idarticulo'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 999999]]);
            if ($id && !empty($product['nombre'])) {
                $ids[$id] = trim((string) $product['nombre']);
            }
        }
    }
    if (!$ids || count($ids) > 50000) {
        throw new RuntimeException('Cantidad de productos inválida para un sitemap.');
    }
    ksort($ids, SORT_NUMERIC);
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($ids as $id => $_) {
        $xml .= '  <url><loc>https://labrocaferreterias.com/producto.php?id=' . $id . '</loc></url>' . "\n";
    }
    $xml .= "</urlset>\n";
    $catalog = dirname(__DIR__) . '/storage/seo-catalog.json';
    if (file_put_contents($catalog . '.tmp', json_encode($ids, JSON_UNESCAPED_UNICODE), LOCK_EX) === false || !rename($catalog . '.tmp', $catalog)) {
        throw new RuntimeException('No se pudo guardar el catálogo navegable.');
    }
    $target = dirname(__DIR__) . '/sitemap-products.xml';
    if (file_put_contents($target . '.tmp', $xml, LOCK_EX) === false || !rename($target . '.tmp', $target)) {
        throw new RuntimeException('No se pudo guardar el sitemap.');
    }
    echo count($ids) . " productos publicados en sitemap-products.xml\n";
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . "\n");
    exit(1);
}
