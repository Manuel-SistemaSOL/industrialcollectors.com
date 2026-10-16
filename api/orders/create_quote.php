<?php

declare(strict_types=1);

use IndustrialCollectors\Quotes\QuoteException;

require __DIR__ . '/_bootstrap.php';

bm_quote_require_method('POST');

try {
    bm_quote_apply_rate_limit($config);
    $payload = bm_quote_read_json_body();
    bm_quote_verify_csrf($config, (string) ($payload['csrf_token'] ?? ''));

    bm_quote_json_response($quoteService->create($payload));
} catch (QuoteException $exception) {
    $logger->warning('Quote request rejected', ['message' => $exception->getMessage()]);
    bm_quote_json_response(['success' => false, 'message' => $exception->getMessage()], 422);
} catch (Throwable $exception) {
    $logger->error('Quote request failed', ['type' => get_class($exception)]);
    bm_quote_json_response(['success' => false, 'message' => 'No fue posible registrar la cotizacion.'], 500);
}
