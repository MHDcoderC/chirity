<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    return kh_public_url($path);
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }
    $val = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $val;
}

function slugify(string $text): string
{
    $text = trim($text);
    $text = preg_replace('/\s+/u', '-', $text) ?? $text;
    $text = preg_replace('/[^\p{L}\p{N}\-_]+/u', '', $text) ?? $text;
    $text = trim($text, '-');
    if ($text === '') {
        $text = 'item-' . time();
    }
    return mb_strtolower($text, 'UTF-8');
}

function unique_slug(string $base, string $table, ?int $excludeId = null): string
{
    $slug = slugify($base);
    $original = $slug;
    $i = 1;

    while (true) {
        $sql = "SELECT id FROM {$table} WHERE slug = ?";
        $params = [$slug];
        if ($excludeId) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        if (!$stmt->fetch()) {
            return $slug;
        }
        $slug = $original . '-' . $i++;
    }
}

function post_status_label(string $status): string
{
    return POST_STATUS[$status] ?? $status;
}

function sanitize_html(string $html): string
{
    $allowed = '<p><br><strong><b><em><i><u><h2><h3><h4><ul><ol><li><a><img><blockquote><hr><span><div>';
    return strip_tags($html, $allowed);
}

function excerpt_from_content(string $html, ?int $len = null): string
{
    $len ??= EXCERPT_MAX_LENGTH;
    $plain = trim(preg_replace('/\s+/u', ' ', strip_tags($html)) ?? '');
    return truncate_text($plain, $len);
}

function truncate_text(string $text, int $max, string $suffix = '…'): string
{
    $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    if ($max < 1 || mb_strlen($text) <= $max) {
        return $text;
    }
    $cut = mb_substr($text, 0, $max);
    if (preg_match('/\s/u', $cut)) {
        $cut = preg_replace('/\s+\S*$/u', '', $cut) ?? $cut;
    }
    return rtrim($cut, '،,. ') . $suffix;
}

function limit_text_field(string $text, int $max): string
{
    $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    if (mb_strlen($text) <= $max) {
        return $text;
    }
    return mb_substr($text, 0, $max);
}

function normalize_post_fields(array $data): array
{
    $data['title'] = limit_text_field((string) ($data['title'] ?? ''), TITLE_MAX_LENGTH);
    $data['excerpt'] = limit_text_field((string) ($data['excerpt'] ?? ''), EXCERPT_MAX_LENGTH);
    return $data;
}

function is_installed(): bool
{
    $root = dirname(__DIR__);
    if (!is_file($root . '/config/installed.lock')) {
        return false;
    }
    if (!is_file($root . '/config/local.php')) {
        return false;
    }
    return kh_test_db_connection();
}

function is_install_locked(): bool
{
    return is_file(dirname(__DIR__) . '/config/installed.lock');
}

