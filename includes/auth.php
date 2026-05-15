<?php

declare(strict_types=1);

function auth_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function auth_check(): void
{
    if (!auth_user()) {
        redirect('admin/login.php');
    }
}

function auth_login(array $user): void
{
    session_regenerate_id(true);
    unset($user['password']);
    $_SESSION['user'] = $user;
}

function auth_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function auth_attempt(string $username, string $password): bool
{
    $user = User::findByUsername($username);
    if (!$user || !password_verify($password, $user['password'])) {
        return false;
    }
    auth_login($user);
    return true;
}
