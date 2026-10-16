<?php

declare(strict_types=1);

use IndustrialCollectors\Quotes\Config;
use IndustrialCollectors\Quotes\QuoteEmailNotifier;
use IndustrialCollectors\Quotes\QuoteException;
use IndustrialCollectors\Quotes\QuoteForwarder;
use IndustrialCollectors\Quotes\QuotePayloadValidator;
use IndustrialCollectors\Quotes\QuoteService;
use IndustrialCollectors\Quotes\SecureLogger;

require __DIR__ . '/quote_order_payload_test.php';

$testLogPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'industrial-quotes-' . bin2hex(random_bytes(8)) . '.log';
$blockedLogParent = tempnam(sys_get_temp_dir(), 'quote-log-blocked-');
if ($blockedLogParent === false) {
    throw new RuntimeException('No se pudo preparar la prueba de logging.');
}
$logger = new SecureLogger($testLogPath);
$configValues = [
    'enabled' => true,
    'currency' => 'GTQ',
    'site' => ['base_url' => 'https://example.invalid'],
    'orders' => ['external_api_url' => 'https://example.invalid/sol-quotes', 'destination' => 'VENTA'],
    'mail' => ['enabled' => true, 'from_email' => 'ventas@example.invalid'],
];
$config = new Config($configValues);
$httpCalls = [];
$mailCalls = [];

