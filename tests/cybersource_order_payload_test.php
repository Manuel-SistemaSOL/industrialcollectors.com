<?php

declare(strict_types=1);

use BeautyMarket\CyberSource\Config;
use BeautyMarket\CyberSource\GatewayException;
use BeautyMarket\CyberSource\OrderForwarder;
use BeautyMarket\CyberSource\OrderPayloadValidator;
use BeautyMarket\CyberSource\SecureAcceptanceService;
use BeautyMarket\CyberSource\SecureLogger;

require dirname(__DIR__) . '/integrations/cybersource/Autoload.php';

function assertSameValue($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message . '. Esperado: ' . var_export($expected, true)
            . '; recibido: ' . var_export($actual, true));
    }
}

function assertTrueValue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$config = new Config([
    'currency' => 'GTQ',
    'security' => ['max_amount' => 999999.99],
    'orders' => [
        'destination' => 'VENTA',
        'timezone' => 'America/Guatemala',
        'external_api_url' => 'https://example.invalid/orders',
    ],
]);

$validator = new OrderPayloadValidator($config);
$secureAcceptance = new SecureAcceptanceService($config);
$logPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'beautymarket-cybersource-payload-test.log';
$forwarder = new OrderForwarder($config, new SecureLogger($logPath));

$checkout = $validator->validateCheckoutRequest([
    'formData' => [
        'nombreCompleto' => 'Cliente Prueba',
        'direccion' => 'Zona 1 Guatemala',
        'telefono' => '55555555',
        'correo' => 'cliente@example.com',
        'necesitaFactura' => false,
        'tipoDocumento' => 'NIT',
        'numeroDocumento' => 'C/F',
        'formaPago' => 'tarjeta',
        'carrito' => [
            [
                'idarticulo' => 20,
                'cantidad' => 2,
                'precio_normal' => 125.50,
            ],
        ],
        'subtotalNormal' => 251.00,
        'total' => 251.00,
    ],
    'apiData' => [
        'total_venta' => 251.00,
        'total_ventades' => 251.00,
        'comentario_cotizacion' => 'Tienda en linea | Pago pendiente en NeoNet',
    ],
]);

$contractFields = [
    'codigo_cliente',
    'nit',
    'nombre_cliente',
    'telefono_cliente',
    'direccion_cliente',
    'correo_cliente',
    'valor_tarjeta',
    'tipo_documento_cliente',
    'fecha_hora',
    'forma_pago',
    'tipo_comprobante',
    'tipo_pagoBacVisaNet',
    'opcionesAdicionales',
    'total_venta',
    'total_ventades',
    'idvendedor',
    'tipo_cliente',
    'forma_productos',
    'comentario_cotizacion',
    'destino',
    'no_auto_tarjeta',
    'datosArticulos',
];

assertSameValue($contractFields, array_keys($checkout['apiData']), 'El contrato externo no conserva el orden esperado');
assertSameValue(251.0, $checkout['apiData']['valor_tarjeta'], 'valor_tarjeta debe coincidir con total_venta');
assertSameValue('', $checkout['apiData']['no_auto_tarjeta'], 'La autorizacion debe iniciar vacia antes del pago');
assertTrueValue(
    preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $checkout['apiData']['fecha_hora']) === 1,
    'fecha_hora debe usar el formato de la API externa'
);

$quoteCheckout = $validator->validateQuoteRequest([
    'formData' => [
        'nombreCompleto' => 'Cliente Prueba',
        'direccion' => 'Zona 1 Guatemala',
        'telefono' => '55555555',
        'correo' => 'cliente@example.com',
        'necesitaFactura' => false,
        'tipoDocumento' => 'NIT',
        'numeroDocumento' => 'C/F',
        'formaPago' => 'transferencia',
        'carrito' => [
            [
                'idarticulo' => 20,
                'cantidad' => 2,
                'precio_normal' => 125.50,
            ],
        ],
        'subtotalNormal' => 251.00,
        'total' => 251.00,
    ],
    'apiData' => [
        'total_venta' => 251.00,
        'total_ventades' => 251.00,
        'comentario_cotizacion' => 'Tienda en linea | Pendiente de confirmacion/cobro',
    ],
]);

assertSameValue($contractFields, array_keys($quoteCheckout['apiData']), 'La cotizacion debe conservar el contrato externo');
assertSameValue(0, $quoteCheckout['apiData']['valor_tarjeta'], 'La cotizacion sin tarjeta no debe enviar valor_tarjeta');
assertSameValue('Transferencia Bancaria', $quoteCheckout['apiData']['forma_pago'], 'Debe etiquetar transferencia para la API externa');
assertSameValue([125.5], $quoteCheckout['apiData']['datosArticulos']['articulos']['precio_venta'], 'Transferencia debe conservar el precio del producto');
assertSameValue([251.0], $quoteCheckout['apiData']['datosArticulos']['articulos']['subtotal1'], 'Transferencia debe conservar el subtotal del producto');

