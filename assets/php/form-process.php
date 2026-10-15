<?php

$errors = [];

function cleanContactValue($value, $maxLength)
{
    $value = trim(strip_tags((string) $value));
    $value = str_replace(["\r", "\n"], ' ', $value);
    $value = preg_replace('/\s+/', ' ', $value);

    return substr($value ?: '', 0, $maxLength);
}

function cleanContactMessage($value, $maxLength)
{
    $value = trim(strip_tags((string) $value));
    $value = str_replace(["\r\n", "\r"], "\n", $value);
    $value = preg_replace("/[ \t]+/", ' ', $value);

    return substr($value ?: '', 0, $maxLength);
}

function configuredEmail($key, $fallback)
{
    $value = trim((string) getenv($key));

    return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : $fallback;
}

function contactHeaderText($value, $maxLength = 160)
{
    $value = trim(strip_tags((string) $value));
    $value = str_replace(["\r", "\n"], ' ', $value);
    $value = preg_replace('/\s+/', ' ', $value);

    return substr($value ?: '', 0, $maxLength);
}

function contactMailbox($name, $email)
{
    return '"' . addcslashes(contactHeaderText($name), '"\\') . '" <' . $email . '>';
}

function contactMailParameters($fromEmail)
{
    if (stripos(PHP_OS, 'WIN') === 0) {
        return '';
    }

    $envelope = configuredEmail('CONTACT_MAIL_ENVELOPE_SENDER', $fromEmail);

    return $envelope !== '' ? '-f ' . escapeshellarg($envelope) : '';
}

function contactWantsJson()
{
    $accept = (string) ($_SERVER['HTTP_ACCEPT'] ?? '');
    $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));

    return stripos($accept, 'application/json') !== false || $requestedWith === 'xmlhttprequest';
}

function contactRespond($statusCode, array $payload, $plainMessage = '')
{
    http_response_code($statusCode);

    if (contactWantsJson()) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    header('Content-Type: text/plain; charset=UTF-8');
    echo $plainMessage !== '' ? $plainMessage : (string) ($payload['message'] ?? '');
    exit;
}

function contactLog($body, $status, array $context = [])
{
    $rootDir = dirname(__DIR__, 2);
    $logDir = $rootDir . '/logs';

    if (!is_dir($logDir) && !@mkdir($logDir, 0775, true)) {
        return;
    }

    if (!is_writable($logDir)) {
        return;
    }

    $entry = "==========================================\n";
    $entry .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
    $entry .= "Estado: " . $status . "\n";

    if ($context) {
        $entry .= "IP: " . ($context['ip'] ?? '') . "\n";
        $entry .= "Navegador: " . ($context['user_agent'] ?? '') . "\n";
    }

    $entry .= $body . "\n";
    $entry .= "==========================================\n\n";

    @file_put_contents($logDir . '/contact-form.log', $entry, FILE_APPEND | LOCK_EX);
}

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    contactRespond(405, [
        'success' => false,
        'message' => 'Metodo no permitido.',
    ], 'Metodo no permitido.');
}

if (cleanContactValue($_POST['website'] ?? '', 80) !== '') {
    contactRespond(200, [
        'success' => true,
        'message' => 'Mensaje recibido correctamente.',
        'mail_sent' => false,
        'stored' => false,
    ], 'success');
}

$name = cleanContactValue($_POST['name'] ?? '', 80);
$email = trim((string) ($_POST['email'] ?? ''));
$msgSubject = contactHeaderText($_POST['msg_subject'] ?? '', 120);
$phoneNumber = preg_replace('/[^\d+\-\s()]/', '', (string) ($_POST['phone_number'] ?? ''));
$phoneNumber = cleanContactValue($phoneNumber, 30);
$message = cleanContactMessage($_POST['message'] ?? '', 3000);

if ($name === '') {
    $errors[] = 'El nombre es requerido.';
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Ingrese un email valido.';
}

if ($phoneNumber === '') {
    $errors[] = 'El telefono es requerido.';
}

if ($msgSubject === '') {
    $errors[] = 'El asunto es requerido.';
}

if ($message === '') {
    $errors[] = 'El mensaje es requerido.';
}

if ($errors) {
    contactRespond(422, [
        'success' => false,
        'message' => implode(' ', $errors),
        'errors' => $errors,
    ], implode(' ', $errors));
}

$mailTo = configuredEmail('CONTACT_MAIL_TO', 'industrialcollectorsventas@gmail.com');
$fromEmail = configuredEmail('CONTACT_MAIL_FROM', 'industrialcollectorsventas@gmail.com');
$replyTo = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : configuredEmail('CONTACT_MAIL_REPLY_TO', $fromEmail);
$subject = 'Nuevo mensaje de contacto - ' . $msgSubject;
$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

$body = "";
$body .= "Nuevo mensaje recibido desde el formulario de contacto\n\n";
$body .= "Nombre: " . $name . "\n";
$body .= "Email: " . $email . "\n";
$body .= "Telefono: " . $phoneNumber . "\n";
$body .= "Asunto: " . $msgSubject . "\n\n";
$body .= "Mensaje:\n" . $message . "\n\n";
$body .= "Este mensaje fue enviado desde industrialcollectors.com.";

$headers = [];
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/plain; charset=UTF-8';
$headers[] = 'From: ' . contactMailbox('Industrial Collectors, S.A.', $fromEmail);
$headers[] = 'Reply-To: ' . $replyTo;
$headers[] = 'X-Mailer: PHP/' . phpversion();

$parameters = contactMailParameters($fromEmail);
if ($parameters !== '') {
    $sent = @mail($mailTo, $encodedSubject, $body, implode("\r\n", $headers), $parameters);
} else {
    $sent = @mail($mailTo, $encodedSubject, $body, implode("\r\n", $headers));
}

$logContext = [
    'ip' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
    'user_agent' => contactHeaderText($_SERVER['HTTP_USER_AGENT'] ?? '', 220),
];

contactLog($body, $sent ? 'sent' : 'mail_failed_saved', $logContext);

if ($sent) {
    contactRespond(200, [
        'success' => true,
        'message' => 'Mensaje enviado correctamente. Nos pondremos en contacto contigo pronto.',
        'mail_sent' => true,
        'stored' => true,
    ], 'success');
}

contactRespond(202, [
    'success' => true,
    'message' => 'Mensaje recibido correctamente. Nos pondremos en contacto contigo pronto.',
    'mail_sent' => false,
    'stored' => true,
], 'success');
