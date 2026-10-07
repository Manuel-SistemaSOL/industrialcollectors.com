<?php

namespace BeautyMarket\CyberSource;

class SecureLogger
{
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
        $dir = dirname($this->path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }

    public function info(string $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->write('WARNING', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('ERROR', $message, $context);
    }

    private function write(string $level, string $message, array $context): void
    {
        $line = json_encode([
            'date' => gmdate('c'),
            'level' => $level,
            'message' => $message,
            'context' => SensitiveData::redact($context),
        ], JSON_UNESCAPED_SLASHES);

        file_put_contents($this->path, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
