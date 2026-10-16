<?php

declare(strict_types=1);

use IndustrialCollectors\Quotes\Config;
use IndustrialCollectors\Quotes\QuoteException;
use IndustrialCollectors\Quotes\QuotePayloadValidator;

require dirname(__DIR__) . '/integrations/quotes/Autoload.php';

function assertQuoteValue($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message);
    }
}

function assertQuoteRejected(callable $operation, string $message): void
{
    try {
        $operation();
    } catch (QuoteException $exception) {
        return;
    }
    throw new RuntimeException($message);
}

function quoteTestPayload(string $method): array
{
    return [
        'formData' => [
            'nombreCompleto' => 'Cliente Prueba',
            'direccion' => 'Zona 1 Guatemala',
            'telefono' => '55555555',
            'correo' => 'cliente@example.invalid',
            'necesitaFactura' => false,
            'tipoDocumento' => 'NIT',
            'numeroDocumento' => 'C/F',
            'formaPago' => $method,
            'modalidadEntrega' => 'recoge_tienda',
            'costoEnvio' => 0,
            'carrito' => [
                ['idarticulo' => 20, 'cantidad' => 2, 'precio_normal' => 125.50],
            ],
            'subtotalNormal' => 251.00,
            'total' => 251.00,
        ],
        'apiData' => [
            'total_venta' => 251.00,
            'total_ventades' => 251.00,
            'comentario_cotizacion' => 'Tienda en linea | Cotizacion pendiente de confirmacion',
        ],
    ];
}

$config = new Config([
    'currency' => 'GTQ',
    'security' => ['max_amount' => 999999.99],
    'orders' => ['destination' => 'VENTA', 'timezone' => 'America/Guatemala'],
]);
$validator = new QuotePayloadValidator($config);
$contractFields = [
    'codigo_cliente', 'nit', 'nombre_cliente', 'telefono_cliente', 'direccion_cliente',
    'correo_cliente', 'valor_tarjeta', 'tipo_documento_cliente', 'fecha_hora', 'forma_pago',
    'tipo_comprobante', 'tipo_pagoBacVisaNet', 'opcionesAdicionales', 'total_venta',
    'total_ventades', 'idvendedor', 'tipo_cliente', 'forma_productos', 'comentario_cotizacion',
    'destino', 'no_auto_tarjeta', 'datosArticulos',
];
$labels = [
    'transferencia' => 'Transferencia Bancaria',
    'efectivo' => 'Efectivo al recoger',
    'tarjeta' => 'Tarjeta al recoger',
];

foreach ($labels as $method => $label) {
    $payload = quoteTestPayload($method);
    $payload['apiData']['valor_tarjeta'] = 251;
    $payload['apiData']['no_auto_tarjeta'] = 'CLIENT-UNTRUSTED';
    $payload['apiData']['tipo_pagoBacVisaNet'] = 'CLIENT-UNTRUSTED';
    $payload['apiData']['datosArticulos'] = ['articulos' => ['idarticulo' => [999], 'precio_venta' => [1]]];
    $quote = $validator->validateQuoteRequest($payload);
    $api = $quote['apiData'];

    assertQuoteValue($contractFields, array_keys($api), 'Se altero el contrato de SOL.');
    assertQuoteValue($label, $api['forma_pago'], 'La forma de pago debe indicar el pago presencial elegido.');
    assertQuoteValue(0, $api['valor_tarjeta'], 'Una cotizacion nunca debe registrar dinero cobrado a tarjeta.');
    assertQuoteValue('', $api['no_auto_tarjeta'], 'No debe aceptar autorizaciones suministradas por el navegador.');
    assertQuoteValue('', $api['tipo_pagoBacVisaNet'], 'El campo legado de SOL debe permanecer vacio.');
    assertQuoteValue([20], $api['datosArticulos']['articulos']['idarticulo'], 'Debe reconstruir el detalle del carrito.');
    assertQuoteValue([125.5], $api['datosArticulos']['articulos']['precio_venta'], 'Debe conservar los precios actuales del carrito.');
    assertQuoteValue([251.0], $api['datosArticulos']['articulos']['subtotal1'], 'Debe conservar cantidades y subtotales.');
    assertQuoteValue([251.0], $api['datosArticulos']['articulos']['subtotaldes1'], 'No debe agregar descuentos ni recargos.');
    assertQuoteValue(251.0, $quote['amount'], 'No debe agregar recargos por forma de pago.');
    assertQuoteValue(0.0, $quote['formData']['costoEnvio'], 'Recoger en tienda no debe incluir envio.');
    assertQuoteValue('recoge_tienda', $quote['formData']['modalidadEntrega'], 'La entrega debe ser recoger en tienda.');
    assertQuoteValue(1, preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $api['fecha_hora']), 'Debe conservar el formato de fecha de SOL.');

    foreach (['contra_capital', 'contra_municipios', 'contra_interior', 'envio_previo_todo_pais', 'entrega_desconocida'] as $delivery) {
        $invalid = quoteTestPayload($method);
        $invalid['formData']['modalidadEntrega'] = $delivery;
        assertQuoteRejected(function () use ($validator, $invalid) {
            $validator->validateQuoteRequest($invalid);
        }, 'Debe rechazar envios para todos los metodos de pago.');
    }
}

