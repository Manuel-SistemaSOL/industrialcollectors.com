<?php

namespace BeautyMarket\CyberSource;

class OrderEmailNotifier
{
    private $config;
    private $logger;

    public function __construct(Config $config, SecureLogger $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function sendForSession(array $session, array $paymentData = [], array $orderResponse = []): array
    {
        $apiData = isset($session['api_payload']) && is_array($session['api_payload']) ? $session['api_payload'] : [];
        $formData = isset($session['form_data']) && is_array($session['form_data']) ? $session['form_data'] : [];

        return $this->sendOrderConfirmation([
            'email' => $this->firstNonEmpty([$apiData, $formData], ['correo_cliente', 'correo']),
            'customer_name' => $this->firstNonEmpty([$apiData, $formData], ['nombre_cliente', 'nombreCompleto']),
            'reference_number' => (string) ($session['reference_number'] ?? ''),
            'quote_number' => $this->quoteNumberFromResponse($orderResponse),
            'payment_label' => (string) ($apiData['forma_pago'] ?? 'Tarjeta de Credito/Debito'),
            'amount' => (float) ($session['amount'] ?? $apiData['total_venta'] ?? 0),
            'currency' => (string) ($session['currency'] ?? $this->config->get('currency', 'GTQ')),
            'authorization_number' => $this->firstNonEmpty([$paymentData], [
                'authorization_number',
                'no_auto_tarjeta',
                'authorization_code',
                'auth_code',
            ]),
        ]);
    }

    public function sendOrderConfirmation(array $order): array
    {
        if (!$this->config->bool('mail.enabled', true)) {
            return $this->result(false, true, 'mail_disabled', $order);
        }

        $to = trim((string) ($order['email'] ?? ''));
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return $this->result(false, true, 'invalid_or_missing_recipient', $order);
        }

        $reference = $this->identifier((string) ($order['reference_number'] ?? ''));
        $quote = $this->identifier((string) ($order['quote_number'] ?? ''));
        if ($reference === '' && $quote === '') {
            return $this->result(false, true, 'missing_reference', $order);
        }

        $subjectNumber = $quote !== '' ? $quote : $reference;
        $subject = 'La Broca Ferreterías - Confirmacion de pedido ' . $subjectNumber;
        $fromEmail = $this->configuredEmail('mail.from_email', 'labrocaferreterias01@gmail.com');
        $fromName = $this->headerText((string) $this->config->get('mail.from_name', 'La Broca Ferreterías'));
        $replyTo = $this->configuredEmail('mail.reply_to', $fromEmail);
        $boundary = 'BM-' . bin2hex(random_bytes(12));

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            'From: ' . $this->mailbox($fromName, $fromEmail),
            'Reply-To: ' . $replyTo,
            'X-Mailer: PHP/' . phpversion(),
        ];

        $body = $this->multipartBody(
            $boundary,
            $this->plainTextBody($order, $reference, $quote),
            $this->htmlBody($order, $reference, $quote)
        );

        $parameters = $this->mailParameters($fromEmail);
        if ($parameters !== '') {
            $sent = @mail($to, $subject, $body, implode("\r\n", $headers), $parameters);
        } else {
            $sent = @mail($to, $subject, $body, implode("\r\n", $headers));
        }

        $result = $this->result((bool) $sent, false, $sent ? 'sent' : 'mail_failed', $order);

        if ($sent) {
            $this->logger->info('Customer order email sent', [
                'email' => $to,
                'reference' => $reference,
                'quote_number' => $quote,
            ]);
        } else {
            $this->logger->warning('Customer order email failed', [
                'email' => $to,
                'reference' => $reference,
                'quote_number' => $quote,
            ]);
        }