$quoteWithShipping = $validator->validateQuoteRequest([
    'formData' => [
        'nombreCompleto' => 'Cliente Prueba',
        'direccion' => 'Zona 1 Guatemala',
        'telefono' => '55555555',
        'correo' => 'cliente@example.com',
        'necesitaFactura' => false,
        'tipoDocumento' => 'NIT',
        'numeroDocumento' => 'C/F',
        'formaPago' => 'transferencia',
        'modalidadEntrega' => 'envio_previo_todo_pais',
        'costoEnvio' => 30.00,
        'carrito' => [
            [
                'idarticulo' => 20,
                'cantidad' => 2,
                'precio_normal' => 125.50,
            ],
        ],
        'subtotalNormal' => 251.00,
        'total' => 281.00,
    ],
    'apiData' => [
        'total_venta' => 281.00,
        'total_ventades' => 251.00,
        'comentario_cotizacion' => 'Tienda en linea | Pendiente de confirmacion/cobro',
    ],
]);

assertSameValue(281.0, $quoteWithShipping['apiData']['total_venta'], 'El total debe incluir el envio seleccionado');
assertSameValue(251.0, $quoteWithShipping['apiData']['total_ventades'], 'El subtotal debe conservar solo productos');
assertTrueValue(
    strpos($quoteWithShipping['apiData']['comentario_cotizacion'], 'Envio: Q30.00') !== false,
    'El comentario debe incluir el costo de envio'
);

$gatewayResponse = [
    'decision' => 'ACCEPT',
    'reason_code' => '100',
    'auth_code' => '361665',
    'auth_trans_ref_no' => '7812337269696389704501',
    'transaction_id' => '7812337269696389704501',
];
$payment = $secureAcceptance->paymentSummary($gatewayResponse);

assertSameValue('361665', $payment['authorization_number'], 'Debe usarse auth_code como autorizacion');
assertSameValue('361665', $payment['no_auto_tarjeta'], 'no_auto_tarjeta debe usar auth_code');
assertSameValue(
    '7812337269696389704501',
    $payment['transaction_id'],
    'La referencia larga debe conservarse como transaction_id'
);
assertSameValue(
    '654321',
    $secureAcceptance->authorizationNumber([
        'auth_code' => '',
        'authorization_code' => '654321',
    ]),
    'Debe ignorarse un campo de autorizacion vacio cuando existe otro valido'
);

$outgoing = $forwarder->buildPayload([
    'reference_number' => 'BMG-TEST-001',
    'amount' => 251.00,
    'api_payload' => $checkout['apiData'],
], $payment);

assertSameValue('361665', $outgoing['no_auto_tarjeta'], 'El payload final debe enviar el codigo de autorizacion');
assertSameValue(251.0, $outgoing['valor_tarjeta'], 'El payload final debe enviar el valor cobrado a tarjeta');
assertSameValue($contractFields, array_keys($outgoing), 'El reenvio final debe conservar el contrato ordenado');
assertTrueValue(!array_key_exists('no_autorizacion', $outgoing), 'No deben enviarse alias fuera del contrato');
assertTrueValue(!array_key_exists('autorizacion', $outgoing), 'No deben enviarse alias fuera del contrato');
assertTrueValue(
    strpos($outgoing['comentario_cotizacion'], 'Autorizacion: 361665') !== false,
    'El comentario debe dejar trazabilidad de la autorizacion'
);

$fallbackAuthorization = $forwarder->buildPayload([
    'reference_number' => 'BMG-TEST-EMPTY-FALLBACK',
    'amount' => 251.00,
    'api_payload' => $checkout['apiData'],
], [
    'no_auto_tarjeta' => '',
    'authorization_number' => '654321',
]);
assertSameValue(
    '654321',
    $fallbackAuthorization['no_auto_tarjeta'],
    'El reenvio debe ignorar aliases vacios antes de una autorizacion valida'
);

assertSameValue(
    SecureAcceptanceService::STATUS_DECLINED,
    $secureAcceptance->paymentStatus([
        'decision' => 'DECLINE',
        'reason_code' => '100',
        'auth_response' => '00',
    ]),
    'Una decision rechazada no debe aprobarse por un codigo secundario inconsistente'
);

$missingAuthorizationRejected = false;
try {
    $forwarder->buildPayload([
        'reference_number' => 'BMG-TEST-002',
        'amount' => 251.00,
        'api_payload' => $checkout['apiData'],
    ], [
        'transaction_id' => '7812337269696389704501',
    ]);
} catch (GatewayException $exception) {
    $missingAuthorizationRejected = strpos($exception->getMessage(), 'codigo de autorizacion') !== false;
}

assertTrueValue(
    $missingAuthorizationRejected,
    'No debe enviarse un pedido aprobado sin no_auto_tarjeta'
);

if (is_file($logPath)) {
    unlink($logPath);
}

echo "OK: contrato externo y no_auto_tarjeta verificados.\n";
