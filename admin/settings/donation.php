<?php declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
auth_check();

$info = donation_info();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $cardNumber = normalize_card_number($_POST['card_number'] ?? '');
    $cardHolder = trim($_POST['card_holder'] ?? '');

    if ($cardNumber !== '' && (strlen($cardNumber) < 16 || strlen($cardNumber) > 19)) {
        $errors[] = 'شماره کارت باید بین ۱۶ تا ۱۹ رقم باشد.';
    }
    if ($cardHolder === '' && $cardNumber !== '') {
        $errors[] = 'نام صاحب کارت الزامی است.';
    }
    if ($cardNumber === '' && $cardHolder !== '') {
        $errors[] = 'شماره کارت را وارد کنید.';
    }

    if (!$errors) {
        setting_set('donation_card_number', $cardNumber);
        setting_set('donation_card_holder', limit_text_field($cardHolder, 120));
        flash('success', 'اطلاعات کمک مالی ذخیره شد.');
        redirect('admin/settings/donation.php');
    }
    $info = ['card_number' => $cardNumber, 'card_holder' => $cardHolder];
}

$displayNumber = format_card_number($info['card_number']);

ob_start();
?>

<?php foreach ($errors as $err): ?>
<div class="alert alert-danger py-2"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="editor-box">
            <h2 class="h6 mb-3"><i class="bi bi-credit-card"></i> تنظیمات کمک مالی</h2>
            <p class="text-muted small">این اطلاعات در دکمه «کمک مالی» هدر سایت نمایش داده می‌شود.</p>
            <form method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">شماره کارت *</label>
                    <input type="text" name="card_number" class="form-control" dir="ltr"
                           inputmode="numeric" maxlength="24"
                           value="<?= e($displayNumber) ?>"
                           placeholder="6037 9977 1234 5678">
                    <div class="form-text">فقط رقم — با یا بدون خط تیره</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">نام صاحب کارت *</label>
                    <input type="text" name="card_holder" class="form-control"
                           value="<?= e($info['card_holder']) ?>"
                           maxlength="120" placeholder="نام کامل صاحب حساب">
                </div>
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-check-lg"></i> ذخیره
                </button>
            </form>
        </div>
    </div>
</div>

<?php
$pageContent = ob_get_clean();
$pageTitle = 'کمک مالی';
require dirname(__DIR__, 2) . '/templates/partials/admin-layout.php';
