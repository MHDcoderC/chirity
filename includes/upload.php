<?php

declare(strict_types=1);

function upload_image(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('خطا در آپلود فایل.');
    }
    if (($file['size'] ?? 0) > MAX_UPLOAD_SIZE) {
        throw new RuntimeException('حجم تصویر بیش از حد مجاز است.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, ALLOWED_IMAGE_TYPES, true)) {
        throw new RuntimeException('فرمت تصویر مجاز نیست.');
    }

    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
        default      => throw new RuntimeException('فرمت نامعتبر.'),
    };

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $name = date('Ymd') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = UPLOAD_DIR . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('ذخیره فایل انجام نشد.');
    }

    image_resize_cover($dest, FEATURED_IMAGE_WIDTH, FEATURED_IMAGE_HEIGHT, FEATURED_IMAGE_QUALITY);

    return $name;
}

/**
 * برش و تغییر اندازه تصویر به نسبت ثابت (cover) برای یکنواختی اسلایدر و کارت‌ها
 */
function image_resize_cover(string $path, int $width, int $height, int $quality = 85): bool
{
    if (!is_file($path) || !extension_loaded('gd')) {
        return false;
    }

    $info = @getimagesize($path);
    if (!$info) {
        return false;
    }

    $src = match ($info['mime']) {
        'image/jpeg' => @imagecreatefromjpeg($path),
        'image/png'  => @imagecreatefrompng($path),
        'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
        'image/gif'  => @imagecreatefromgif($path),
        default      => false,
    };

    if (!$src) {
        return false;
    }

    $srcW = imagesx($src);
    $srcH = imagesy($src);
    if ($srcW < 1 || $srcH < 1) {
        imagedestroy($src);
        return false;
    }

    $scale = max($width / $srcW, $height / $srcH);
    $cropW = (int) round($width / $scale);
    $cropH = (int) round($height / $scale);
    $cropX = (int) round(($srcW - $cropW) / 2);
    $cropY = (int) round(($srcH - $cropH) / 2);

    $dst = imagecreatetruecolor($width, $height);
    if (!$dst) {
        imagedestroy($src);
        return false;
    }

    if ($info['mime'] === 'image/png' || $info['mime'] === 'image/gif') {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $width, $height, $transparent);
    }

    imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, $width, $height, $cropW, $cropH);
    imagedestroy($src);

    $ok = match ($info['mime']) {
        'image/jpeg' => imagejpeg($dst, $path, $quality),
        'image/png'  => imagepng($dst, $path, (int) round(9 - ($quality / 100) * 9)),
        'image/webp' => function_exists('imagewebp') ? imagewebp($dst, $path, $quality) : false,
        'image/gif'  => imagegif($dst, $path),
        default      => false,
    };

    imagedestroy($dst);
    return (bool) $ok;
}

function delete_upload(?string $filename): void
{
    if (!$filename) {
        return;
    }
    $path = UPLOAD_DIR . '/' . basename($filename);
    if (is_file($path)) {
        unlink($path);
    }
}

function upload_member_photo(array $file): string
{
    if (!is_dir(MEMBER_UPLOAD_DIR)) {
        mkdir(MEMBER_UPLOAD_DIR, 0755, true);
    }
    $name = upload_image($file);
    $src = UPLOAD_DIR . '/' . $name;
    $dest = MEMBER_UPLOAD_DIR . '/' . $name;
    if (is_file($src)) {
        rename($src, $dest);
    }
    return $name;
}

function delete_member_photo(?string $filename): void
{
    if (!$filename) {
        return;
    }
    $path = MEMBER_UPLOAD_DIR . '/' . basename($filename);
    if (is_file($path)) {
        unlink($path);
    }
}

function upload_jehadi_banner(array $file): string
{
    if (!is_dir(JEHADI_UPLOAD_DIR)) {
        mkdir(JEHADI_UPLOAD_DIR, 0755, true);
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, ALLOWED_IMAGE_TYPES, true)) {
        throw new RuntimeException('فرمت تصویر بنر مجاز نیست.');
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('خطا در آپلود بنر.');
    }
    if (($file['size'] ?? 0) > MAX_UPLOAD_SIZE) {
        throw new RuntimeException('حجم بنر بیش از حد مجاز است.');
    }

    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
        default      => throw new RuntimeException('فرمت نامعتبر.'),
    };

    $name = 'banner_' . date('Ymd') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $dest = JEHADI_UPLOAD_DIR . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('ذخیره بنر انجام نشد.');
    }

    image_resize_cover($dest, JEHADI_BANNER_WIDTH, JEHADI_BANNER_HEIGHT, JEHADI_BANNER_QUALITY);
    return $name;
}

function delete_jehadi_banner(?string $filename): void
{
    if (!$filename) {
        return;
    }
    $path = JEHADI_UPLOAD_DIR . '/' . basename($filename);
    if (is_file($path)) {
        unlink($path);
    }
}
