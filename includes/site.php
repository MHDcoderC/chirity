<?php

declare(strict_types=1);

function site_setting_defaults(): array
{
    return [
        'site_name'        => APP_NAME,
        'site_tagline'     => 'پایگاه اطلاع‌رسانی رسمی',
        'footer_about'     => 'پوشش اخبار و رویدادهای خیریه — شفاف، به‌روز و در دسترس همه.',
        'footer_phone'     => '',
        'footer_email'     => 'info@charity.local',
    ];
}

function site_init_defaults(): void
{
    if (!is_installed()) {
        return;
    }
    foreach (site_setting_defaults() as $key => $default) {
        if (setting_get($key) === '') {
            setting_set($key, $default);
        }
    }
}

function site_name(): string
{
    if (!is_installed()) {
        return APP_NAME;
    }
    site_init_defaults();
    $v = setting_get('site_name', APP_NAME);
    return $v !== '' ? $v : APP_NAME;
}

function site_tagline(): string
{
    if (!is_installed()) {
        return site_setting_defaults()['site_tagline'];
    }
    site_init_defaults();
    return setting_get('site_tagline', site_setting_defaults()['site_tagline']);
}

function site_footer_about(): string
{
    if (!is_installed()) {
        return site_setting_defaults()['footer_about'];
    }
    site_init_defaults();
    return setting_get('footer_about', site_setting_defaults()['footer_about']);
}

function site_footer_phone(): string
{
    if (!is_installed()) {
        return '';
    }
    return setting_get('footer_phone', '');
}

function site_footer_email(): string
{
    if (!is_installed()) {
        return site_setting_defaults()['footer_email'];
    }
    site_init_defaults();
    return setting_get('footer_email', site_setting_defaults()['footer_email']);
}

function site_footer_phone_display(): string
{
    $raw = preg_replace('/\D/', '', site_footer_phone());
    if ($raw === '' || strlen($raw) < 10) {
        return site_footer_phone();
    }
    if (strlen($raw) === 11 && str_starts_with($raw, '09')) {
        return persian_digits(substr($raw, 0, 4) . ' ' . substr($raw, 4, 3) . ' ' . substr($raw, 7));
    }
    return persian_digits($raw);
}
