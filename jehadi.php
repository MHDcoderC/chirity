<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (!is_installed()) {
    header('Location: ' . url('install.php'));
    exit;
}

$call = JehadiCall::active();
$navCategories = Category::forHeader();
$pageTitle = $call ? $call['title'] : 'فراخوان گروه جهادی';

$canRegister = $call && JehadiCall::isOpenForRegistration($call);
$isFull = $call && JehadiCall::isFull($call);

require __DIR__ . '/templates/partials/head.php';
require __DIR__ . '/templates/partials/header.php';
?>

<main class="jehadi-page py-4">
    <div class="container">
        <?php if (!$call): ?>
            <div class="alert alert-secondary text-center py-5">
                <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                در حال حاضر فراخوان فعالی برگزار نمی‌شود. ان‌شاءالله به‌زودی فراخوان جدید اعلام می‌شود.
            </div>
        <?php else: ?>
            <header class="jehadi-page-head mb-4">
                <span class="jehadi-page-badge"><i class="bi bi-megaphone"></i> فراخوان گروه جهادی</span>
                <h1 class="jehadi-page-title"><?= e($call['title']) ?></h1>
                <p class="jehadi-page-dates text-muted mb-0">
                    <i class="bi bi-calendar-range"></i>
                    از <?= jalali_date($call['date_from'], 'long') ?>
                    تا <?= jalali_date($call['date_to'], 'long') ?>
                    <span class="mx-2">|</span>
                    <i class="bi bi-people"></i>
                    <?= persian_digits((string) $call['registration_count']) ?> / <?= persian_digits((string) $call['max_registrations']) ?> ثبت‌نام
                </p>
            </header>

            <?php if ($isFull): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    ظرفیت این فراخوان تکمیل شده است. ان‌شاءالله در فراخوان‌های بعدی می‌توانید ثبت‌نام کنید.
                </div>
            <?php endif; ?>

            <div class="jehadi-page-content article-single">
                <div class="article-content">
                    <?= sanitize_html($call['description']) ?>
                </div>
            </div>

            <div class="jehadi-page-actions text-center mt-4">
                <?php if ($canRegister): ?>
                    <button type="button" class="btn btn-danger btn-lg px-5" data-bs-toggle="modal" data-bs-target="#jehadiRegisterModal">
                        <i class="bi bi-pencil-square"></i> ثبت‌نام در فراخوان
                    </button>
                <?php elseif (!$isFull): ?>
                    <p class="text-muted">مهلت ثبت‌نام این فراخوان به پایان رسیده است.</p>
                <?php endif; ?>
            </div>

            <?php include __DIR__ . '/templates/partials/jehadi-register-modal.php'; ?>
        <?php endif; ?>
    </div>
</main>

<?php
$extraJs = '<script src="' . asset('js/jehadi-register.js') . '"></script>';
require __DIR__ . '/templates/partials/footer.php';
