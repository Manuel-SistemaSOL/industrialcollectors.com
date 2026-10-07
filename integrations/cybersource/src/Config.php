<?php

namespace BeautyMarket\CyberSource;

class Config
{
    private $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public static function fromFile(string $path): self
    {
        if (!is_file($path)) {
            throw new GatewayException('No se encontro la configuracion de CyberSource.');
        }

        $values = require $path;
        if (!is_array($values)) {
            throw new GatewayException('La configuracion de CyberSource no es valida.');
        }

        return new self($values);
    }

    public function get(string $key, $default = null)
    {
        $segments = explode('.', $key);
        $value = $this->values;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    public function bool(string $key, bool $default = false): bool
    {
        return filter_var($this->get($key, $default), FILTER_VALIDATE_BOOLEAN);
    }

    public function requireValue(string $key): string
    {
        $value = trim((string) $this->get($key, ''));
        if ($value === '') {
            throw new GatewayException("Configuracion pendiente: {$key}");
        }

        return $value;
    }

    public function baseUrl(): string
    {
        $configured = trim((string) $this->get('site.base_url', ''));
        if ($configured !== '') {
            return rtrim($configured, '/');
        }

        if (PHP_SAPI !== 'cli' && !empty($_SERVER['HTTP_HOST'])) {
            $host = (string) $_SERVER['HTTP_HOST'];
            $proto = $_SERVER['HTTP_X_FORWARDED_PROTO']
                ?? ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
            $proto = strtolower((string) $proto) === 'https' ? 'https' : 'http';
            $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
            $apiPos = strpos($scriptName, '/api/');
            $basePath = $apiPos === false ? '' : substr($scriptName, 0, $apiPos);

            return rtrim($proto . '://' . $host . $basePath, '/');
        }

        return 'https://labrocaferreterias.com';
    }

    public function callbackUrl(string $pathKey): string
    {
        return $this->baseUrl() . '/' . ltrim((string) $this->get($pathKey, ''), '/');
    }

    public function secureAcceptanceEndpoint(): string
    {
        return $this->get('environment') === 'production'
            ? (string) $this->get('secure_acceptance.production_endpoint')
            : (string) $this->get('secure_acceptance.test_endpoint');
    }
}
