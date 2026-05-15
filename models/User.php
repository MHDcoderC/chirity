<?php

declare(strict_types=1);

class User
{
    public static function findByUsername(string $username): ?array
    {
        $stmt = db()->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT id, username, email, display_name, role, created_at FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(string $username, string $email, string $password, string $displayName): int
    {
        $stmt = db()->prepare(
            'INSERT INTO users (username, email, password, display_name, role) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $username,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            $displayName,
            'admin',
        ]);
        return (int) db()->lastInsertId();
    }
}
