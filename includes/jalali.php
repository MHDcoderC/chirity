<?php

declare(strict_types=1);

/** تبدیل میلادی به شمسی */
function gregorian_to_jalali(int $gy, int $gm, int $gd): array
{
    $gDaysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    $jy = ($gy <= 1600) ? 0 : 979;
    $gy -= ($gy <= 1600) ? 621 : 1600;
    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
    $days = (365 * $gy)
        + (int) (($gy2 + 3) / 4)
        - (int) (($gy2 + 99) / 100)
        + (int) (($gy2 + 399) / 400)
        - 80 + $gd;

    for ($i = 0; $i < $gm - 1; $i++) {
        $days += $gDaysInMonth[$i];
    }
    if ($gm > 2 && (($gy % 4 === 0 && $gy % 100 !== 0) || ($gy % 400 === 0))) {
        $days++;
    }

    $jy += 33 * (int) ($days / 12053);
    $days %= 12053;
    $jy += 4 * (int) ($days / 1461);
    $days %= 1461;
    $jy += (int) (($days - 1) / 365);

    if ($days > 365) {
        $days = ($days - 1) % 365;
    }

    if ($days < 186) {
        $jm = 1 + (int) ($days / 31);
        $jd = 1 + ($days % 31);
    } else {
        $jm = 7 + (int) (($days - 186) / 30);
        $jd = 1 + (($days - 186) % 30);
    }

    return [$jy, $jm, $jd];
}

function persian_digits(string $value): string
{
    return strtr($value, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
}

function jalali_month_name(int $month): string
{
    static $names = [
        1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد',
        4 => 'تیر', 5 => 'مرداد', 6 => 'شهریور',
        7 => 'مهر', 8 => 'آبان', 9 => 'آذر',
        10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
    ];
    return $names[$month] ?? '';
}

function jalali_weekday_name(int $w): string
{
    static $days = [
        0 => 'یکشنبه', 1 => 'دوشنبه', 2 => 'سه‌شنبه',
        3 => 'چهارشنبه', 4 => 'پنجشنبه', 5 => 'جمعه', 6 => 'شنبه',
    ];
    return $days[$w] ?? '';
}

/**
 * @param string|null $datetime تاریخ میلادی (Y-m-d یا datetime)
 * @param string $format short|long|full|time
 */
function jalali_date(?string $datetime, string $format = 'short'): string
{
    if (!$datetime) {
        return '';
    }

    $ts = strtotime($datetime);
    if ($ts === false) {
        return $datetime;
    }

    [$jy, $jm, $jd] = gregorian_to_jalali(
        (int) date('Y', $ts),
        (int) date('n', $ts),
        (int) date('j', $ts)
    );

    $weekday = jalali_weekday_name((int) date('w', $ts));
    $month = jalali_month_name($jm);

    $text = match ($format) {
        'long'  => sprintf('%d %s %d', $jd, $month, $jy),
        'full'  => sprintf('%s، %d %s %d', $weekday, $jd, $month, $jy),
        'time'  => sprintf('%d %s %d، %s', $jd, $month, $jy, date('H:i', $ts)),
        default => sprintf('%d/%02d/%02d', $jy, $jm, $jd),
    };

    return persian_digits($text);
}

function jalali_today(string $format = 'full'): string
{
    return jalali_date(date('Y-m-d'), $format);
}

/** تبدیل تاریخ شمسی به میلادی — خروجی Y-m-d */
function jalali_to_gregorian(int $jy, int $jm, int $jd): array
{
    $jy += 1595;
    $days = -355668 + (365 * $jy) + ((int) ($jy / 33)) * 8 + (int) ((($jy % 33) + 3) / 4) + $jd
        + (($jm < 7) ? ($jm - 1) * 31 : (($jm - 7) * 30) + 186);

    $gy = 400 * (int) ($days / 146097);
    $days %= 146097;
    $leap = true;
    if ($days >= 36525) {
        $days--;
        $gy += 100 * (int) ($days / 36524);
        $days %= 36524;
        if ($days >= 365) {
            $days++;
        } else {
            $leap = false;
        }
    }
    $gy += 4 * (int) ($days / 1461);
    $days %= 1461;
    if ($leap) {
        if ($days >= 366) {
            $days--;
            $gy += (int) ($days / 365);
            $days %= 365;
        }
    }

    $sal_a = [0, 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    $gm = 0;
    for ($i = 1; $i < 13; $i++) {
        $v = $sal_a[$i];
        if ($i > 2 && (($gy % 4 === 0 && $gy % 100 !== 0) || ($gy % 400 === 0))) {
            $v++;
        }
        if ($days < $v) {
            break;
        }
        $gm = $i;
    }
    $gd = $days - $sal_a[$gm] + 1;
    if ($gm > 2 && (($gy % 4 === 0 && $gy % 100 !== 0) || ($gy % 400 === 0))) {
        $gd--;
    }

    return [$gy, $gm, $gd];
}

function parse_jalali_date_string(string $input): ?string
{
    $input = str_replace(['/', '-', ' '], '/', trim($input));
    $input = strtr($input, ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9']);
    if (!preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', $input, $m)) {
        return null;
    }
    [$jy, $jm, $jd] = [(int) $m[1], (int) $m[2], (int) $m[3]];
    if ($jm < 1 || $jm > 12 || $jd < 1 || $jd > 31) {
        return null;
    }
    [$gy, $gm, $gd] = jalali_to_gregorian($jy, $jm, $jd);
    if (!checkdate($gm, $gd, $gy)) {
        return null;
    }
    return sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
}

function jalali_month_names(): array
{
    return [
        1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر',
        5 => 'مرداد', 6 => 'شهریور', 7 => 'مهر', 8 => 'آبان',
        9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
    ];
}

/** تبدیل تاریخ میلادی Y-m-d به اجزای شمسی */
function gregorian_to_jalali_parts(?string $date): array
{
    if (!$date || !preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $date, $m)) {
        return ['year' => '', 'month' => '', 'day' => ''];
    }
    [$jy, $jm, $jd] = gregorian_to_jalali((int) $m[1], (int) $m[2], (int) $m[3]);
    return ['year' => (string) $jy, 'month' => (string) $jm, 'day' => (string) $jd];
}

/** خواندن سه فیلد سال/ماه/روز شمسی از POST */
function parse_jalali_post_fields(string $prefix): ?string
{
    $y = trim($_POST[$prefix . '_year'] ?? '');
    $m = trim($_POST[$prefix . '_month'] ?? '');
    $d = trim($_POST[$prefix . '_day'] ?? '');
    if ($y === '' || $m === '' || $d === '') {
        return null;
    }
    return parse_jalali_date_string($y . '/' . $m . '/' . $d);
}

function jalali_year_options(int $from = 1300, int $to = 0): array
{
    if ($to < 1) {
        [$to] = gregorian_to_jalali((int) date('Y'), (int) date('n'), (int) date('j'));
    }
    $years = [];
    for ($y = $to; $y >= $from; $y--) {
        $years[] = $y;
    }
    return $years;
}

function validate_iran_national_id(string $code): bool
{
    if (!preg_match('/^\d{10}$/', $code)) {
        return false;
    }
    if (preg_match('/^(\d)\1{9}$/', $code)) {
        return false;
    }
    $check = (int) $code[9];
    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $sum += (int) $code[$i] * (10 - $i);
    }
    $rem = $sum % 11;
    return ($rem < 2 && $check === $rem) || ($rem >= 2 && $check === 11 - $rem);
}

function validate_iran_mobile(string $mobile): bool
{
    return (bool) preg_match('/^09\d{9}$/', $mobile);
}
