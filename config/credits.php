<?php

declare(strict_types=1);

function kh_credit_decode(string $encoded, int $key = 0x5A): string
{
    $bin = base64_decode($encoded, true);
    if ($bin === false || $bin === '') {
        return '';
    }
    $out = '';
    $len = strlen($bin);
    for ($i = 0; $i < $len; $i++) {
        $out .= chr(ord($bin[$i]) ^ (($key + $i) & 0xFF));
    }
    return $out;
}

function kh_dev_name(): string
{
    static $v = null;
    return $v ??= kh_credit_decode('g96E8IfauM5Cu9e9yr/PscWw4A==');
}

function kh_dev_url(): string
{
    static $v = null;
    return $v ??= kh_credit_decode('Mi8oLS1lT04PDgAGCQMNRx4EHA==');
}

function kh_dev_label(): string
{
    static $v = null;
    return $v ??= kh_credit_decode('guyE7Ib4uMy570S87keww7PjtN621qn2SFM=');
}

function dev_credit_line(): string
{
    return kh_dev_label() . kh_dev_name();
}

function kh_dev_link_html(string $class = ''): string
{
    $cls = $class !== '' ? ' class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"' : '';
    return '<a href="' . htmlspecialchars(kh_dev_url(), ENT_QUOTES, 'UTF-8') . '"' . $cls
        . ' target="_blank" rel="noopener noreferrer">' . htmlspecialchars(kh_dev_name(), ENT_QUOTES, 'UTF-8') . '</a>';
}

function kh_dev_footer_credit(): string
{
    return '<span class="kh-dev-credit" data-kh="1">'
        . htmlspecialchars(kh_dev_label(), ENT_QUOTES, 'UTF-8')
        . kh_dev_link_html('site-footer-dev-link')
        . '</span>';
}

function kh_github_repo_url(): string
{
    return 'https://github.com/MHDcoderC/chirity';
}
