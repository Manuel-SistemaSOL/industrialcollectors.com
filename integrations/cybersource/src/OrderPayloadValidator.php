<?php

namespace BeautyMarket\CyberSource;

class OrderPayloadValidator
{
    private const PAYMENT_CARD = 'tarjeta';
    private const PAYMENT_TRANSFER = 'transferencia';
    private const PAYMENT_CASH_ON_DELIVERY = 'contra_entrega';
    private const DELIVERY_PICKUP = 'recoge_tienda';
    private const DELIVERY_OPTIONS = [
        'recoge_tienda' => [
            'label' => 'Recoge en tienda - GRATIS',
            'detail' => 'Retiro en tienda sin costo de envio.',
            'cost' => 0.00,
            'payments' => ['tarjeta', 'transferencia', 'contra_entrega'],
        ],
        'contra_capital' => [
            'label' => 'Zonas capitalinas - Q30.00',
            'detail' => 'Pago contra entrega en zonas capitalinas. Aplican restricciones en zonas de riesgo.',
            'cost' => 30.00,
            'payments' => ['contra_entrega'],
        ],
        'contra_municipios' => [
            'label' => 'Villa Nueva, Villa Canales, Ciudad San Cristobal o Mixco - Q35.00',
            'detail' => 'Pago contra entrega en Villa Nueva, Villa Canales, Ciudad San Cristobal o Mixco.',
            'cost' => 35.00,
            'payments' => ['contra_entrega'],
        ],
        'contra_interior' => [
            'label' => 'Interior de la Republica - Q45.00',
            'detail' => 'Pago contra entrega al interior de la Republica.',
            'cost' => 45.00,
            'payments' => ['contra_entrega'],
        ],
        'envio_previo_todo_pais' => [
            'label' => 'Envio a todo el pais - Q30.00',
            'detail' => 'Envio a todo el pais con pago previo por deposito, transferencia o tarjeta.',
            'cost' => 30.00,
            'payments' => ['tarjeta', 'transferencia'],
        ],
    ];

    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function validateCheckoutRequest(array $payload): array
    {
        return $this->validateOrderRequest($payload, [self::PAYMENT_CARD]);
    }

    public function validateQuoteRequest(array $payload): array
    {
        return $this->validateOrderRequest($payload, [self::PAYMENT_TRANSFER, self::PAYMENT_CASH_ON_DELIVERY]);
    }

    private function validateOrderRequest(array $payload, array $allowedPaymentMethods): array
    {
        $formData = $payload['formData'] ?? $payload['order'] ?? [];
        $apiData = $payload['apiData'] ?? $payload['api_payload'] ?? [];

        if (!is_array($formData) || !is_array($apiData)) {
            throw new GatewayException('Datos de pedido incompletos.');
        }

        $formData = $this->normalizeFormData($formData);

        if (!in_array($formData['formaPago'], $allowedPaymentMethods, true)) {
            throw new GatewayException('El metodo de pago no es valido para este proceso.');
        }

        $items = $this->normalizeItems($formData['carrito']);
        $subtotalNormal = $this->sumItems($items, 'subtotal_normal');
        $productSubtotal = $this->sumItems($items, 'subtotal');
        $amount = $this->money($productSubtotal + $formData['costoEnvio']);

        if ($productSubtotal <= 0 || $amount > (float) $this->config->get('security.max_amount', 999999.99)) {
            throw new GatewayException('Monto de pago no valido.');
        }

        $this->assertMoneyMatches($formData['total'], $amount, 'El total del formulario no coincide con el pedido.');
        $this->assertMoneyMatches($formData['subtotalNormal'], $subtotalNormal, 'El subtotal normal no coincide con el pedido.');

        $apiData = $this->normalizeApiPayload($apiData, $formData, $items, $productSubtotal, $amount);
        $this->validateApiPayload($apiData);

        return [
            'formData' => $formData,
            'apiData' => $apiData,
            'items' => $items,
            'amount' => $amount,
            'subtotal' => $productSubtotal,
            'currency' => (string) $this->config->get('currency', 'GTQ'),
        ];
    }

