<?php

function bm_product_escape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function bm_product_error(int $status): void
{
    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');
    if ($status === 503) {
        header('Retry-After: 300');
    } else {
        header('X-Robots-Tag: noindex');
    }
    $title = $status === 404 ? 'Producto no encontrado' : 'Catálogo temporalmente no disponible';
    echo '<!doctype html><html lang="es-GT"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>' . $title . ' | Industrial Collectors, S.A.</title></head><body><main><h1>' . $title . '</h1><p>';
    echo $status === 404 ? 'El enlace no corresponde a un producto disponible.' : 'Intenta nuevamente en unos minutos.';
    echo '</p><a href="tienda.php">Visitar la tienda</a></main></body></html>';
    exit;
}