        return $result;
    }

    public function quoteNumberFromResponse(array $response): string
    {
        foreach (['idcotizacion', 'numero_cotizacion', 'numeroCotizacion', 'id_cotizacion', 'cotizacion', 'quote_number'] as $key) {
            if (!array_key_exists($key, $response)) {
                continue;
            }

            $value = $this->identifier((string) $response[$key]);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function plainTextBody(array $order, string $reference, string $quote): string
    {
        $name = $this->displayText((string) ($order['customer_name'] ?? ''));
        $payment = $this->displayText((string) ($order['payment_label'] ?? ''));
        $amount = $this->formatAmount((float) ($order['amount'] ?? 0), (string) ($order['currency'] ?? 'GTQ'));
        $authorization = $this->identifier((string) ($order['authorization_number'] ?? ''));

        $lines = [];
        $lines[] = $name !== '' ? 'Hola ' . $name . ',' : 'Hola,';
        $lines[] = '';
        $lines[] = 'Gracias por comprar en La Broca Ferreterías. Conserva este correo como respaldo de tu pedido.';
        $lines[] = '';

        if ($quote !== '') {
            $lines[] = 'Numero de cotizacion: ' . $quote;
        }

        if ($reference !== '') {
            $lines[] = 'Referencia de pago: ' . $reference;
        }

        if ($payment !== '') {
            $lines[] = 'Metodo de pago: ' . $payment;
        }

        if ($amount !== '') {
            $lines[] = 'Total: ' . $amount;
        }

        if ($authorization !== '') {
            $lines[] = 'Autorizacion: ' . $authorization;
        }

        $lines[] = '';
        $lines[] = 'Para coordinar el seguimiento de tu pedido, comunicate por WhatsApp al +502 4314 3784.';
        $lines[] = '';
        $lines[] = 'La Broca Ferreterías';

        return implode("\r\n", $lines);
    }

    private function htmlBody(array $order, string $reference, string $quote): string
    {
        $name = $this->escape($this->displayText((string) ($order['customer_name'] ?? '')));
        $payment = $this->escape($this->displayText((string) ($order['payment_label'] ?? '')));
        $amount = $this->escape($this->formatAmount((float) ($order['amount'] ?? 0), (string) ($order['currency'] ?? 'GTQ')));
        $authorization = $this->escape($this->identifier((string) ($order['authorization_number'] ?? '')));
        $logoUrl = $this->escape(rtrim($this->config->baseUrl(), '/') . '/assets/img/LogoLaBroca.png');

        $rows = '';
        if ($quote !== '') {
            $rows .= $this->htmlRow('Numero de cotizacion', $quote);
        }

        if ($reference !== '') {
            $rows .= $this->htmlRow('Referencia de pago', $reference);
        }

        if ($payment !== '') {
            $rows .= $this->htmlRow('Metodo de pago', $payment);
        }

        if ($amount !== '') {
            $rows .= $this->htmlRow('Total', $amount);
        }

        if ($authorization !== '') {
            $rows .= $this->htmlRow('Autorizacion', $authorization);
        }

        $greeting = $name !== '' ? 'Hola ' . $name . ',' : 'Hola,';

        return '<!doctype html><html lang="es"><head><meta charset="utf-8"></head>'
            . '<body style="margin:0;background:#f6f6f3;font-family:Arial,sans-serif;color:#1f2430">'
            . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f6f3;padding:24px 12px">'
            . '<tr><td align="center">'
            . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border:1px solid #e8e5dc;border-radius:10px;overflow:hidden">'
            . '<tr><td style="padding:30px 28px;text-align:center">'
            . '<img src="' . $logoUrl . '" alt="La Broca Ferreterías" style="max-width:210px;height:auto;margin-bottom:24px">'
            . '<h1 style="margin:0 0 10px;color:#1a1a1a;font-size:28px;line-height:1.2">Confirmacion de pedido</h1>'
            . '<p style="margin:0 0 22px;color:#3f4654;font-size:16px;line-height:1.5">' . $greeting . '<br>Gracias por comprar en La Broca Ferreterías. Conserva este correo como respaldo.</p>'
            . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;text-align:left">' . $rows . '</table>'
            . '<p style="margin:24px 0 0;color:#3f4654;font-size:15px;line-height:1.5">Para coordinar el seguimiento de tu pedido, comunicate por WhatsApp al <strong>+502 4314 3784</strong>.</p>'
            . '</td></tr></table>'
            . '</td></tr></table></body></html>';
    }

    private function htmlRow(string $label, string $value): string
    {
        return '<tr>'
            . '<td style="padding:12px 14px;border-top:1px solid #f6f6f3;color:#626b7a;font-size:14px">' . $this->escape($label) . '</td>'
            . '<td style="padding:12px 14px;border-top:1px solid #f6f6f3;color:#151515;font-size:15px;font-weight:700;text-align:right">' . $this->escape($value) . '</td>'
            . '</tr>';
    }

    private function multipartBody(string $boundary, string $text, string $html): string
    {
        return '--' . $boundary . "\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n"
            . $text . "\r\n\r\n"
            . '--' . $boundary . "\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n"
            . $html . "\r\n\r\n"
            . '--' . $boundary . "--\r\n";
    }

    private function result(bool $sent, bool $skipped, string $reason, array $order): array
    {
        return [
            'sent' => $sent,
            'skipped' => $skipped,
            'reason' => $reason,
            'reference_number' => $this->identifier((string) ($order['reference_number'] ?? '')),
            'quote_number' => $this->identifier((string) ($order['quote_number'] ?? '')),
        ];
    }

    private function firstNonEmpty(array $sources, array $keys): string
    {
        foreach ($sources as $source) {
            if (!is_array($source)) {
                continue;
            }

            foreach ($keys as $key) {
                if (!array_key_exists($key, $source)) {
                    continue;
                }

                $value = trim((string) $source[$key]);
                if ($value !== '') {
                    return $value;
                }
            }
        }

        return '';
    }

    private function configuredEmail(string $key, string $fallback): string
    {
        $value = trim((string) $this->config->get($key, $fallback));
        return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : $fallback;
    }

    private function mailbox(string $name, string $email): string
    {
        return '"' . addcslashes($name, '"\\') . '" <' . $email . '>';
    }

    private function mailParameters(string $fromEmail): string
    {
        $envelope = $this->configuredEmail('mail.envelope_sender', $fromEmail);
        if ($envelope === '' || stripos(PHP_OS, 'WIN') === 0) {
            return '';
        }

        return '-f ' . escapeshellarg($envelope);
    }

    private function formatAmount(float $amount, string $currency): string
    {
        if ($amount <= 0) {
            return '';
        }

        $prefix = strtoupper($currency) === 'GTQ' ? 'Q' : strtoupper($currency) . ' ';
        return $prefix . number_format($amount, 2, '.', ',');
    }

    private function identifier(string $value): string
    {
        $value = trim(strip_tags($value));
        $value = preg_replace('/[^A-Za-z0-9._#-]/', '', $value) ?? '';

        return substr($value, 0, 80);
    }

    private function displayText(string $value): string
    {
        $value = trim(strip_tags($value));
        $value = preg_replace('/\s+/', ' ', $value) ?? '';

        return substr($value, 0, 160);
    }

    private function headerText(string $value): string
    {
        $value = $this->displayText($value);

        return $value !== '' ? $value : 'La Broca Ferreterías';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
