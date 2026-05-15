<?php
/** هسته بارگذاری */

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/credits.php';
require_once __DIR__ . '/site.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once __DIR__ . '/jalali.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/upload.php';
require_once __DIR__ . '/migrate.php';
require_once __DIR__ . '/settings.php';

kh_configure_session();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// بارگذاری مدل‌ها
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/Category.php';
require_once dirname(__DIR__) . '/models/Post.php';
require_once dirname(__DIR__) . '/models/Comment.php';
require_once dirname(__DIR__) . '/models/PostGallery.php';
require_once dirname(__DIR__) . '/models/CharityMember.php';
require_once dirname(__DIR__) . '/models/JehadiCall.php';
require_once dirname(__DIR__) . '/models/JehadiRegistration.php';

if (is_installed()) {
    run_migrations();
    site_init_defaults();
}
