<?php

namespace BeautyMarket\CyberSource;

class OrderForwarder
{
    private $config;
    private $logger;

    public function __construct(Config $config, SecureLogger $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function forward(array $session, array $paymentData = []): array
    {
        $apiData = $this->buildPayload($session, $paymentData);

        return $this->sendPayload($apiData, (string) ($session['reference_number'] ?? ''));
    }

    public function forwardQuote(array $apiData, string $reference = ''): array
    {
        return $this->sendPayload($this->orderedPayload($apiData), $reference);
    }

    private function sendPayload(array $apiData, string $reference = ''): array
    {
        $endpoint = trim((string) $this->config->get('orders.external_api_url'));
        if ($endpoint === '') {
            throw new GatewayException('La URL de la API externa no esta configurada.');
        }

        $encodedPayload = json_encode($apiData, JSON_UNESCAPED_SLASHES);
        if ($encodedPayload === false) {
            throw new GatewayException('No fue posible preparar el pedido para la API externa.');
        }

        $this->logger->info('External order API request prepared', [
            'reference' => $reference !== '' ? $reference : null,
            'no_auto_tarjeta' => $apiData['no_auto_tarjeta'],
            'total_venta' => $apiData['total_venta'],
            'fields' => array_keys($apiData),
        ]);

        $ch = curl_init($endpoint);
        if ($ch === false) {
            throw new GatewayException('No fue posible preparar la conexion con la API externa.');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $encodedPayload,
            CURLOPT_TIMEOUT => 30,
        ]);

        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $error) {
            $this->logger->error('External order API failed', ['error' => $error, 'reference' => $reference !== '' ? $reference : null]);
            throw new GatewayException('No fue posible enviar el pedido a la API externa.');
        }

        $decoded = json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            $this->logger->error('External order API invalid response', ['status' => $status, 'body' => $raw]);
            throw new GatewayException('La API externa devolvio una respuesta no valida.');
        }

        if ($status < 200 || $status >= 300 || empty($decoded['success'])) {
            $this->logger->error('External order API rejected request', ['status' => $status, 'response' => $decoded]);
            throw new GatewayException($decoded['message'] ?? 'La API externa rechazo el pedido.');
        }

        return $decoded;
    }

    public function buildPayload(array $session, array $paymentData = []): array
    {
        $apiData = $session['api_payload'] ?? null;
        if (!is_array($apiData)) {
            throw new GatewayException('No hay payload de pedido para reenviar.');
        }

        $authorizationNumber = $this->firstNonEmpty($paymentData, [
            'no_auto_tarjeta',
            'authorization_number',
            'authorization_code',
            'auth_code',
        ]);

        if ($authorizationNumber === '') {
            throw new GatewayException('NeoNet aprobo el pago sin devolver codigo de autorizacion.');
        }

        $apiData['valor_tarjeta'] = (float) ($apiData['total_venta'] ?? $session['amount'] ?? 0);
        $apiData['fecha_hora'] = $this->externalDateTime();
        $apiData['no_auto_tarjeta'] = $authorizationNumber;
        $apiData['comentario_cotizacion'] = $this->paymentComment(
            (string) ($apiData['comentario_cotizacion'] ?? 'Tienda en linea'),
            (string) ($session['reference_number'] ?? ''),
            $authorizationNumber
        );

        return $this->orderedPayload($apiData);
    }

    private function paymentComment(string $comment, string $reference, string $authorizationNumber): string
    {
        $comment = trim($comment);
        if (strpos($comment, 'Pago NeoNet aprobado') !== false) {
            return $comment;
        }

        return trim($comment
            . ' | Pago NeoNet aprobado'
            . ' | Ref: ' . $reference
            . ' | Autorizacion: ' . $authorizationNumber);
    }

    private function firstNonEmpty(array $fields, array $keys): string
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $fields)) {
                continue;
            }

            $value = trim((string) $fields[$key]);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function orderedPayload(array $apiData): array
    {
        return [
            'codigo_cliente' => $apiData['codigo_cliente'] ?? null,
            'nit' => (string) ($apiData['nit'] ?? ''),
            'nombre_cliente' => (string) ($apiData['nombre_cliente'] ?? ''),
            'telefono_cliente' => (string) ($apiData['telefono_cliente'] ?? ''),
            'direccion_cliente' => (string) ($apiData['direccion_cliente'] ?? ''),
            'correo_cliente' => (string) ($apiData['correo_cliente'] ?? ''),
            'valor_tarjeta' => (float) ($apiData['valor_tarjeta'] ?? 0),
            'tipo_documento_cliente' => (string) ($apiData['tipo_documento_cliente'] ?? ''),
            'fecha_hora' => (string) ($apiData['fecha_hora'] ?? ''),
            'forma_pago' => (string) ($apiData['forma_pago'] ?? ''),
            'tipo_comprobante' => (string) ($apiData['tipo_comprobante'] ?? ''),
            'tipo_pagoBacVisaNet' => (string) ($apiData['tipo_pagoBacVisaNet'] ?? ''),
            'opcionesAdicionales' => (string) ($apiData['opcionesAdicionales'] ?? ''),
            'total_venta' => (float) ($apiData['total_venta'] ?? 0),
            'total_ventades' => (float) ($apiData['total_ventades'] ?? 0),
            'idvendedor' => (int) ($apiData['idvendedor'] ?? 0),
            'tipo_cliente' => (string) ($apiData['tipo_cliente'] ?? 'Cliente'),
            'forma_productos' => (string) ($apiData['forma_productos'] ?? 'Detallado'),
            'comentario_cotizacion' => (string) ($apiData['comentario_cotizacion'] ?? ''),
            'destino' => (string) ($apiData['destino'] ?? $this->config->get('orders.destination', 'VENTA')),
            'no_auto_tarjeta' => (string) ($apiData['no_auto_tarjeta'] ?? ''),
            'datosArticulos' => $apiData['datosArticulos'] ?? ['articulos' => []],
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
}
