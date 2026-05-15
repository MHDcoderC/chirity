<?php

declare(strict_types=1);

/** نصب اولیه */

require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/credits.php';

kh_check_php_version();

if (kh_env() === 'local') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

$installBase = kh_app_url();
$repairMode = isset($_GET['repair']) || isset($_GET['reason']);
$dbReason = (string) ($_GET['reason'] ?? '');

if (is_file(__DIR__ . '/config/installed.lock') && !$repairMode) {
    require_once __DIR__ . '/config/database.php';
    if (kh_has_db_config_file() && kh_test_db_connection()) {
        header('Location: ' . kh_public_url());
        exit;
    }
}

session_start();

$installLocked = is_file(__DIR__ . '/config/installed.lock');

$errors = [];
$termsError = '';
$success = false;
$termsAccepted = !empty($_SESSION['install_terms_accepted']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'accept_terms') {
    if (!empty($_POST['terms_read'])) {
        $_SESSION['install_terms_accepted'] = true;
        header('Location: ' . kh_public_url('install.php'));
        exit;
    }
    $termsError = 'برای ادامه، تأیید مطالعه و پذیرش شرایط الزامی است.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'accept_terms') {
    if (!$termsAccepted) {
        header('Location: ' . kh_public_url('install.php'));
        exit;
    }

    $dbHost = trim($_POST['db_host'] ?? 'localhost');
    $dbPort = trim($_POST['db_port'] ?? '3306');
    $dbName = trim($_POST['db_name'] ?? '');
    $dbUser = trim($_POST['db_user'] ?? '');
    $dbPass = $_POST['db_pass'] ?? '';
    $appUrl = trim($_POST['app_url'] ?? '');
    $createDatabase = !empty($_POST['create_database']);

    $adminUser = trim($_POST['admin_user'] ?? '');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $adminPass = $_POST['admin_pass'] ?? '';
    $adminName = trim($_POST['admin_name'] ?? 'مدیر سایت');
    $siteName = trim($_POST['site_name'] ?? 'خیریه حضرت زینب شهرستان دهدشت');

    if ($dbName === '' || $dbUser === '') {
        $errors[] = 'نام دیتابیس و کاربر MySQL الزامی است.';
    }
    if ($adminUser === '' || $adminPass === '' || strlen($adminPass) < 6) {
        $errors[] = 'نام کاربری و رمز عبور مدیر (حداقل ۶ کاراکتر) الزامی است.';
    }

    if (!$errors) {
        try {
            $pdoOpts = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];

            if ($createDatabase && kh_env() === 'local') {
                $bootstrap = new PDO(
                    "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4",
                    $dbUser,
                    $dbPass,
                    $pdoOpts
                );
                $bootstrap->exec(
                    "CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
                );
            }

            $pdo = new PDO(
                "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4",
                $dbUser,
                $dbPass,
                $pdoOpts
            );

            $schema = file_get_contents(__DIR__ . '/database/schema.sql');
            if ($schema === false) {
                throw new RuntimeException('فایل schema یافت نشد.');
            }
            $schema = preg_replace('/^--.*$/m', '', $schema) ?? $schema;
            foreach (array_filter(array_map('trim', explode(';', $schema))) as $sql) {
                if ($sql !== '') {
                    $pdo->exec($sql);
                }
            }

            $hash = password_hash($adminPass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO users (username, email, password, display_name, role) VALUES (?,?,?,?,?)'
            );
            $stmt->execute([$adminUser, $adminEmail, $hash, $adminName, 'admin']);

            $savedAppUrl = $appUrl !== '' ? $appUrl : kh_detect_app_url();
            if ($savedAppUrl !== '/' && str_starts_with($savedAppUrl, '//')) {
                $savedAppUrl = '';
            }
            $savedAppUrl = rtrim($savedAppUrl, '/');

            $local = "<?php\nreturn [\n    'host' => " . var_export($dbHost, true)
                . ",\n    'port' => " . var_export($dbPort, true)
                . ",\n    'name' => " . var_export($dbName, true)
                . ",\n    'user' => " . var_export($dbUser, true)
                . ",\n    'pass' => " . var_export($dbPass, true) . ",\n];\n";
            file_put_contents(__DIR__ . '/config/local.php', $local);

            $localApp = "<?php\nreturn [\n    'app_url' => " . var_export($savedAppUrl, true)
                . ",\n    'env' => 'production',\n    'force_https' => true,\n];\n";
            file_put_contents(__DIR__ . '/config/local.app.php', $localApp);

            kh_sync_htaccess_rewrite_base($savedAppUrl);

            $cats = [
                ['اخبار خیریه', 'charity-news', 'آخرین اخبار و رویدادهای خیریه'],
                ['گزارش‌ها', 'reports', 'گزارش فعالیت‌ها و پروژه‌ها'],
                ['اخبار عمومی', 'general', 'سایر اخبار'],
            ];
            $ins = $pdo->prepare('INSERT INTO categories (name, slug, description, sort_order) VALUES (?,?,?,?)');
            foreach ($cats as $i => $c) {
                $ins->execute([$c[0], $c[1], $c[2], $i]);
            }

            $siteDefaults = [
                'site_name'    => $siteName,
                'site_tagline' => 'پایگاه اطلاع‌رسانی رسمی',
                'footer_about' => 'پوشش اخبار و رویدادهای خیریه — شفاف، به‌روز و در دسترس همه.',
                'footer_phone' => '',
                'footer_email' => 'info@charity.local',
            ];
            $setIns = $pdo->prepare(
                'INSERT INTO settings (`key`, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)'
            );
            foreach ($siteDefaults as $key => $val) {
                $setIns->execute([$key, $val]);
            }

            unset($_SESSION['install_terms_accepted']);
            file_put_contents(__DIR__ . '/config/installed.lock', date('c'));
            $success = true;
        } catch (Throwable $e) {
            $errors[] = 'خطا: ' . $e->getMessage();
        }
    }
}

