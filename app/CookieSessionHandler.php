<?php

class CookieSessionHandler implements SessionHandlerInterface
{
    private const COOKIE = 'etab_sess';

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $raw = $_COOKIE[self::COOKIE] ?? '';
        if ($raw === '' || !str_contains($raw, '.')) {
            return '';
        }
        [$payload, $mac] = explode('.', $raw, 2);
        $calc = hash_hmac('sha256', $payload, self::secret());
        if (!hash_equals($calc, $mac)) {
            return '';
        }
        $bin = base64_decode($payload, true);
        return $bin === false ? '' : $bin;
    }

    public function write(string $id, string $data): bool
    {
        $payload = base64_encode($data);
        $mac = hash_hmac('sha256', $payload, self::secret());
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
        if (!headers_sent()) {
            setcookie(self::COOKIE, $payload . '.' . $mac, [
                'expires' => time() + 86400 * 7,
                'path' => '/',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        return true;
    }

    public function destroy(string $id): bool
    {
        if (!headers_sent()) {
            setcookie(self::COOKIE, '', [
                'expires' => time() - 42000,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        unset($_COOKIE[self::COOKIE]);
        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        return 0;
    }

    private static function secret(): string
    {
        return (string) (etab_env('ETAB_DB_PASS') ?: config('csrf_key', 'etab_csrf'));
    }
}
