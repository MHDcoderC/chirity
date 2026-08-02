<?php

declare(strict_types=1);

function kh_local_app_config(): array
{
    static $cfg = null;
    if ($cfg !== null) {
        return $cfg;
    }
    $file = __DIR__ . '/local.app.php';
    if (is_file($file)) {
        $loaded = require $file;
        $cfg = is_array($loaded) ? $loaded : [];
    } else {
        $cfg = [];
    }
    return $cfg;
}

function kh_env(): string
{
    $cfg = kh_local_app_config();
    $env = (string) ($cfg['env'] ?? '');
    if ($env === 'production' || $env === 'local') {
        return $env;
    }
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    if ($host === 'localhost' || str_starts_with($host, '127.0.0.1') || str_ends_with($host, '.local')) {
        return 'local';
    }
    return 'production';
}

function kh_is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    if ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443) {
        return true;
    }
    $fwd = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    return $fwd === 'https';
}

function kh_detect_app_url(): string
{
    $cfg = kh_local_app_config();
    if (array_key_exists('app_url', $cfg) && is_string($cfg['app_url'])) {
        return rtrim($cfg['app_url'], '/');
    }

    if (PHP_SAPI === 'cli') {
        return '/kh';
    }

    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($script === '') {
        return '';
    }

    $dir = dirname($script);
    if ($dir === '/' || $dir === '.' || $dir === '\\') {
        return '';
    }

    return rtrim($dir, '/');
}

function kh_app_url(): string
{
    static $cached = null;
    return $cached ??= kh_detect_app_url();
}

function kh_rewrite_base(): string
{
    $base = kh_app_url();
    return $base === '' ? '/' : $base . '/';
}

function kh_sync_htaccess_rewrite_base(?string $appUrl = null): void
{
    $file = dirname(__DIR__) . '/.htaccess';
    if (!is_file($file) || !is_writable($file)) {
        return;
    }
    $content = file_get_contents($file);
    if ($content === false) {
        return;
    }
    if ($appUrl !== null) {
        $appUrl = rtrim($appUrl, '/');
        $base = $appUrl === '' ? '/' : $appUrl . '/';
    } else {
        $base = kh_rewrite_base();
    }
    $updated = preg_replace('/^\s*RewriteBase\s+.*$/m', '    RewriteBase ' . $base, $content, 1);
    if (is_string($updated) && $updated !== $content) {
        file_put_contents($file, $updated);
    }
}

function kh_force_https_redirect(): void
{
    $cfg = kh_local_app_config();
    $force = $cfg['force_https'] ?? null;
    if ($force === false) {
        return;
    }
    if (kh_env() !== 'production') {
        return;
    }
    if (kh_is_https() || PHP_SAPI === 'cli') {
        return;
    }
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    if ($host === '') {
        return;
    }
    header('Location: https://' . $host . $uri, true, 301);
    exit;
}

function kh_bootstrap_environment(): void
{
    if (defined('KH_ENV_BOOTSTRAPPED')) {
        return;
    }
    define('KH_ENV_BOOTSTRAPPED', true);

    if (kh_env() === 'production') {
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');
        error_reporting(E_ALL);
    } else {
        ini_set('display_errors', '1');
        error_reporting(E_ALL);
    }

    kh_force_https_redirect();
}

function kh_configure_session(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', (string) (defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 7200));

    if (kh_is_https()) {
        ini_set('session.cookie_secure', '1');
    }

    session_name('kh_session');
}

function kh_public_url(string $path = ''): string
{
    $path = ltrim($path, '/');
    $base = kh_app_url();
    if ($path === '') {
        return $base === '' ? '/' : $base . '/';
    }
    return ($base === '' ? '' : $base) . '/' . $path;
}

function kh_check_php_version(): void
{
    if (PHP_VERSION_ID < 80100) {
        http_response_code(500);
        echo 'PHP 8.1 یا بالاتر لازم است. نسخه فعلی: ' . PHP_VERSION;
        exit;
    }
}
