<?php
require_once __DIR__ . '/assets/php/product_seo.php';
$catalog = json_decode((string) @file_get_contents(__DIR__ . '/storage/seo-catalog.json'), true);
if (!is_array($catalog) || !$catalog) {
    bm_product_error(503);
}
$page = isset($_GET['page']) ? filter_var($_GET['page'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : 1;
$pages = (int) ceil(count($catalog) / 60);
if (!$page || $page > $pages) {
    http_response_code(404);
    header('X-Robots-Tag: noindex');
    echo '<!doctype html><html lang="es"><meta charset="utf-8"><title>Página no encontrada</title><h1>Página no encontrada</h1><a href="catalogo.php">Ver catálogo</a></html>';
    exit;
}
$seo_title = 'Catálogo de productos' . ($page > 1 ? ' — Página ' . $page : '') . ' | La Broca Ferreterías';
$seo_description = 'Consulta nuestro catálogo de herramientas y productos de ferretería. Abre cada producto para conocer su precio y disponibilidad.';
$seo_canonical_url = 'https://labrocaferreterias.com/catalogo.php' . ($page > 1 ? '?page=' . $page : '');
include __DIR__ . '/head.php';
?>
<main class="container ptb-50">
    <h1>Catálogo de productos</h1>
    <p>Consulta el precio y la disponibilidad en la ficha de cada producto, o <a href="tienda.php">busca en la tienda</a>.</p>
    <ul class="row">
        <?php foreach (array_slice($catalog, ($page - 1) * 60, 60, true) as $id => $name): ?>
            <li class="col-md-6 mb-3"><a href="producto.php?id=<?= (int) $id ?>"><?= bm_product_escape($name) ?></a></li>
        <?php endforeach; ?>
    </ul>
    <nav aria-label="Páginas del catálogo">
        <p>Página <?= $page ?> de <?= $pages ?></p>
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a class="btn btn-light m-1" href="catalogo.php<?= $i > 1 ? '?page=' . $i : '' ?>" <?= $page === $i ? 'aria-current="page"' : '' ?>><?= $i ?></a>
        <?php endfor; ?>
    </nav>
</main>
<?php include __DIR__ . '/footer.php'; ?>
