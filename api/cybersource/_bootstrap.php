<?php

declare(strict_types=1);

use BeautyMarket\CyberSource\Config;
use BeautyMarket\CyberSource\GatewayException;
use BeautyMarket\CyberSource\HostedCheckoutService;
use BeautyMarket\CyberSource\OrderForwarder;
use BeautyMarket\CyberSource\OrderEmailNotifier;
use BeautyMarket\CyberSource\OrderPayloadValidator;
use BeautyMarket\CyberSource\PaymentSessionStore;
use BeautyMarket\CyberSource\SecureAcceptanceService;
use BeautyMarket\CyberSource\SecureLogger;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rootDir = dirname(__DIR__, 2);

require_once $rootDir . '/integrations/cybersource/Autoload.php';

$config = Config::fromFile($rootDir . '/config/cybersource.php');
$logger = new SecureLogger((string) $config->get('paths.logs', $rootDir . '/logs/cybersource.log'));
$sessionStore = new PaymentSessionStore(
    (string) $config->get('paths.storage', $rootDir . '/storage/cybersource'),
    (int) $config->get('security.session_ttl_minutes', 90)
);
$validator = new OrderPayloadValidator($config);
$secureAcceptance = new SecureAcceptanceService($config);
$hostedCheckout = new HostedCheckoutService($config, $secureAcceptance);
$orderForwarder = new OrderForwarder($config, $logger);
$orderEmailNotifier = new OrderEmailNotifier($config, $logger);

function bm_json_response(array $payload, int $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function bm_require_method(string $method): void
{
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== strtoupper($method)) {
        bm_json_response(['success' => false, 'message' => 'Metodo no permitido.'], 405);
    }
}

function bm_read_json_body(): array
{
    $raw = file_get_contents('php://input') ?: '';
    if (trim($raw) === '') {
        return $_POST ?: [];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        throw new GatewayException('JSON no valido.');
    }

    return $decoded;
}

function bm_verify_csrf(Config $config, string $token): void
{
    $key = (string) $config->get('security.csrf_session_key', 'cybersource_csrf_token');
    $expected = (string) ($_SESSION[$key] ?? '');

    if ($expected === '' || $token === '' || !hash_equals($expected, $token)) {
        throw new GatewayException('Token de seguridad invalido. Recargue la pagina e intente de nuevo.');
    }
}

function bm_apply_rate_limit(Config $config): void
{
    $window = max(10, (int) $config->get('security.rate_limit_window_seconds', 60));
    $maxAttempts = max(1, (int) $config->get('security.rate_limit_max_attempts', 10));
    $now = time();
    $bucket = $_SESSION['cybersource_checkout_rate'] ?? ['start' => $now, 'attempts' => 0];

    if (!is_array($bucket) || ($now - (int) ($bucket['start'] ?? 0)) > $window) {
        $bucket = ['start' => $now, 'attempts' => 0];
    }

    $bucket['attempts'] = (int) ($bucket['attempts'] ?? 0) + 1;
    $_SESSION['cybersource_checkout_rate'] = $bucket;

    if ($bucket['attempts'] > $maxAttempts) {
        throw new GatewayException('Demasiados intentos de pago. Espere un momento e intente nuevamente.');
    }
}

function bm_require_internal_token(Config $config): void
{
    $expected = trim((string) $config->get('security.admin_token', ''));
    if ($expected === '') {
        throw new GatewayException('Token interno pendiente de configurar.');
    }

    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $provided = (string) ($headers['X-Internal-Token'] ?? $headers['x-internal-token'] ?? '');

    if ($provided === '' || !hash_equals($expected, $provided)) {
        bm_json_response(['success' => false, 'message' => 'No autorizado.'], 401);
    }
}

function bm_reference_number(): string
{
    return 'BMG-' . gmdate('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(4)));
}

function bm_reference_from_response(array $payload): string
{
    return (string) (
        $payload['req_reference_number']
        ?? $payload['reference_number']
        ?? $payload['clientReferenceInformation']['code']
        ?? ''
    );
}

function bm_payment_transaction_id(array $payload): string
{
    return (string) (
        $payload['transaction_id']
        ?? $payload['request_id']
        ?? $payload['auth_trans_ref_no']
        ?? $payload['id']
        ?? ''
    );
}

function bm_quote_number_from_response(array $payload): string
{
    foreach (['idcotizacion', 'numero_cotizacion', 'numeroCotizacion', 'id_cotizacion', 'cotizacion', 'quote_number'] as $key) {
        if (!array_key_exists($key, $payload)) {
            continue;
        }

        $value = trim((string) $payload[$key]);
        if ($value !== '') {
            return preg_replace('/[^A-Za-z0-9._#-]/', '', $value) ?? '';
        }
    }

    return '';
}

function bm_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function bm_error_page(string $title, string $message, int $status = 400)
{
    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . bm_escape($title) . '</title>';
    echo '<style>body{font-family:Arial,sans-serif;background:#f6f6f3;color:#222;margin:0;display:grid;min-height:100vh;place-items:center}.box{background:#fff;border:1px solid #e8e5dc;border-radius:8px;max-width:560px;padding:28px;box-shadow:0 14px 35px rgba(0,0,0,.08)}h1{color:#151515;margin:0 0 10px;font-size:28px}p{line-height:1.5}.btn{display:inline-block;margin-top:14px;background:#151515;color:#fff;text-decoration:none;padding:12px 18px;border-radius:6px}</style>';
    echo '</head><body><main class="box"><h1>' . bm_escape($title) . '</h1><p>' . bm_escape($message) . '</p><a class="btn" href="../../cart.php">Volver al carrito</a></main></body></html>';
    exit;
}
