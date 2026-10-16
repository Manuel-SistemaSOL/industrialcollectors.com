<?php

declare(strict_types=1);

use IndustrialCollectors\Quotes\Config;
use IndustrialCollectors\Quotes\QuoteEmailNotifier;
use IndustrialCollectors\Quotes\QuoteException;
use IndustrialCollectors\Quotes\QuoteForwarder;
use IndustrialCollectors\Quotes\QuotePayloadValidator;
use IndustrialCollectors\Quotes\QuoteService;
use IndustrialCollectors\Quotes\SecureLogger;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rootDir = dirname(__DIR__, 2);
require_once $rootDir . '/integrations/quotes/Autoload.php';

$config = Config::fromFile($rootDir . '/config/quotes.php');
$logger = new SecureLogger((string) $config->get('paths.logs', $rootDir . '/logs/quotes.log'));
$quoteService = new QuoteService(
    $config,
    new QuotePayloadValidator($config),
    new QuoteForwarder($config, $logger),
    new QuoteEmailNotifier($config, $logger),
    $logger
);

function bm_quote_json_response(array $payload, int $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function bm_quote_require_method(string $method): void
{
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== strtoupper($method)) {
        header('Allow: ' . strtoupper($method));
        bm_quote_json_response(['success' => false, 'message' => 'Metodo no permitido.'], 405);
    }
}

function bm_quote_read_json_body(): array
{
    $raw = file_get_contents('php://input') ?: '';
    if (trim($raw) === '') {
        return $_POST ?: [];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        throw new QuoteException('JSON no valido.');
    }

    return $decoded;
}

function bm_quote_verify_csrf(Config $config, string $token): void
{
    $key = (string) $config->get('security.csrf_session_key', 'quote_csrf_token');
    $expected = (string) ($_SESSION[$key] ?? '');
    if ($expected === '' || $token === '' || !hash_equals($expected, $token)) {
        throw new QuoteException('Token de seguridad invalido. Recargue la pagina e intente de nuevo.');
    }
}

function bm_quote_apply_rate_limit(Config $config): void
{
    $window = max(10, (int) $config->get('security.rate_limit_window_seconds', 60));
    $maxAttempts = max(1, (int) $config->get('security.rate_limit_max_attempts', 10));
    $now = time();
    $bucket = $_SESSION['quote_request_rate'] ?? ['start' => $now, 'attempts' => 0];
    if (!is_array($bucket) || ($now - (int) ($bucket['start'] ?? 0)) > $window) {
        $bucket = ['start' => $now, 'attempts' => 0];
    }

    $bucket['attempts'] = (int) ($bucket['attempts'] ?? 0) + 1;
    $_SESSION['quote_request_rate'] = $bucket;
    if ($bucket['attempts'] > $maxAttempts) {
        throw new QuoteException('Demasiadas solicitudes de cotizacion. Espere un momento e intente nuevamente.');
    }
}
