<?php
/**
 * اتصال PDO — پس از نصب، مقادیر در config/local.php ذخیره می‌شود.
 */

declare(strict_types=1);

require_once __DIR__ . '/env.php';

$dbConfig = [
    'host'    => '127.0.0.1',
    'port'    => '3306',
    'name'    => '',
    'user'    => '',
    'pass'    => '',
    'charset' => 'utf8mb4',
];

function kh_load_db_config(): array
{
    global $dbConfig;
    $localFile = __DIR__ . '/local.php';
    if (!is_file($localFile)) {
        return $dbConfig;
    }
    $local = require $localFile;
    if (!is_array($local)) {
        return $dbConfig;
    }
    return array_merge($dbConfig, $local);
}

$dbConfig = kh_load_db_config();

function kh_has_db_config_file(): bool
{
    return is_file(__DIR__ . '/local.php');
}

function kh_db_config_complete(): bool
{
    $c = kh_load_db_config();
    return ($c['name'] ?? '') !== '' && ($c['user'] ?? '') !== '';
}

function kh_pdo_from_config(?array $config = null): PDO
{
    $config ??= kh_load_db_config();
    $host = (string) ($config['host'] ?? 'localhost');
    $port = (string) ($config['port'] ?? '3306');
    $name = (string) ($config['name'] ?? '');
    $charset = (string) ($config['charset'] ?? 'utf8mb4');

    if ($name === '') {
        throw new PDOException('نام دیتابیس تنظیم نشده است.');
    }

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $name, $charset);

    return new PDO($dsn, (string) ($config['user'] ?? ''), (string) ($config['pass'] ?? ''), [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
}

function kh_test_db_connection(?array $config = null): bool
{
    try {
        kh_pdo_from_config($config);
        return true;
    } catch (Throwable) {
        return false;
    }
}

function kh_redirect_to_install(string $reason = ''): never
{
    $url = kh_public_url('install.php');
    if ($reason !== '') {
        $url .= (str_contains($url, '?') ? '&' : '?') . 'reason=' . rawurlencode($reason);
    }
    if (!headers_sent()) {
        header('Location: ' . $url);
    }
    exit;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (!kh_db_config_complete()) {
        kh_redirect_to_install('no_config');
    }

    try {
        $pdo = kh_pdo_from_config();
    } catch (PDOException $e) {
        error_log('DB connection failed: ' . $e->getMessage());
        kh_redirect_to_install('connect_failed');
    }

    return $pdo;
}
