<?php

class User
{
    public static function find(int $id): ?array
    {
        return Database::query('SELECT * FROM users WHERE id = ?', [$id])->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        return Database::query('SELECT * FROM users WHERE email = ?', [strtolower(trim($email))])->fetch() ?: null;
    }

    public static function judges(): array
    {
        return Database::query("SELECT * FROM users WHERE role = 'judge' ORDER BY name")->fetchAll();
    }

    public static function all(?string $role = null): array
    {
        if ($role) {
            return Database::query('SELECT * FROM users WHERE role = ? ORDER BY name', [$role])->fetchAll();
        }
        return Database::query('SELECT * FROM users ORDER BY role, name')->fetchAll();
    }

    public static function create(array $data): int
    {
        Database::query(
            'INSERT INTO users (name, email, password_hash, role, phone, bio, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $data['name'],
                strtolower(trim($data['email'])),
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['role'] ?? 'judge',
                $data['phone'] ?? null,
                $data['bio'] ?? null,
                $data['is_active'] ?? 1,
            ]
        );
        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $fields = [];
        $params = [];
        foreach (['name', 'email', 'phone', 'bio', 'role', 'is_active'] as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "$f = ?";
                $params[] = $f === 'email' ? strtolower(trim($data[$f])) : $data[$f];
            }
        }
        if (!empty($data['password'])) {
            $fields[] = 'password_hash = ?';
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        if (!$fields) {
            return;
        }
        $params[] = $id;
        Database::query('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?', $params);
    }

    public static function delete(int $id): void
    {
        Database::query('DELETE FROM users WHERE id = ? AND role = ?', [$id, 'judge']);
    }

    public static function countJudges(): int
    {
        return (int) Database::query("SELECT COUNT(*) c FROM users WHERE role='judge' AND is_active=1")->fetch()['c'];
    }
}
