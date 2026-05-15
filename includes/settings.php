<?php

declare(strict_types=1);

function setting_get(string $key, string $default = ''): string
{
    try {
        $stmt = db()->prepare('SELECT value FROM settings WHERE `key` = ?');
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (string) $val : $default;
    } catch (Throwable) {
        return $default;
    }
}

function setting_set(string $key, string $value): void
{
    db()->prepare(
        'INSERT INTO settings (`key`, value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE value = ?'
    )->execute([$key, $value, $value]);
}

function donation_info(): array
{
    return [
        'card_number' => setting_get('donation_card_number'),
        'card_holder' => setting_get('donation_card_holder'),
    ];
}

function donation_is_configured(): bool
{
    $info = donation_info();
    return preg_replace('/\D/', '', $info['card_number']) !== '';
}

function format_card_number(string $number): string
{
    $digits = preg_replace('/\D/', '', $number);
    if ($digits === '') {
        return '';
    }
    return trim(chunk_split($digits, 4, ' '));
}

function normalize_card_number(string $input): string
{
    return preg_replace('/\D/', '', $input) ?? '';
}
