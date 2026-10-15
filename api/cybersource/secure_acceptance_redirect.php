<?php

declare(strict_types=1);

use BeautyMarket\CyberSource\GatewayException;

require __DIR__ . '/_bootstrap.php';

try {
    $reference = (string) ($_GET['reference'] ?? '');
    if ($reference === '') {
        throw new GatewayException('Referencia de pago no recibida.');
    }

    $session = $sessionStore->get($reference);
    if (!$session) {
        throw new GatewayException('Sesion de pago no encontrada.');
    }

    if (($session['status'] ?? '') === 'EXPIRED') {
        throw new GatewayException('La sesion de pago expiro. Genere el pedido nuevamente.');
    }

    $fields = $secureAcceptance->fieldsForSession($session);

    $sessionStore->update($reference, [
        'status' => 'REDIRECTED',
        'redirected_at' => gmdate('c'),
        'secure_acceptance' => [
            'transaction_uuid' => $fields['transaction_uuid'] ?? null,
            'endpoint' => $secureAcceptance->endpoint(),
        ],
    ]);

    $logger->info('Redirecting to Secure Acceptance', [
        'reference' => $reference,
        'amount' => $session['amount'] ?? null,
    ]);
} catch (\BeautyMarket\CyberSource\GatewayException $exception) {
    $logger->warning('Secure Acceptance redirect rejected', ['message' => $exception->getMessage()]);
    bm_error_page('Pago no disponible', $exception->getMessage(), 422);
} catch (Throwable $exception) {
    $logger->error('Secure Acceptance redirect failed', ['message' => $exception->getMessage()]);
    bm_error_page('Pago no disponible', 'No fue posible abrir el portal de pago.', 500);
}

?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Industrial Collectors, S.A.</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;display:grid;min-height:100vh;place-items:center;background:#fff}
        .logo{display:block;width:min(380px,78vw);height:auto}
        #secureAcceptanceForm{display:none}
    </style>
</head>
<body>
    <img class="logo" src="../../assets/img/logotipo%20IC/version%20horizontal/Logo-Industrial-Collectors-horizontal.png" alt="Industrial Collectors, S.A.">

    <form id="secureAcceptanceForm" method="post" action="<?php echo bm_escape($secureAcceptance->endpoint()); ?>">
        <?php foreach ($fields as $name => $value): ?>
            <input type="hidden" name="<?php echo bm_escape((string) $name); ?>" value="<?php echo bm_escape((string) $value); ?>">
        <?php endforeach; ?>
    </form>

    <script>
        window.setTimeout(function () {
            document.getElementById('secureAcceptanceForm').submit();
        }, 700);
    </script>
</body>
</html>