    public function validateApiPayload(array $apiData): void
    {
        $required = [
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

        foreach ($required as $field) {
            if (!array_key_exists($field, $apiData)) {
                throw new GatewayException("Payload externo incompleto: {$field}");
            }
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', (string) $apiData['fecha_hora'])) {
            throw new GatewayException('La fecha del pedido para la API externa no es valida.');
        }

        $valorTarjeta = $this->money($apiData['valor_tarjeta']);
        $totalVenta = $this->money($apiData['total_venta']);
        $formaPago = strtolower((string) $apiData['forma_pago']);

        if ($valorTarjeta > 0 || strpos($formaPago, 'tarjeta') !== false) {
            $this->assertMoneyMatches(
                $valorTarjeta,
                $totalVenta,
                'El valor de tarjeta no coincide con el total del pedido.'
            );
        }

        $items = $apiData['datosArticulos']['articulos'] ?? null;
        if (!is_array($items)) {
            throw new GatewayException('El detalle de articulos no es valido.');
        }

        foreach (['idarticulo', 'cantidad', 'precio_venta', 'subtotal1', 'subtotaldes1'] as $field) {
            if (empty($items[$field]) || !is_array($items[$field])) {
                throw new GatewayException("Detalle de articulos incompleto: {$field}");
            }
        }

        $count = count($items['idarticulo']);
        foreach (['cantidad', 'precio_venta', 'subtotal1', 'subtotaldes1'] as $field) {
            if (count($items[$field]) !== $count) {
                throw new GatewayException('El detalle de articulos tiene longitudes inconsistentes.');
            }
        }
    }

    public function validateReturnAmount(array $session, array $response): void
    {
        $returnedAmount = $this->money($response['req_amount'] ?? $response['auth_amount'] ?? $response['amount'] ?? 0);
        $sessionAmount = $this->money($session['amount'] ?? 0);
        $currency = strtoupper((string) ($response['req_currency'] ?? $response['currency'] ?? ''));

        if ($returnedAmount > 0 && abs($returnedAmount - $sessionAmount) > 0.01) {
            throw new GatewayException('El monto retornado por NeoNet no coincide con el pedido.');
        }

        if ($currency && $currency !== strtoupper((string) ($session['currency'] ?? ''))) {
            throw new GatewayException('La moneda retornada por NeoNet no coincide con el pedido.');
        }
    }

    private function normalizeFormData(array $formData): array
    {
        $nombre = $this->string($formData['nombreCompleto'] ?? '', 3, 80);
        if (!preg_match('/^[\p{L}\s]+$/u', $nombre)) {
            throw new GatewayException('Ingrese un nombre valido.');
        }

        $direccion = $this->string($formData['direccion'] ?? '', 5, 150);
        if (!preg_match('/^[\p{L}0-9\s#,\-.]+$/u', $direccion)) {
            throw new GatewayException('Ingrese una direccion valida.');
        }

        $telefono = preg_replace('/\D+/', '', (string) ($formData['telefono'] ?? ''));
        if (!preg_match('/^\d{8}$/', $telefono)) {
            throw new GatewayException('El telefono debe tener 8 digitos.');
        }

        $correo = trim((string) ($formData['correo'] ?? ''));
        if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new GatewayException('Ingrese un correo valido.');
        }

        $formaPago = $this->string($formData['formaPago'] ?? '', 1, 40);
        $entrega = $this->normalizeDeliveryOption($formData, $formaPago);
        $necesitaFactura = filter_var($formData['necesitaFactura'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $tipoDocumento = strtoupper($this->string($formData['tipoDocumento'] ?? 'NIT', 1, 8));
        $numeroDocumento = strtoupper($this->string($formData['numeroDocumento'] ?? 'C/F', 1, 20));

        if ($necesitaFactura) {
            if (!in_array($tipoDocumento, ['NIT', 'DPI'], true)) {
                throw new GatewayException('Seleccione un tipo de documento valido.');
            }

            if ($tipoDocumento === 'DPI' && !preg_match('/^\d{13}$/', $numeroDocumento)) {
                throw new GatewayException('El DPI debe tener 13 digitos.');
            }

            if ($tipoDocumento === 'NIT' && !preg_match('/^[0-9K\-]{3,20}$/', $numeroDocumento)) {
                throw new GatewayException('Ingrese un NIT valido.');
            }
        } else {
            $tipoDocumento = 'NIT';
            $numeroDocumento = 'C/F';
        }

        return [
            'nombreCompleto' => $nombre,
            'direccion' => $direccion,
            'telefono' => $telefono,
            'correo' => $correo,
            'necesitaFactura' => $necesitaFactura,
            'tipoDocumento' => $tipoDocumento,
            'numeroDocumento' => $numeroDocumento,
            'formaPago' => $formaPago,
            'modalidadEntrega' => $entrega['value'],
            'entregaLabel' => $entrega['label'],
            'entregaDetalle' => $entrega['detail'],
            'costoEnvio' => $entrega['cost'],
            'carrito' => $formData['carrito'] ?? [],
            'subtotalNormal' => $this->money($formData['subtotalNormal'] ?? 0),
            'total' => $this->money($formData['total'] ?? 0),
        ];
    }

    private function normalizeDeliveryOption(array $formData, string $paymentMethod): array
    {
        $value = trim((string) ($formData['modalidadEntrega'] ?? $formData['entrega'] ?? self::DELIVERY_PICKUP));

        if ($value === '' || !array_key_exists($value, self::DELIVERY_OPTIONS)) {
            throw new GatewayException('Seleccione una modalidad de entrega valida.');
        }

        $option = self::DELIVERY_OPTIONS[$value];
        if (!in_array($paymentMethod, $option['payments'], true)) {
            throw new GatewayException('La modalidad de entrega no corresponde al metodo de pago seleccionado.');
        }

        $expectedCost = $this->money($option['cost']);
        $receivedCost = $this->money($formData['costoEnvio'] ?? $expectedCost);
        $this->assertMoneyMatches($receivedCost, $expectedCost, 'El costo de envio no coincide con la modalidad seleccionada.');

        return [
            'value' => $value,
            'label' => (string) $option['label'],
            'detail' => (string) $option['detail'],
            'cost' => $expectedCost,
        ];
    }

    private function normalizeItems($cart): array
    {
        if (!is_array($cart) || count($cart) === 0) {
            throw new GatewayException('El carrito esta vacio.');
        }

        $items = [];
        foreach ($cart as $item) {
            if (!is_array($item)) {
                throw new GatewayException('El carrito contiene articulos no validos.');
            }

            $id = trim((string) ($item['idarticulo'] ?? $item['id'] ?? ''));
            if (!preg_match('/^[A-Za-z0-9_-]{1,50}$/', $id)) {
                throw new GatewayException('El carrito contiene un articulo no valido.');
            }

            $quantity = (int) ($item['cantidad'] ?? 1);
            if ($quantity < 1 || $quantity > 99) {
                throw new GatewayException('La cantidad de un articulo no es valida.');
            }

            $normalPrice = $this->money($item['precio_normal'] ?? $item['precio'] ?? 0);
            if ($normalPrice <= 0) {
                throw new GatewayException('El carrito contiene precios no validos.');
            }

            $lineSubtotal = $this->money($normalPrice * $quantity);
            $items[] = [
                'idarticulo' => $id,
                'cantidad' => $quantity,
                'precio_normal' => $normalPrice,
                'precio_venta' => $normalPrice,
                'subtotal_normal' => $lineSubtotal,
                'subtotal' => $lineSubtotal,
            ];
        }

        return $items;
    }

    private function normalizeApiPayload(array $apiData, array $formData, array $items, float $subtotal, float $amount): array
    {
        $this->assertMoneyMatches($apiData['total_venta'] ?? 0, $amount, 'El total de la API externa no coincide con el pago.');
        $this->assertMoneyMatches($apiData['total_ventades'] ?? 0, $subtotal, 'El subtotal de la API externa no coincide con el pedido.');

        $ids = [];
        $quantities = [];
        $prices = [];
        $subtotals = [];

        foreach ($items as $item) {
            $ids[] = (int) $item['idarticulo'];
            $quantities[] = (float) $item['cantidad'];
            $prices[] = $item['precio_venta'];
            $subtotals[] = $item['subtotal'];
        }

        $deliverySummary = $this->deliverySummary($formData);
        $comment = trim((string) ($apiData['comentario_cotizacion'] ?? 'Tienda en linea'));
        if ($comment === '') {
            $comment = 'Tienda en linea | Pago pendiente en NeoNet';
        }

        if (strpos($comment, 'Entrega:') === false) {
            $comment .= ' | ' . $deliverySummary;
        }

        $additionalOptions = trim((string) ($apiData['opcionesAdicionales'] ?? ''));
        if ($additionalOptions === '') {
            $additionalOptions = $deliverySummary;
        }

        return [
            'codigo_cliente' => $apiData['codigo_cliente'] ?? null,
            'nit' => $formData['numeroDocumento'],
            'nombre_cliente' => $formData['nombreCompleto'],
            'telefono_cliente' => $formData['telefono'],
            'direccion_cliente' => $formData['direccion'],
            'correo_cliente' => $formData['correo'],
            'valor_tarjeta' => $formData['formaPago'] === self::PAYMENT_CARD ? $amount : 0,
            'tipo_documento_cliente' => $formData['tipoDocumento'],
            'fecha_hora' => $this->externalDateTime(),
            'forma_pago' => $this->paymentLabel($formData['formaPago']),
            'tipo_comprobante' => trim((string) ($apiData['tipo_comprobante'] ?? '')),
            'tipo_pagoBacVisaNet' => trim((string) ($apiData['tipo_pagoBacVisaNet'] ?? '')),
            'opcionesAdicionales' => $additionalOptions,
            'total_venta' => $amount,
            'total_ventades' => $subtotal,
            'idvendedor' => (int) ($apiData['idvendedor'] ?? 0),
            'tipo_cliente' => 'Cliente',
            'forma_productos' => 'Detallado',
            'comentario_cotizacion' => $comment,
            'destino' => (string) $this->config->get('orders.destination', 'VENTA'),
            'no_auto_tarjeta' => trim((string) ($apiData['no_auto_tarjeta'] ?? '')),
            'datosArticulos' => [
                'articulos' => [
                    'idarticulo' => $ids,
                    'cantidad' => $quantities,
                    'precio_venta' => $prices,
                    'subtotal1' => $subtotals,
                    'subtotaldes1' => $subtotals,
                ],
            ],
        ];
    }

    private function externalDateTime(): string
    {
        $timezone = (string) $this->config->get('orders.timezone', 'America/Guatemala');

        try {
            $now = new \DateTimeImmutable('now', new \DateTimeZone($timezone));
        } catch (\Exception $exception) {
            $now = new \DateTimeImmutable('now');
        }

        return $now->format('Y-m-d H:i:s');
    }

    private function sumItems(array $items, string $field = 'subtotal'): float
    {
        return $this->money(array_sum(array_column($items, $field)));
    }

    private function paymentLabel(string $paymentMethod): string
    {
        $labels = [
            self::PAYMENT_CARD => 'Tarjeta de Credito/Debito',
            self::PAYMENT_TRANSFER => 'Transferencia Bancaria',
            self::PAYMENT_CASH_ON_DELIVERY => 'Pago Contra Entrega',
        ];

        return $labels[$paymentMethod] ?? $paymentMethod;
    }

    private function deliverySummary(array $formData): string
    {
        return sprintf(
            'Entrega: %s | Envio: Q%.2f',
            $formData['entregaLabel'],
            $formData['costoEnvio']
        );
    }

    private function assertMoneyMatches($received, float $expected, string $message): void
    {
        if (abs($this->money($received) - $expected) > 0.01) {
            throw new GatewayException($message);
        }
    }

    private function string($value, int $min, int $max): string
    {
        $value = trim(strip_tags((string) $value));
        $value = preg_replace('/\s+/', ' ', $value) ?? '';

        $length = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
        if ($length < $min || $length > $max) {
            throw new GatewayException('Datos de pedido incompletos o no validos.');
        }

        return $value;
    }

    private function money($value): float
    {
        return round((float) preg_replace('/[^0-9.\-]/', '', (string) $value), 2);
    }
}
