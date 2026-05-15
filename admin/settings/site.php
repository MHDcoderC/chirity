<?php declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
auth_check();

$defaults = site_setting_defaults();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $name = limit_text_field(trim($_POST['site_name'] ?? ''), 80);
    $tagline = limit_text_field(trim($_POST['site_tagline'] ?? ''), 120);
    $about = limit_text_field(trim($_POST['footer_about'] ?? ''), 400);
    $phone = preg_replace('/\D/', '', $_POST['footer_phone'] ?? '') ?? '';
    $email = trim($_POST['footer_email'] ?? '');

    if ($name === '') {
        $errors[] = 'نام سایت الزامی است.';
    }
    if ($phone !== '' && !preg_match('/^09\d{9}$/', $phone)) {
        $errors[] = 'شماره همراه باید ۱۱ رقم و با ۰۹ شروع شود.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'ایمیل معتبر نیست.';
    }

    if (!$errors) {
        setting_set('site_name', $name);
        setting_set('site_tagline', $tagline);
        setting_set('footer_about', $about);
        setting_set('footer_phone', $phone);
        setting_set('footer_email', limit_text_field($email, 100));
        flash('success', 'تنظیمات سایت ذخیره شد.');
        redirect('admin/settings/site.php');
    }
}

site_init_defaults();

$data = [
    'site_name'    => setting_get('site_name', $defaults['site_name']),
    'site_tagline' => setting_get('site_tagline', $defaults['site_tagline']),
    'footer_about' => setting_get('footer_about', $defaults['footer_about']),
    'footer_phone' => setting_get('footer_phone', ''),
    'footer_email' => setting_get('footer_email', $defaults['footer_email']),
];

ob_start();
?>

<?php foreach ($errors as $err): ?>
<div class="alert alert-danger py-2"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="editor-box">
            <h2 class="h6 mb-1 fw-bold"><i class="bi bi-sliders"></i> تنظیمات اصلی سایت</h2>
            <p class="text-muted small mb-3">نام، زیرعنوان و اطلاعات فوتر از اینجا مدیریت می‌شود. بخش اعتبار فنی توسعه‌دهنده قابل تغییر نیست.</p>
            <form method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">نام سایت *</label>
                    <input type="text" name="site_name" class="form-control" required maxlength="80"
                           value="<?= e($data['site_name']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">متن زیر نام سایت (زیرعنوان)</label>
                    <input type="text" name="site_tagline" class="form-control" maxlength="120"
                           value="<?= e($data['site_tagline']) ?>"
                           placeholder="مثلاً: پایگاه اطلاع‌رسانی رسمی">
                </div>
                <div class="mb-3">
                    <label class="form-label">متن معرفی در فوتر</label>
                    <textarea name="footer_about" class="form-control" rows="3" maxlength="400"><?= e($data['footer_about']) ?></textarea>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">شماره همراه</label>
                        <input type="tel" name="footer_phone" class="form-control" dir="ltr"
                               inputmode="numeric" maxlength="11" pattern="09[0-9]{9}"
                               value="<?= e($data['footer_phone']) ?>" placeholder="09123456789">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">ایمیل</label>
                        <input type="email" name="footer_email" class="form-control" dir="ltr" maxlength="100"
                               value="<?= e($data['footer_email']) ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-check-lg"></i> ذخیره تنظیمات
                </button>
            </form>
        </div>
    </div>
</div>

<?php
$pageContent = ob_get_clean();
$pageTitle = 'تنظیمات سایت';
require dirname(__DIR__, 2) . '/templates/partials/admin-layout.php';
