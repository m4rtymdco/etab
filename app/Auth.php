<?php

class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $user = Database::query(
            'SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1',
            [strtolower(trim($email))]
        )->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_role'] = $user['role'];
        Audit::log((int) $user['id'], 'login', 'user', (int) $user['id']);
        return true;
    }

    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        static $cached = null;
        static $cachedId = null;
        $id = (int) $_SESSION['user_id'];
        if ($cached && $cachedId === $id) {
            return $cached;
        }
        $cached = Database::query('SELECT * FROM users WHERE id = ? LIMIT 1', [$id])->fetch() ?: null;
        $cachedId = $id;
        if (!$cached || !(int) $cached['is_active']) {
            self::logout();
            return null;
        }
        return $cached;
    }

    public static function id(): ?int
    {
        return self::check() ? (int) $_SESSION['user_id'] : null;
    }

    public static function logout(): void
    {
        $uid = $_SESSION['user_id'] ?? null;
        if ($uid) {
            Audit::log((int) $uid, 'logout', 'user', (int) $uid);
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }
}
