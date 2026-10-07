<?php

declare(strict_types=1);

use BeautyMarket\CyberSource\GatewayException;

require __DIR__ . '/_bootstrap.php';

function bm_payment_page(string $title, string $message, string $reference, string $statusClass = 'ok', string $action = 'home')
{
    header('Content-Type: text/html; charset=utf-8');
    $isWarn = $statusClass === 'warn';
    $color = $statusClass === 'ok' ? '#151515' : ($isWarn ? '#9b4b1f' : '#555');
    $buttonText = $action === 'approved' ? 'Confirmar' : ($action === 'cart' ? 'Volver al carrito' : 'Volver al inicio');
    $buttonHref = $action === 'cart' ? '../../cart.php' : '../../index.php';
    $eventKey = 'beautymarket_neonet_payment_approved';
    $safeReference = bm_escape($reference);
    $jsReference = json_encode($reference, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $jsAction = json_encode($action, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

    echo '<!doctype html><html lang="es"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . bm_escape($title) . '</title>';
    echo '<style>';
    echo ':root{--brand:#151515;--brand-dark:#242424;--soft:#f6f6f3;--line:#e8e5dc}';
    echo '*{box-sizing:border-box}body{font-family:Arial,sans-serif;background:linear-gradient(135deg,#ffffff 0%,#fff7cc 100%);color:#1f2430;margin:0;display:grid;min-height:100vh;place-items:center;padding:24px}';
    echo '.box{width:min(92vw,720px);background:#fff;border:1px solid var(--line);border-radius:10px;padding:36px 32px;box-shadow:0 24px 60px rgba(38,55,199,.16);text-align:center}';
    echo '.logo{display:block;width:min(320px,78vw);height:auto;margin:0 auto 30px}';
    echo 'h1{color:' . $color . ';margin:0 0 12px;font-size:34px;line-height:1.15}p{line-height:1.55;margin:0 auto;color:#3f4654;font-size:17px;max-width:560px}';
    echo '.ref{display:inline-flex;align-items:center;gap:8px;background:#fff7cc;color:#151515;border-radius:8px;padding:12px 16px;margin:24px 0 6px;font-weight:700}.actions{margin-top:24px}.btn{display:inline-block;background:var(--brand);color:#fff;text-decoration:none;padding:13px 26px;border-radius:7px;font-weight:700;border:0;cursor:pointer;font-size:16px}.btn:hover{background:var(--brand-dark)}';
    echo '@media(max-width:520px){.box{padding:30px 20px}h1{font-size:28px}.ref{width:100%;justify-content:center;word-break:break-word}}';
    echo '</style></head><body><main class="box">';
    echo '<img class="logo" src="../../assets/img/LogoLaBroca.png" alt="La Broca Ferreterías">';
    echo '<h1>' . bm_escape($title) . '</h1><p>' . bm_escape($message) . '</p>';
    if ($reference !== '') {
        echo '<div class="ref">Referencia: <span>' . $safeReference . '</span></div>';
    }
    echo '<div class="actions"><a class="btn" id="paymentAction" href="' . bm_escape($buttonHref) . '">' . bm_escape($buttonText) . '</a></div>';
    echo '</main><script>';
    echo 'var paymentAction=' . $jsAction . ';';
    echo 'var paymentReference=' . $jsReference . ';';
    echo 'var eventKey="' . $eventKey . '";';
    echo 'function clearLocalCart(){try{["compusisgt_carrito","compusisgt_carrito_timestamp","compusisgt_carrito_hash"].forEach(function(key){sessionStorage.removeItem(key);});}catch(error){}}';
    echo 'function closePaymentWindow(){try{window.close();}catch(error){}window.setTimeout(function(){if(!window.closed){var button=document.getElementById("paymentAction");if(button){button.textContent="Puede cerrar esta ventana";}}},700);}';
    echo 'function confirmPayment(event){if(paymentAction!=="approved"){return;}event.preventDefault();var button=document.getElementById("paymentAction");if(button){button.textContent="Confirmado";button.style.pointerEvents="none";button.setAttribute("aria-disabled","true");}var payload={status:"approved",reference:paymentReference,at:new Date().toISOString()};try{localStorage.setItem(eventKey,JSON.stringify(payload));}catch(error){}try{if(window.opener&&!window.opener.closed){window.opener.postMessage({type:"beautymarket:neonet-approved",reference:paymentReference},window.location.origin);window.opener.focus();}}catch(error){}clearLocalCart();window.setTimeout(closePaymentWindow,180);}';
    echo 'document.getElementById("paymentAction").addEventListener("click",confirmPayment);';
    echo '</script></body></html>';
    exit;
}

function bm_send_customer_order_email_once($sessionStore, $orderEmailNotifier, $logger, string $reference, array $session, array $paymentSummary = [], array $orderResponse = []): array
{
    if (!empty($session['customer_email_sent_at'])) {
        return $session;
    }

    $emailResult = $orderEmailNotifier->sendForSession($session, $paymentSummary, $orderResponse);
    $updates = [
        'customer_email_last_attempt_at' => gmdate('c'),
        'customer_email_result' => $emailResult,
    ];

    if (!empty($emailResult['sent'])) {
        $updates['customer_email_sent_at'] = gmdate('c');
    }

    try {
        return $sessionStore->update($reference, $updates);
    } catch (\Throwable $exception) {
        $logger->warning('Customer order email result could not be stored', [
            'reference' => $reference,
            'message' => $exception->getMessage(),
        ]);

        return $session;
    }
}

try {
    $response = $_POST ?: $_GET;
    if (!$response) {
        throw new GatewayException('No se recibio respuesta de NeoNet.');
    }

    if (!$hostedCheckout->validateReturn($response)) {
        throw new GatewayException('La firma de NeoNet no es valida.');
    }

    $reference = bm_reference_from_response($response);
    if ($reference === '') {
        throw new GatewayException('NeoNet no retorno referencia de pedido.');
    }

    $session = $sessionStore->get($reference);
    if (!$session) {
        throw new GatewayException('Sesion de pago no encontrada.');
    }

    $validator->validateReturnAmount($session, $response);

    $storedResponse = $response;
    unset($storedResponse['signature']);
    $paymentStatus = $secureAcceptance->paymentStatus($response);
    $paymentSummary = $secureAcceptance->paymentSummary($response);
    $authorizationNumber = $paymentSummary['authorization_number'];
    $approved = $secureAcceptance->isApproved($response);

    if ($approved) {
        $forwardResponse = [];
        $session = $sessionStore->update($reference, [
            'status' => 'PAID',
            'paid_at' => gmdate('c'),
            'payment_response' => $storedResponse,
            'payment' => $paymentSummary,
        ]);

        if (empty($session['order_forwarded_at'])) {
            try {
                $forwardResponse = $orderForwarder->forward($session, $paymentSummary);
                $session = $sessionStore->update($reference, [
                    'status' => 'PAID_ORDER_CREATED',
                    'order_forwarded_at' => gmdate('c'),
                    'order_forward_response' => $forwardResponse,
                ]);
            } catch (\BeautyMarket\CyberSource\GatewayException $exception) {
                $logger->error('Approved payment order forwarding failed', [
                    'reference' => $reference,
                    'message' => $exception->getMessage(),
                ]);
                $session = $sessionStore->update($reference, [
                    'status' => 'PAID_ORDER_FORWARD_FAILED',
                    'order_forward_error' => $exception->getMessage(),
                ]);
                $session = bm_send_customer_order_email_once(
                    $sessionStore,
                    $orderEmailNotifier,
                    $logger,
                    $reference,
                    $session,
                    $paymentSummary,
                    $forwardResponse
                );

                bm_payment_page(
                    'Pago aprobado',
                    'El pago fue aprobado, pero el pedido quedo pendiente de registrarse en la API externa. Comparta esta referencia con administracion.',
                    $reference,
                    'warn',
                    'approved'
                );
            }
        } elseif (!empty($session['order_forward_response']) && is_array($session['order_forward_response'])) {
            $forwardResponse = $session['order_forward_response'];
        }

        $session = bm_send_customer_order_email_once(
            $sessionStore,
            $orderEmailNotifier,
            $logger,
            $reference,
            $session,
            $paymentSummary,
            $forwardResponse
        );

        $logger->info('Payment approved', [
            'reference' => $reference,
            'authorization_number' => $authorizationNumber,
            'transaction_id' => $paymentSummary['transaction_id'],
        ]);
        bm_payment_page('Pago aprobado', 'Tu pago fue aprobado. Para coordinar el seguimiento de tu pedido, comunicate por WhatsApp al +502 4314 3784.', $reference, 'ok', 'approved');
    }

    $status = $secureAcceptance->sessionStatusForPaymentStatus($paymentStatus);
    $sessionStore->update($reference, [
        'status' => $status,
        'payment_response' => $storedResponse,
        'payment' => $paymentSummary,
    ]);

    $logger->warning('Payment not approved', [
        'reference' => $reference,
        'status' => $status,
        'gateway_status' => $paymentStatus,
        'decision' => $response['decision'] ?? null,
        'reason_code' => $response['reason_code'] ?? null,
    ]);

    if ($status === 'CANCELLED') {
        bm_payment_page('Pago cancelado', 'El pago fue cancelado en NeoNet. El pedido no fue enviado a cobro.', $reference, 'warn', 'cart');
    }

    if ($status === 'AWAITING_CONFIRMATION') {
        bm_payment_page('Pago en validacion', 'NeoNet dejo el pago pendiente de validacion. El pedido no fue enviado a cobro automatico.', $reference, 'warn', 'cart');
    }

    bm_payment_page('Pago rechazado', 'NeoNet no autorizo el pago. El pedido no fue enviado a cobro.', $reference, 'warn', 'cart');
} catch (\BeautyMarket\CyberSource\GatewayException $exception) {
    $logger->warning('Payment return rejected', ['message' => $exception->getMessage()]);
    bm_payment_page('Respuesta de pago no valida', $exception->getMessage(), '', 'warn', 'cart');
} catch (Throwable $exception) {
    $logger->error('Payment return failed', ['message' => $exception->getMessage()]);
    bm_payment_page('Error de pago', 'No fue posible procesar la respuesta de NeoNet.', '', 'warn', 'cart');
}
