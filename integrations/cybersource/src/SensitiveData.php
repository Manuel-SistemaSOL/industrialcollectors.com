<?php

namespace BeautyMarket\CyberSource;

class SensitiveData
{
    private const SENSITIVE_KEYS = [
        'access_key',
        'address',
        'admin_token',
        'card',
        'cvv',
        'email',
        'forename',
        'key_id',
        'merchant_id',
        'number',
        'payment_token',
        'phone',
        'secret',
        'secret_key',
        'securityCode',
        'signature',
        'surname',
        'x_internal_token',
    ];

    public static function redact($value)
    {
        if (is_array($value)) {
            $redacted = [];
            foreach ($value as $key => $item) {
                $redacted[$key] = self::isSensitiveKey((string) $key)
                    ? '[REDACTED]'
                    : self::redact($item);
            }
            return $redacted;
        }

        if (is_string($value) && strlen($value) > 120) {
            return substr($value, 0, 60) . '...[TRUNCATED]';
        }

        return $value;
    }

    private static function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower(str_replace(['-', '_'], '', $key));

        foreach (self::SENSITIVE_KEYS as $sensitiveKey) {
            $needle = strtolower(str_replace(['-', '_'], '', $sensitiveKey));
            if (strpos($normalized, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}
