<?php

namespace BeautyMarket\CyberSource;

class HostedCheckoutService
{
    private $config;
    private $secureAcceptance;

    public function __construct(Config $config, SecureAcceptanceService $secureAcceptance)
    {
        $this->config = $config;
        $this->secureAcceptance = $secureAcceptance;
    }

    public function createCheckoutUrl(string $reference): string
    {
        return $this->config->baseUrl()
            . '/api/cybersource/secure_acceptance_redirect.php?reference='
            . rawurlencode($reference);
    }

    public function validateReturn(array $fields): bool
    {
        return $this->secureAcceptance->verifyResponse($fields);
    }
}