$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>نصب سامانه</title>
    <link rel="stylesheet" href="<?= $h(kh_public_url('assets/vendor/bootstrap/css/bootstrap.rtl.min.css')) ?>">
    <link rel="stylesheet" href="<?= $h(kh_public_url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css')) ?>">
    <link rel="stylesheet" href="<?= $h(kh_public_url('assets/css/fonts.css')) ?>">
    <link rel="stylesheet" href="<?= $h(kh_public_url('assets/css/main.css')) ?>">
</head>
<body class="install-page">
<div class="install-page-bg" aria-hidden="true"></div>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-6">

            <?php if ($success): ?>
            <div class="install-glass-card install-glass-card--success">
                <div class="install-glass-inner text-center">
                    <div class="install-success-icon"><i class="bi bi-check-circle-fill"></i></div>
                    <h1 class="install-glass-title h4">نصب با موفقیت انجام شد</h1>
                    <p class="install-glass-sub mb-4">سامانه آماده استفاده است.</p>
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                        <a href="<?= $h(kh_public_url('admin/login.php')) ?>" class="btn btn-light">ورود به پنل</a>
                        <a href="<?= $h(kh_public_url()) ?>" class="btn btn-outline-light">مشاهده سایت</a>
                    </div>
                </div>
            </div>

            <?php elseif (!$termsAccepted): ?>
            <div class="install-terms-card">
                <div class="install-terms-inner">
                    <header class="install-terms-head">
                        <h1 class="install-terms-title">شرایط استفاده</h1>
                        <p class="install-terms-lead">قبل از نصب، این موارد را بخوانید.</p>
                    </header>

                    <div class="install-terms-body">
                        <p>
                            این سامانه توسط <strong><?= $h(kh_dev_name()) ?></strong> برای
                            <strong>خیریه حضرت زینب شهرستان دهدشت</strong> طراحی شده است.
                        </p>
                        <p><strong>استفاده در همین خیریه:</strong> بدون نیاز به مجوز جداگانه.</p>
                        <p>
                            <strong>استفاده برای خیریه، سازمان یا پروژه دیگر:</strong>
                            هر کسی می‌تواند استفاده کند؛ شرط آن
                            <strong>ستاره‌دادن (Star)</strong> به مخزن GitHub این پروژه با حساب شخصی خودتان است.
                        </p>
                        <p class="install-terms-github mb-0">
                            <i class="bi bi-github"></i>
                            مخزن پروژه:
                            <a href="<?= $h(kh_github_repo_url()) ?>" class="install-terms-link" target="_blank" rel="noopener noreferrer"><?= $h(kh_github_repo_url()) ?></a>
                        </p>
                        <p class="install-terms-contact mb-0">
                            توسعه‌دهنده:
                            <a href="<?= $h(kh_dev_url()) ?>" class="install-terms-link" target="_blank" rel="noopener noreferrer"><?= $h(kh_dev_name()) ?></a>
                        </p>
                    </div>

                    <?php if ($termsError !== ''): ?>
                    <p class="install-terms-error"><?= $h($termsError) ?></p>
                    <?php endif; ?>

                    <form method="post" class="install-terms-form">
                        <input type="hidden" name="action" value="accept_terms">
                        <label class="install-terms-agree" for="termsRead">
                            <input type="checkbox" name="terms_read" value="1" id="termsRead" required>
                            <span class="install-terms-agree-box" aria-hidden="true"></span>
                            <span class="install-terms-agree-text">شرایط را خواندم و می‌پذیرم؛ در صورت استفاده خارج از خیریه اصلی، مخزن GitHub را Star می‌کنم.</span>
                        </label>
                        <button type="submit" class="btn btn-install-terms w-100">ادامه نصب</button>
                    </form>
                </div>
            </div>

            <?php else: ?>
            <div class="install-glass-card install-glass-card--setup">
                <div class="install-glass-border" aria-hidden="true"></div>
                <div class="install-glass-inner">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-4">
                        <h1 class="install-glass-title h4 mb-0"><i class="bi bi-gear-wide-connected"></i> نصب اولیه</h1>
                        <span class="install-step-badge">مرحله ۲ از ۲</span>
                    </div>

                    <?php foreach ($errors as $err): ?>
                    <div class="alert alert-danger py-2"><?= $h($err) ?></div>
                    <?php endforeach; ?>

                    <?php if ($dbReason === 'connect_failed'): ?>
                    <div class="alert alert-warning py-2 small">
                        اتصال به دیتابیس برقرار نشد. همان اطلاعاتی که در cPanel ساختید را در فرم زیر وارد کنید — نیازی به ویرایش دستی فایل نیست.
                    </div>
                    <?php elseif ($repairMode || $installLocked): ?>
                    <div class="alert alert-warning py-2 small">
                        تنظیم مجدد: فقط فرم زیر را پر کنید؛ <code>config/local.php</code> خودکار ساخته می‌شود.
                    </div>
                    <?php endif; ?>

                    <div class="alert alert-info py-2 small mb-3 install-cpanel-hint">
                        <strong>هاست cPanel:</strong> ابتدا در cPanel دیتابیس و کاربر MySQL بسازید و کاربر را به دیتابیس اضافه کنید.
                        هاست معمولاً <code>localhost</code> است.
                    </div>

                    <form method="post">
                        <input type="hidden" name="action" value="install">
                        <h2 class="install-section-title">آدرس نصب</h2>
                        <div class="mb-4">
                            <label class="form-label">مسیر سایت (APP_URL)</label>
                            <input type="text" name="app_url" class="form-control install-input" dir="ltr"
                                   value="<?= $h($_POST['app_url'] ?? $installBase) ?>"
                                   placeholder="خالی برای روت دامنه یا /kh">
                            <div class="form-text text-white-50 small">تشخیص خودکار: <code dir="ltr"><?= $h($installBase === '' ? '/' : $installBase) ?></code></div>
                        </div>

                        <h2 class="install-section-title">پایگاه داده</h2>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">هاست</label>
                                <input type="text" name="db_host" class="form-control install-input" dir="ltr"
                                       value="<?= $h($_POST['db_host'] ?? (kh_env() === 'local' ? '127.0.0.1' : 'localhost')) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">پورت</label>
                                <input type="text" name="db_port" class="form-control install-input" dir="ltr"
                                       value="<?= $h($_POST['db_port'] ?? '3306') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">نام دیتابیس</label>
                                <input type="text" name="db_name" class="form-control install-input" dir="ltr"
                                       value="<?= $h($_POST['db_name'] ?? 'namazi') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">کاربر MySQL</label>
                                <input type="text" name="db_user" class="form-control install-input" dir="ltr"
                                       value="<?= $h($_POST['db_user'] ?? (kh_env() === 'local' ? 'root' : '')) ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">رمز MySQL</label>
                                <input type="password" name="db_pass" class="form-control install-input" dir="ltr">
                            </div>

                        <?php if (kh_env() === 'local'): ?>
                        <div class="form-check mb-4 install-terms-check">
                            <input class="form-check-input" type="checkbox" name="create_database" value="1" id="createDb"
                                <?= !isset($_POST['action']) || !empty($_POST['create_database']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="createDb">ساخت خودکار دیتابیس (فقط XAMPP/لوکال)</label>
                        </div>
                        <?php endif; ?>
                        </div>

                        <h2 class="install-section-title">تنظیمات سایت</h2>
                        <div class="mb-4">
                            <label class="form-label">نام سایت</label>
                            <input type="text" name="site_name" class="form-control install-input" required
                                   value="<?= $h($_POST['site_name'] ?? 'خیریه حضرت زینب شهرستان دهدشت') ?>">
                        </div>

                        <h2 class="install-section-title">حساب مدیر</h2>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">نام کاربری</label>
                                <input type="text" name="admin_user" class="form-control install-input" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ایمیل</label>
                                <input type="email" name="admin_email" class="form-control install-input">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">نام نمایشی</label>
                                <input type="text" name="admin_name" class="form-control install-input" value="مدیر سایت">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">رمز عبور</label>
                                <input type="password" name="admin_pass" class="form-control install-input" minlength="6" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-install-continue w-100">
                            <i class="bi bi-check-lg"></i> نصب و راه‌اندازی
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>
</body>
</html>
