<?php

namespace BeautyMarket\CyberSource;

class PaymentSessionStore
{
    private $path;
    private $ttlMinutes;

    public function __construct(string $path, int $ttlMinutes = 90)
    {
        $this->path = $path;
        $this->ttlMinutes = $ttlMinutes;

        if (!is_dir($this->path)) {
            mkdir($this->path, 0775, true);
        }
    }

    public function create(array $session): array
    {
        $session['created_at'] = gmdate('c');
        $session['updated_at'] = gmdate('c');
        $session['expires_at'] = gmdate('c', time() + ($this->ttlMinutes * 60));
        $session['status'] = $session['status'] ?? 'CREATED';
        $this->save($session['reference_number'], $session);

        return $session;
    }

    public function get(string $reference): ?array
    {
        $file = $this->file($reference);
        if (!is_file($file)) {
            return null;
        }

        $data = json_decode((string) file_get_contents($file), true);
        if (!is_array($data)) {
            return null;
        }

        if (!empty($data['expires_at']) && strtotime($data['expires_at']) < time()) {
            $data['status'] = 'EXPIRED';
            $this->save($reference, $data);
        }

        return $data;
    }

    public function update(string $reference, array $updates): array
    {
        $session = $this->get($reference);
        if (!$session) {
            throw new GatewayException('Sesion de pago no encontrada.');
        }

        $session = array_replace_recursive($session, $updates);
        $session['updated_at'] = gmdate('c');
        $this->save($reference, $session);

        return $session;
    }

    private function save(string $reference, array $session): void
    {
        file_put_contents($this->file($reference), json_encode($session, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    private function file(string $reference): string
    {
        $safe = preg_replace('/[^A-Za-z0-9_-]/', '', $reference);
        return rtrim($this->path, '/\\') . DIRECTORY_SEPARATOR . $safe . '.json';
    }
}
