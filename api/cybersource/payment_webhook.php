<?php

declare(strict_types=1);

use BeautyMarket\CyberSource\GatewayException;

require __DIR__ . '/_bootstrap.php';

bm_require_method('POST');

try {
    $response = $_POST ?: bm_read_json_body();
    if (!$response) {
        throw new GatewayException('No se recibio respuesta de NeoNet.');
    }

    if (!$hostedCheckout->validateReturn($response)) {
        throw new GatewayException('La firma de NeoNet no es valida.');
    }

    $reference = bm_reference_from_response($response);
    if ($reference === '') {
        throw new GatewayException('NeoNet no retorno referencia de pedido.');
    }

    $session = $sessionStore->get($reference);
    if (!$session) {
        throw new GatewayException('Sesion de pago no encontrada.');
    }

    $validator->validateReturnAmount($session, $response);

    $storedResponse = $response;
    unset($storedResponse['signature']);
    $paymentStatus = $secureAcceptance->paymentStatus($response);
    $paymentSummary = $secureAcceptance->paymentSummary($response);
    $authorizationNumber = $paymentSummary['authorization_number'];

    if ($secureAcceptance->isApproved($response)) {
        $session = $sessionStore->update($reference, [
            'status' => 'PAID',
            'paid_at' => $session['paid_at'] ?? gmdate('c'),
            'payment_response' => $storedResponse,
            'payment' => $paymentSummary,
        ]);

        if (empty($session['order_forwarded_at'])) {
            $forwardResponse = $orderForwarder->forward($session, $paymentSummary);
            $sessionStore->update($reference, [
                'status' => 'PAID_ORDER_CREATED',
                'order_forwarded_at' => gmdate('c'),
                'order_forward_response' => $forwardResponse,
            ]);
        }

        $logger->info('Payment webhook approved', ['reference' => $reference]);
        bm_json_response([
            'success' => true,
            'status' => 'PAID_ORDER_CREATED',
            'gateway_status' => $paymentStatus,
            'reference_number' => $reference,
            'authorization_number' => $authorizationNumber,
            'transaction_id' => $paymentSummary['transaction_id'],
            'no_auto_tarjeta' => $authorizationNumber,
        ]);
    }

    $status = $secureAcceptance->sessionStatusForPaymentStatus($paymentStatus);
    $sessionStore->update($reference, [
        'status' => $status,
        'payment_response' => $storedResponse,
        'payment' => $paymentSummary,
    ]);

    $logger->warning('Payment webhook not approved', [
        'reference' => $reference,
        'status' => $status,
        'gateway_status' => $paymentStatus,
        'decision' => $response['decision'] ?? null,
    ]);

    bm_json_response([
        'success' => true,
        'status' => $status,
        'gateway_status' => $paymentStatus,
        'reference_number' => $reference,
        'authorization_number' => $authorizationNumber,
        'transaction_id' => $paymentSummary['transaction_id'],
        'no_auto_tarjeta' => $authorizationNumber,
    ]);
} catch (\BeautyMarket\CyberSource\GatewayException $exception) {
    $logger->warning('Payment webhook rejected', ['message' => $exception->getMessage()]);
    bm_json_response(['success' => false, 'message' => $exception->getMessage()], 422);
} catch (Throwable $exception) {
    $logger->error('Payment webhook failed', ['message' => $exception->getMessage()]);
    bm_json_response(['success' => false, 'message' => 'No fue posible procesar la notificacion.'], 500);
}
