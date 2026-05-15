<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (!is_installed()) {
    redirect('install.php');
}

if (auth_user()) {
    redirect('admin/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    if (auth_attempt($user, $pass)) {
        redirect('admin/index.php');
    }
    $error = 'نام کاربری یا رمز عبور اشتباه است.';
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ورود به پنل</title>
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/css/bootstrap.rtl.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/fonts.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <h1 class="h4 mb-4 text-center"><i class="bi bi-shield-lock"></i> ورود مدیر</h1>
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">نام کاربری</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">رمز عبور</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-danger w-100">
                <i class="bi bi-box-arrow-in-left"></i> ورود
            </button>
        </form>
        <p class="text-center mt-3 mb-0 small">
            <a href="<?= url() ?>">بازگشت به سایت</a>
        </p>
    </div>
</div>
</body>
</html>
