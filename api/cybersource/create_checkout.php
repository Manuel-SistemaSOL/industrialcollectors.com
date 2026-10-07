<?php

declare(strict_types=1);

use BeautyMarket\CyberSource\GatewayException;

require __DIR__ . '/_bootstrap.php';

bm_require_method('POST');

try {
    if (!$config->bool('enabled', true)) {
        throw new GatewayException('La pasarela de pago no esta habilitada.');
    }

    bm_apply_rate_limit($config);

    $payload = bm_read_json_body();
    bm_verify_csrf($config, (string) ($payload['csrf_token'] ?? ''));

    $validated = $validator->validateCheckoutRequest($payload);

    if ($config->bool('secure_acceptance.enabled', true)) {
        $secureAcceptance->assertConfigured();
    }
    $reference = bm_reference_number();
    $checkoutUrl = $hostedCheckout->createCheckoutUrl($reference);

    $session = $sessionStore->create([
        'reference_number' => $reference,
        'status' => 'CREATED',
        'amount' => $validated['amount'],
        'subtotal' => $validated['subtotal'],
        'currency' => $validated['currency'],
        'checkout_url' => $checkoutUrl,
        'form_data' => $validated['formData'],
        'api_payload' => $validated['apiData'],
        'items' => $validated['items'],
        'payment' => [
            'provider' => 'cybersource_secure_acceptance',
            'environment' => $config->get('environment', 'test'),
            'operation' => $config->get('operation', 'sale'),
        ],
    ]);

    $logger->info('Checkout session created', [
        'reference' => $reference,
        'amount' => $session['amount'],
        'currency' => $session['currency'],
    ]);

    bm_json_response([
        'success' => true,
        'checkout_url' => $checkoutUrl,
        'reference_number' => $reference,
        'message' => 'Sesion de pago creada.',
    ]);
} catch (\BeautyMarket\CyberSource\GatewayException $exception) {
    $logger->warning('Checkout session rejected', ['message' => $exception->getMessage()]);
    bm_json_response(['success' => false, 'message' => $exception->getMessage()], 422);
} catch (Throwable $exception) {
    $logger->error('Checkout session failed', ['message' => $exception->getMessage()]);
    bm_json_response(['success' => false, 'message' => 'No fue posible iniciar el pago.'], 500);
}
