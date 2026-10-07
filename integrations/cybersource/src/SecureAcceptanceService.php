<?php

namespace BeautyMarket\CyberSource;

class SecureAcceptanceService
{
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_DECLINED = 'DECLINED';
    public const STATUS_CANCELLED = 'CANCELLED';
    public const STATUS_PENDING = 'PENDING';

    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function assertConfigured(): void
    {
        $this->config->requireValue('secure_acceptance.profile_id');
        $this->config->requireValue('secure_acceptance.access_key');
        $this->config->requireValue('secure_acceptance.secret_key');
    }

    public function endpoint(): string
    {
        return $this->config->secureAcceptanceEndpoint();
    }

    public function fieldsForSession(array $session): array
    {
        $this->assertConfigured();

        $formData = $session['form_data'] ?? [];
        $names = $this->splitName((string) ($formData['nombreCompleto'] ?? 'Cliente'));
        $transactionType = $this->config->get('operation') === 'authorization' ? 'authorization' : 'sale';
        $email = trim((string) ($formData['correo'] ?? ''));
        if ($email === '') {
            $email = 'cliente@labrocaferreterias.com';
        }

        $fields = [
            'access_key' => $this->config->requireValue('secure_acceptance.access_key'),
            'profile_id' => $this->config->requireValue('secure_acceptance.profile_id'),
            'transaction_uuid' => bin2hex(random_bytes(16)),
            'signed_date_time' => gmdate('Y-m-d\TH:i:s\Z'),
            'locale' => (string) $this->config->get('secure_acceptance.locale', 'en-us'),
            'transaction_type' => $transactionType,
            'payment_method' => 'card',
            'reference_number' => (string) $session['reference_number'],
            'amount' => number_format((float) $session['amount'], 2, '.', ''),
            'currency' => strtoupper((string) ($session['currency'] ?? $this->config->get('currency', 'GTQ'))),
            'bill_to_forename' => $names['first'],
            'bill_to_surname' => $names['last'],
            'bill_to_email' => $email,
            'bill_to_phone' => preg_replace('/\D+/', '', (string) ($formData['telefono'] ?? '')),
            'bill_to_address_line1' => (string) ($formData['direccion'] ?? 'Guatemala'),
            'bill_to_address_city' => 'Guatemala',
            'bill_to_address_state' => 'GU',
            'bill_to_address_postal_code' => '01001',
            'bill_to_address_country' => 'GT',
            'override_custom_receipt_page' => $this->config->callbackUrl('site.return_path'),
            'override_custom_cancel_page' => $this->config->callbackUrl('site.cancel_path'),
        ];

        $fields['signed_field_names'] = implode(',', array_keys($fields)) . ',signed_field_names,unsigned_field_names';
        $fields['unsigned_field_names'] = '';
        $this->assertValidSignedFields($fields);
        $fields['signature'] = CyberSourceSignature::secureAcceptanceSignature($fields, $this->signingKey());

        return $fields;
    }

    public function verifyResponse(array $fields): bool
    {
        return CyberSourceSignature::verifySecureAcceptance($fields, $this->signingKey());
    }

    public function isApproved(array $fields): bool
    {
        return $this->paymentStatus($fields) === self::STATUS_APPROVED;
    }

    public function paymentStatus(array $fields): string
    {
        $decision = strtoupper(trim((string) ($fields['decision'] ?? '')));
        $reasonCode = strtoupper(trim((string) ($fields['reason_code'] ?? $fields['auth_response'] ?? '')));

        if (in_array($decision, ['CANCEL', 'CANCELLED', 'CANCELED'], true)) {
            return self::STATUS_CANCELLED;
        }

        if (in_array($decision, ['REVIEW', 'PENDING'], true)) {
            return self::STATUS_PENDING;
        }

        if (in_array($decision, ['DECLINE', 'DECLINED', 'REJECT', 'REJECTED'], true)) {
            return self::STATUS_DECLINED;
        }

        if (in_array($decision, ['ACCEPT', 'AUTHORIZED'], true)
            || ($decision === '' && in_array($reasonCode, ['100', '00'], true))) {
            return self::STATUS_APPROVED;
        }

        return self::STATUS_PENDING;
    }

    public function sessionStatusForPaymentStatus(string $paymentStatus): string
    {
        switch ($paymentStatus) {
            case self::STATUS_APPROVED:
                return 'PAID';
            case self::STATUS_CANCELLED:
                return 'CANCELLED';
            case self::STATUS_DECLINED:
                return 'DECLINED';
            case self::STATUS_PENDING:
            default:
                return 'AWAITING_CONFIRMATION';
        }
    }

    public function authorizationNumber(array $fields): string
    {
        return $this->firstNonEmpty($fields, [
            'auth_code',
            'authorization_code',
            'authorization_number',
            'no_auto_tarjeta',
        ]);
    }

    public function paymentSummary(array $fields): array
    {
        $authorizationNumber = $this->authorizationNumber($fields);
        $transactionId = $this->firstNonEmpty($fields, [
            'transaction_id',
            'request_id',
            'auth_trans_ref_no',
            'id',
        ]);

        return [
            'gateway_status' => $this->paymentStatus($fields),
            'authorization_number' => $authorizationNumber,
            'no_auto_tarjeta' => $authorizationNumber,
            'transaction_id' => $transactionId,
            'request_id' => $fields['request_id'] ?? null,
            'decision' => $fields['decision'] ?? null,
            'reason_code' => $fields['reason_code'] ?? null,
        ];
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $first = array_shift($parts) ?: 'Cliente';
        $last = implode(' ', $parts) ?: 'La Broca';

        return ['first' => $first, 'last' => $last];
    }

    private function signingKey(): string
    {
        return preg_replace('/\s+/', '', $this->config->requireValue('secure_acceptance.secret_key')) ?? '';
    }

    private function firstNonEmpty(array $fields, array $keys): string
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $fields)) {
                continue;
            }

            $value = trim((string) $fields[$key]);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function assertValidSignedFields(array $fields): void
    {
        $signedFields = $this->csvToList((string) ($fields['signed_field_names'] ?? ''));

        if (in_array('signature', $signedFields, true)) {
            throw new GatewayException('Configuracion de firma invalida: signature no debe firmarse.');
        }

        foreach ($signedFields as $name) {
            if (!array_key_exists($name, $fields)) {
                throw new GatewayException("Campo firmado faltante: {$name}");
            }
        }
    }

    private function csvToList(string $value): array
    {
        $items = array_map('trim', explode(',', $value));

        return array_values(array_filter($items, static function (string $item): bool {
            return $item !== '';
        }));
    }

}
