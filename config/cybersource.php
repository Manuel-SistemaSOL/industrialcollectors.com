<?php

$baseDir = dirname(__DIR__);

$config = [
    'enabled' => true,
    'environment' => getenv('CYBERSOURCE_ENV') ?: 'test',
    'currency' => getenv('CYBERSOURCE_CURRENCY') ?: 'GTQ',
    'operation' => getenv('CYBERSOURCE_OPERATION') ?: 'sale',
    'site' => [
        'base_url' => getenv('SITE_BASE_URL') ?: '',
        'return_path' => '/api/cybersource/payment_return.php',
        'cancel_path' => '/cart.php',
    ],
    'secure_acceptance' => [
        'enabled' => true,
        'profile_id' => getenv('CYBERSOURCE_SA_PROFILE_ID') ?: '',
        'access_key' => getenv('CYBERSOURCE_SA_ACCESS_KEY') ?: '',
        'secret_key' => getenv('CYBERSOURCE_SA_SECRET_KEY') ?: '',
        'test_endpoint' => 'https://testsecureacceptance.cybersource.com/pay',
        'production_endpoint' => 'https://secureacceptance.cybersource.com/pay',
        'locale' => 'en-us',
    ],
    'security' => [
        'admin_token' => getenv('CYBERSOURCE_ADMIN_TOKEN') ?: '',
        'csrf_session_key' => 'cybersource_csrf_token',
        'rate_limit_window_seconds' => 60,
        'rate_limit_max_attempts' => 10,
        'max_amount' => 999999.99,
        'session_ttl_minutes' => 90,
    ],
    'orders' => [
        'external_api_url' => 'https://ssl.sol.sistemasolgt.com/induscollesitio/api/api_cotizacion_insertar.php',
        'destination' => 'VENTA',
        'timezone' => 'America/Guatemala',
    ],
    'mail' => [
        'enabled' => filter_var(getenv('ORDER_MAIL_ENABLED') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'from_email' => getenv('ORDER_MAIL_FROM') ?: 'labrocaferreterias01@gmail.com',
        'from_name' => getenv('ORDER_MAIL_FROM_NAME') ?: 'Industrial Collectors, S.A.',
        'reply_to' => getenv('ORDER_MAIL_REPLY_TO') ?: 'labrocaferreterias01@gmail.com',
        'envelope_sender' => getenv('ORDER_MAIL_ENVELOPE_SENDER') ?: 'labrocaferreterias01@gmail.com',
    ],
    'paths' => [
        'storage' => $baseDir . '/storage/cybersource',
        'logs' => $baseDir . '/logs/cybersource.log',
    ],
];

$privateConfig = __DIR__ . '/cybersource.private.php';
if (is_file($privateConfig)) {
    $private = require $privateConfig;
    if (is_array($private)) {
        $config = array_replace_recursive($config, $private);
    }
}

return $config;
