<?php

namespace BeautyMarket\CyberSource;

class CyberSourceSignature
{
    public static function secureAcceptanceSignature(array $fields, string $secretKey): string
    {
        return base64_encode(hash_hmac('sha256', self::secureAcceptanceDataToSign($fields), $secretKey, true));
    }

    public static function secureAcceptanceDataToSign(array $fields): string
    {
        $signedFieldNames = explode(',', (string) ($fields['signed_field_names'] ?? ''));
        $pairs = [];

        foreach ($signedFieldNames as $name) {
            $name = trim($name);
            $pairs[] = $name . '=' . ($fields[$name] ?? '');
        }

        return implode(',', $pairs);
    }

    public static function verifySecureAcceptance(array $fields, string $secretKey): bool
    {
        if (empty($fields['signature']) || empty($fields['signed_field_names'])) {
            return false;
        }

        $expected = self::secureAcceptanceSignature($fields, $secretKey);

        return hash_equals($expected, (string) $fields['signature']);
    }
}
