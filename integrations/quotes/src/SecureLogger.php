<?php

namespace IndustrialCollectors\Quotes;

class SecureLogger
{
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
        try {
            $dir = dirname($this->path);
            if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
                $this->path = '';
            }
        } catch (\Throwable $exception) {
            $this->path = '';
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
        if ($this->path === '') {
            return;
        }

        // Logging must never change the result of a quote already accepted by SOL.
        try {
            $line = json_encode([
                'date' => gmdate('c'),
                'level' => $level,
                'message' => $message,
                'context' => SensitiveData::redact($context),
            ], JSON_UNESCAPED_SLASHES);

            if ($line !== false) {
                @file_put_contents($this->path, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
            }
        } catch (\Throwable $exception) {
            // Keep the API response valid even when its log destination is unavailable.
        }
    }
}