try {
    $http = function (string $url, string $body) use (&$httpCalls): array {
        $httpCalls[] = ['url' => $url, 'payload' => json_decode($body, true)];
        return ['status' => 200, 'body' => '{"success":true,"idcotizacion":"SOL-123","message":"Recibida"}', 'error' => ''];
    };
    $mail = function ($to, $subject, $body, $headers, $parameters) use (&$mailCalls): bool {
        $mailCalls[] = compact('to', 'subject', 'body', 'headers', 'parameters');
        return true;
    };
    $notifier = new QuoteEmailNotifier($config, $logger, $mail);
    $service = new QuoteService($config, new QuotePayloadValidator($config), new QuoteForwarder($config, $logger, $http), $notifier, $logger);

    foreach (['transferencia', 'efectivo', 'tarjeta'] as $method) {
        $response = $service->create(quoteTestPayload($method));
        assertQuoteValue(true, $response['success'], 'Debe confirmar la cotizacion aceptada por SOL.');
        assertQuoteValue('SOL-123', $response['idcotizacion'], 'Debe devolver el numero real de SOL.');
        assertQuoteValue('SOL-123', $response['numero_cotizacion'], 'Debe mantener los aliases de respuesta existentes.');
        assertQuoteValue(1, preg_match('/^IC-\d{14}-[A-F0-9]{8}$/', $response['reference_number']), 'Debe generar una referencia generica de cotizacion.');
        assertQuoteValue(true, $response['email_sent'], 'Debe conservar el estado del correo.');
        $last = $httpCalls[count($httpCalls) - 1];
        assertQuoteValue('https://example.invalid/sol-quotes', $last['url'], 'Debe usar el endpoint configurado, sin otras llamadas.');
        assertQuoteValue(0, $last['payload']['valor_tarjeta'], 'No debe registrar cargos a tarjeta en SOL.');
        assertQuoteValue('', $last['payload']['no_auto_tarjeta'], 'No debe registrar autorizaciones de cobro.');
        assertQuoteValue(true, strpos($last['payload']['comentario_cotizacion'], $response['reference_number']) !== false, 'SOL debe recibir la referencia de cotizacion.');
    }
    assertQuoteValue(3, count($httpCalls), 'Debe enviar una sola solicitud SOL por cotizacion.');
    assertQuoteValue(3, count($mailCalls), 'Debe enviar solo el correo de cada cotizacion aceptada.');
    assertQuoteValue(true, strpos($mailCalls[2]['subject'], 'Cotizacion recibida SOL-123') !== false, 'El asunto debe describir una cotizacion, no un cobro.');
    assertQuoteValue(true, strpos($mailCalls[2]['body'], 'No se ha realizado ningun cobro en linea') !== false, 'El correo debe aclarar que no hubo cobro.');
    assertQuoteValue(true, strpos($mailCalls[2]['body'], 'https://wa.me/50230888818') !== false, 'El correo debe usar el WhatsApp proporcionado.');
    assertQuoteValue(false, strpos($mailCalls[2]['body'], 'Autorizacion:') !== false, 'El correo no debe mostrar una autorizacion de pago.');

    $invalid = quoteTestPayload('tarjeta');
    $invalid['formData']['total'] = 1;
    assertQuoteRejected(function () use ($service, $invalid) { $service->create($invalid); }, 'Debe rechazar la solicitud antes de enviar a SOL.');
    assertQuoteValue(3, count($httpCalls), 'No debe enviar una cotizacion invalida.');
    assertQuoteValue(3, count($mailCalls), 'No debe enviar correos por solicitudes invalidas.');

    foreach ([
        ['status' => 200, 'body' => '{"success":false,"message":"SOL rechazo la solicitud"}', 'error' => ''],
        ['status' => 500, 'body' => '{"success":true}', 'error' => ''],
        ['status' => 200, 'body' => '<html>Error</html>', 'error' => ''],
        ['status' => 0, 'body' => false, 'error' => 'timeout'],
    ] as $failedResponse) {
        $failedHttp = function () use ($failedResponse): array { return $failedResponse; };
        $failedService = new QuoteService($config, new QuotePayloadValidator($config), new QuoteForwarder($config, $logger, $failedHttp), $notifier, $logger);
        assertQuoteRejected(function () use ($failedService) { $failedService->create(quoteTestPayload('efectivo')); }, 'Un error de SOL no puede mostrarse como cotizacion confirmada.');
    }
    assertQuoteValue(3, count($mailCalls), 'No debe enviar correo cuando SOL no ha aceptado la cotizacion.');

    $failingMail = new QuoteEmailNotifier($config, $logger, function () { throw new RuntimeException('Mailer unavailable'); });
    $savedService = new QuoteService($config, new QuotePayloadValidator($config), new QuoteForwarder($config, $logger, $http), $failingMail, $logger);
    $saved = $savedService->create(quoteTestPayload('efectivo'));
    assertQuoteValue(true, $saved['success'], 'Una falla de correo no debe convertir una cotizacion guardada en error.');
    assertQuoteValue(false, $saved['email_sent'], 'Debe informar el fallo de correo.');
    assertQuoteValue('mail_failed', $saved['email_status'], 'Debe conservar un estado de correo util.');
    assertQuoteValue(4, count($httpCalls), 'No debe reintentar ni duplicar una cotizacion por falla de correo.');

    $disabledValues = $configValues;
    $disabledValues['enabled'] = false;
    $disabledConfig = new Config($disabledValues);
    $disabledService = new QuoteService($disabledConfig, new QuotePayloadValidator($disabledConfig), new QuoteForwarder($disabledConfig, $logger, $http), $notifier, $logger);
    assertQuoteRejected(function () use ($disabledService) { $disabledService->create(quoteTestPayload('transferencia')); }, 'Debe respetar la deshabilitacion de solicitudes.');
    assertQuoteValue(4, count($httpCalls), 'Una tienda deshabilitada no debe contactar SOL.');

    $disabledMailValues = $configValues;
    $disabledMailValues['mail']['enabled'] = false;
    $disabledMail = new QuoteEmailNotifier(new Config($disabledMailValues), $logger, $mail);
    $skip = $disabledMail->sendQuoteConfirmation(['email' => 'cliente@example.invalid', 'reference_number' => 'IC-TEST']);
    assertQuoteValue('mail_disabled', $skip['reason'], 'Debe respetar la configuracion de correo sin enviar.');
    assertQuoteValue(3, count($mailCalls), 'El correo deshabilitado no debe llamar el transporte.');

    foreach ([$blockedLogParent . DIRECTORY_SEPARATOR . 'quotes.log', sys_get_temp_dir()] as $unwritableLogPath) {
        ob_start();
        set_error_handler(function ($severity, $message, $file, $line) {
            throw new ErrorException($message, 0, $severity, $file, $line);
        });
        try {
            $unavailableLogger = new SecureLogger($unwritableLogPath);
            $unavailableLogger->warning('Logger destination unavailable');
            $serviceWithoutLog = new QuoteService(
                $config,
                new QuotePayloadValidator($config),
                new QuoteForwarder($config, $unavailableLogger, $http),
                $disabledMail,
                $unavailableLogger
            );
            $savedWithoutLog = $serviceWithoutLog->create(quoteTestPayload('efectivo'));
        } finally {
            restore_error_handler();
            $unexpectedOutput = ob_get_clean();
        }
        assertQuoteValue(true, $savedWithoutLog['success'], 'Un error de escritura del log no debe fallar una cotizacion registrada.');
        assertQuoteValue('SOL-123', $savedWithoutLog['idcotizacion'], 'Debe conservar el numero SOL aunque el log no funcione.');
        assertQuoteValue('', $unexpectedOutput, 'Un error del log no debe imprimir warnings en la respuesta JSON.');
    }

    $log = (string) file_get_contents($testLogPath);
    assertQuoteValue(false, strpos($log, 'cliente@example.invalid') !== false, 'No debe escribir datos personales del cliente en logs.');
    echo "OK: flujo SOL y correo con mocks, errores y ausencia de cobros. Ningun envio real.\n";
} finally {
    if (is_file($testLogPath)) {
        unlink($testLogPath);
    }
    if (is_file($blockedLogParent)) {
        unlink($blockedLogParent);
    }
}
