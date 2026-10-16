<?php

$baseDir = dirname(__DIR__);
$mailEnabled = getenv('ORDER_MAIL_ENABLED');

$config = [
    'enabled' => true,
    'currency' => getenv('QUOTE_CURRENCY') ?: 'GTQ',
    'site' => [
        'base_url' => getenv('SITE_BASE_URL') ?: '',
    ],
    'security' => [
        'csrf_session_key' => 'quote_csrf_token',
        'rate_limit_window_seconds' => 60,
        'rate_limit_max_attempts' => 10,
        'max_amount' => 999999.99,
    ],
    'orders' => [
        'external_api_url' => getenv('QUOTE_API_URL') ?: 'https://ssl.sol.sistemasolgt.com/induscollesitio/api/api_cotizacion_insertar.php',
        'destination' => 'VENTA',
        'timezone' => 'America/Guatemala',
    ],
    'mail' => [
        'enabled' => filter_var($mailEnabled === false ? 'true' : $mailEnabled, FILTER_VALIDATE_BOOLEAN),
        'from_email' => getenv('ORDER_MAIL_FROM') ?: 'industrialcollectorsventas@gmail.com',
        'from_name' => getenv('ORDER_MAIL_FROM_NAME') ?: 'Industrial Collectors, S.A.',
        'reply_to' => getenv('ORDER_MAIL_REPLY_TO') ?: 'industrialcollectorsventas@gmail.com',
        'envelope_sender' => getenv('ORDER_MAIL_ENVELOPE_SENDER') ?: 'industrialcollectorsventas@gmail.com',
    ],
    'paths' => [
        'logs' => $baseDir . '/logs/quotes.log',
    ],
];

$privateConfig = __DIR__ . '/quotes.private.php';
if (is_file($privateConfig)) {
    $private = require $privateConfig;
    if (is_array($private)) {
        $config = array_replace_recursive($config, $private);
    }
}

return $config;