foreach (['contra_entrega', 'pago_online', 'link_pago', 'desconocido'] as $method) {
    assertQuoteRejected(function () use ($validator, $method) {
        $validator->validateQuoteRequest(quoteTestPayload($method));
    }, 'Debe rechazar formas de pago fuera de las tres autorizadas.');
}

$tampered = [
    ['formData', 'total', 252],
    ['formData', 'subtotalNormal', 1],
    ['formData', 'costoEnvio', 30],
    ['apiData', 'total_venta', 1],
    ['apiData', 'total_ventades', 1],
];
foreach ($tampered as $change) {
    $payload = quoteTestPayload('transferencia');
    $payload[$change[0]][$change[1]] = $change[2];
    assertQuoteRejected(function () use ($validator, $payload) {
        $validator->validateQuoteRequest($payload);
    }, 'Debe rechazar importes incoherentes o costos de envio.');
}

foreach ([0, 100] as $quantity) {
    $payload = quoteTestPayload('efectivo');
    $payload['formData']['carrito'][0]['cantidad'] = $quantity;
    assertQuoteRejected(function () use ($validator, $payload) {
        $validator->validateQuoteRequest($payload);
    }, 'Debe preservar los limites de cantidades.');
}

$payload = quoteTestPayload('tarjeta');
$payload['formData']['carrito'] = [];
assertQuoteRejected(function () use ($validator, $payload) {
    $validator->validateQuoteRequest($payload);
}, 'No debe aceptar un carrito vacio.');

$payload = quoteTestPayload('transferencia');
$payload['formData']['necesitaFactura'] = true;
$cfQuote = $validator->validateQuoteRequest($payload);
assertQuoteValue(true, $cfQuote['formData']['necesitaFactura'], 'Debe conservar la solicitud de factura CF.');
assertQuoteValue('NIT', $cfQuote['apiData']['tipo_documento_cliente'], 'CF debe conservar el tipo de documento esperado por SOL.');
assertQuoteValue('C/F', $cfQuote['apiData']['nit'], 'Debe permitir una factura a consumidor final.');

$payload['formData']['numeroDocumento'] = 'INVALIDO';
assertQuoteRejected(function () use ($validator, $payload) {
    $validator->validateQuoteRequest($payload);
}, 'Permitir C/F no debe omitir la validacion de otros NIT.');

$payload['formData']['tipoDocumento'] = 'DPI';
$payload['formData']['numeroDocumento'] = '123';
assertQuoteRejected(function () use ($validator, $payload) {
    $validator->validateQuoteRequest($payload);
}, 'Debe conservar validacion de factura y DPI.');

$limited = new QuotePayloadValidator(new Config(['security' => ['max_amount' => 100]]));
assertQuoteRejected(function () use ($limited) {
    $limited->validateQuoteRequest(quoteTestPayload('tarjeta'));
}, 'Debe conservar el limite de importe permitido.');

echo "OK: tres formas presenciales, recogida, contrato SOL, importes y validaciones.\n";
