<?php

declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

$logger->warning('Reversal endpoint disabled for Secure Acceptance Hosted Checkout');
bm_json_response([
    'success' => false,
    'message' => 'Endpoint deshabilitado. Esta integracion usa Secure Acceptance Hosted Checkout.',
], 410);
