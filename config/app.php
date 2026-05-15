<?php
/**
 * تنظیمات کلی سامانه
 */

declare(strict_types=1);

require_once __DIR__ . '/env.php';

kh_check_php_version();
kh_bootstrap_environment();

define('APP_ROOT', dirname(__DIR__));
define('APP_URL', kh_app_url());
define('APP_ENV', kh_env());
define('APP_NAME', 'خیریه حضرت زینب');
define('APP_DB_NAME', 'namazi');
define('APP_VERSION', '1.0.0');

define('UPLOAD_DIR', APP_ROOT . '/uploads/posts');
define('UPLOAD_URL', APP_URL . '/uploads/posts');
define('MEMBER_UPLOAD_DIR', APP_ROOT . '/uploads/members');
define('MEMBER_UPLOAD_URL', APP_URL . '/uploads/members');

define('POSTS_PER_PAGE', 12);
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

/** محدودیت متن (کاراکتر) */
define('TITLE_MAX_LENGTH', 120);
define('EXCERPT_MAX_LENGTH', 200);
define('EXCERPT_SLIDER_LENGTH', 90);

/** ابعاد استاندارد تصویر شاخص — ۱۶:۹ */
define('FEATURED_IMAGE_WIDTH', 1200);
define('FEATURED_IMAGE_HEIGHT', 675);
define('FEATURED_IMAGE_QUALITY', 85);

/** بنر فراخوان گروه جهادی — تمام‌عرض */
define('JEHADI_UPLOAD_DIR', APP_ROOT . '/uploads/jehadi');
define('JEHADI_UPLOAD_URL', APP_URL . '/uploads/jehadi');
define('JEHADI_BANNER_WIDTH', 1920);
define('JEHADI_BANNER_HEIGHT', 480);
define('JEHADI_BANNER_QUALITY', 85);
define('JEHADI_TITLE_MAX', 150);

define('POST_STATUS', [
    'draft'   => 'پیش‌نویس',
    'publish' => 'منتشر شده',
    'pending' => 'در انتظار بررسی',
]);

define('SESSION_LIFETIME', 7200);
