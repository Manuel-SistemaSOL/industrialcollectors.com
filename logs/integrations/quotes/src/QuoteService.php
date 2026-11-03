<?php

namespace IndustrialCollectors\Quotes;

class QuoteService
{
    private $config;
    private $validator;
    private $forwarder;
    private $notifier;
    private $logger;

    public function __construct(Config $config, QuotePayloadValidator $validator, QuoteForwarder $forwarder, QuoteEmailNotifier $notifier, SecureLogger $logger)
    {
        $this->config = $config;
        $this->validator = $validator;
        $this->forwarder = $forwarder;
        $this->notifier = $notifier;
        $this->logger = $logger;
    }

    public function create(array $payload): array
    {
        if (!$this->config->bool('enabled', true)) {
            throw new QuoteException('La tienda no esta habilitada para recibir cotizaciones.');
        }

        $validated = $this->validator->validateQuoteRequest($payload);
        $reference = 'IC-' . gmdate('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(4)));
        $apiData = $validated['apiData'];
        $apiData['comentario_cotizacion'] .= ' | Ref tienda: ' . $reference;

        $response = $this->forwarder->forwardQuote($apiData, $reference);
        $quoteNumber = $this->notifier->quoteNumberFromResponse($response);

        // Once SOL accepts the quote, mail failure must not cause a duplicate submission.
        try {
            $emailResult = $this->notifier->sendQuoteConfirmation([
                'email' => $apiData['correo_cliente'],
                'customer_name' => $apiData['nombre_cliente'],
                'reference_number' => $reference,
                'quote_number' => $quoteNumber,
                'payment_label' => $apiData['forma_pago'],
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
            ]);
        } catch (\Throwable $exception) {
            $this->logger->warning('Quote saved; customer email unavailable', ['reference' => $reference]);
            $emailResult = ['sent' => false, 'reason' => 'mail_failed'];
        }

        $this->logger->info('SOL quote created', [
            'reference' => $reference,
            'quote_number' => $quoteNumber,
            'email_sent' => $emailResult['sent'] ?? false,
        ]);

        $response['success'] = true;
        $response['reference_number'] = $reference;
        $response['email_sent'] = (bool) ($emailResult['sent'] ?? false);
        $response['email_status'] = (string) ($emailResult['reason'] ?? '');
        if ($quoteNumber !== '') {
            $response['idcotizacion'] = $quoteNumber;
            $response['numero_cotizacion'] = $quoteNumber;
        }

        return $response;
    }
}
