<?php

declare(strict_types=1);

use BeautyMarket\CyberSource\GatewayException;

require dirname(__DIR__) . '/cybersource/_bootstrap.php';

bm_require_method('POST');

try {
    if (!$config->bool('enabled', true)) {
        throw new GatewayException('La tienda no esta habilitada para recibir pedidos.');
    }

    bm_apply_rate_limit($config);

    $payload = bm_read_json_body();
    bm_verify_csrf($config, (string) ($payload['csrf_token'] ?? ''));

    $validated = $validator->validateQuoteRequest($payload);
    $reference = bm_reference_number();
    $apiData = $validated['apiData'];
    $apiData['comentario_cotizacion'] = trim(
        (string) ($apiData['comentario_cotizacion'] ?? 'Tienda en linea')
        . ' | Ref tienda: '
        . $reference
    );

    $forwardResponse = $orderForwarder->forwardQuote($apiData, $reference);
    $quoteNumber = bm_quote_number_from_response($forwardResponse);

    $emailResult = $orderEmailNotifier->sendOrderConfirmation([
        'email' => (string) ($apiData['correo_cliente'] ?? ''),
        'customer_name' => (string) ($apiData['nombre_cliente'] ?? ''),
        'reference_number' => $reference,
        'quote_number' => $quoteNumber,
        'payment_label' => (string) ($apiData['forma_pago'] ?? ''),
        'amount' => (float) ($apiData['total_venta'] ?? 0),
        'currency' => (string) $config->get('currency', 'GTQ'),
    ]);

    $logger->info('Quote order created', [
        'reference' => $reference,
        'quote_number' => $quoteNumber,
        'email_sent' => $emailResult['sent'] ?? false,
    ]);

    $response = $forwardResponse;
    $response['success'] = true;
    $response['reference_number'] = $reference;
    $response['email_sent'] = (bool) ($emailResult['sent'] ?? false);
    $response['email_status'] = (string) ($emailResult['reason'] ?? '');

    if ($quoteNumber !== '') {
        $response['idcotizacion'] = $quoteNumber;
        $response['numero_cotizacion'] = $quoteNumber;
    }

    bm_json_response($response);
} catch (GatewayException $exception) {
    $logger->warning('Quote order rejected', ['message' => $exception->getMessage()]);
    bm_json_response(['success' => false, 'message' => $exception->getMessage()], 422);
} catch (Throwable $exception) {
    $logger->error('Quote order failed', ['message' => $exception->getMessage()]);
    bm_json_response(['success' => false, 'message' => 'No fue posible registrar el pedido.'], 500);
}
