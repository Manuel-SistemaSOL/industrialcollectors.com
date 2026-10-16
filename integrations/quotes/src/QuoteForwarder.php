<?php

namespace IndustrialCollectors\Quotes;

class QuoteForwarder
{
    private $config;
    private $logger;
    private $transport;

    public function __construct(Config $config, SecureLogger $logger, ?callable $transport = null)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->transport = $transport;
    }

    public function forwardQuote(array $apiData, string $reference = ''): array
    {
        $endpoint = trim((string) $this->config->get('orders.external_api_url'));
        if ($endpoint === '') {
            throw new QuoteException('La URL de la API externa no esta configurada.');
        }

        $apiData = $this->orderedPayload($apiData);
        $encodedPayload = json_encode($apiData, JSON_UNESCAPED_SLASHES);
        if ($encodedPayload === false) {
            throw new QuoteException('No fue posible preparar la cotizacion para la API externa.');
        }

        $this->logger->info('SOL quote request prepared', [
            'reference' => $reference !== '' ? $reference : null,
            'total_venta' => $apiData['total_venta'],
            'fields' => array_keys($apiData),
        ]);

        $response = $this->transport !== null
            ? call_user_func($this->transport, $endpoint, $encodedPayload)
            : $this->request($endpoint, $encodedPayload);
        $raw = $response['body'] ?? false;
        $status = (int) ($response['status'] ?? 0);
        $error = (string) ($response['error'] ?? '');

        if ($raw === false || $error !== '') {
            $this->logger->error('SOL quote connection failed', ['reference' => $reference]);
            throw new QuoteException('No fue posible enviar la cotizacion a la API externa.');
        }

        $decoded = json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            $this->logger->error('SOL quote invalid response', ['status' => $status, 'reference' => $reference]);
            throw new QuoteException('La API externa devolvio una respuesta no valida.');
        }

        if ($status < 200 || $status >= 300 || empty($decoded['success'])) {
            $this->logger->error('SOL quote rejected', ['status' => $status, 'reference' => $reference]);
            throw new QuoteException((string) ($decoded['message'] ?? 'La API externa rechazo la cotizacion.'));
        }

        return $decoded;
    }

    private function request(string $endpoint, string $encodedPayload): array
    {
        $ch = curl_init($endpoint);
        if ($ch === false) {
            throw new QuoteException('No fue posible preparar la conexion con la API externa.');
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

        return ['body' => $raw, 'status' => $status, 'error' => $error];
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
            'valor_tarjeta' => 0,
            'tipo_documento_cliente' => (string) ($apiData['tipo_documento_cliente'] ?? ''),
            'fecha_hora' => (string) ($apiData['fecha_hora'] ?? ''),
            'forma_pago' => (string) ($apiData['forma_pago'] ?? ''),
            'tipo_comprobante' => (string) ($apiData['tipo_comprobante'] ?? ''),
            // These empty legacy fields remain part of SOL's quote contract only.
            'tipo_pagoBacVisaNet' => '',
            'opcionesAdicionales' => (string) ($apiData['opcionesAdicionales'] ?? ''),
            'total_venta' => (float) ($apiData['total_venta'] ?? 0),
            'total_ventades' => (float) ($apiData['total_ventades'] ?? 0),
            'idvendedor' => (int) ($apiData['idvendedor'] ?? 0),
            'tipo_cliente' => (string) ($apiData['tipo_cliente'] ?? 'Cliente'),
            'forma_productos' => (string) ($apiData['forma_productos'] ?? 'Detallado'),
            'comentario_cotizacion' => (string) ($apiData['comentario_cotizacion'] ?? ''),
            'destino' => (string) ($apiData['destino'] ?? $this->config->get('orders.destination', 'VENTA')),
            'no_auto_tarjeta' => '',
            'datosArticulos' => $apiData['datosArticulos'] ?? ['articulos' => []],
        ];
    }
}
