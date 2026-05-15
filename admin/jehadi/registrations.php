<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
auth_check();

$filterCallId = (int) ($_GET['call_id'] ?? 0) ?: null;
$calls = JehadiCall::all();
$registrations = JehadiRegistration::adminList($filterCallId);
$totalCount = count($registrations);

ob_start();
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h2 class="h6 mb-1 fw-bold">ثبت‌نام‌های جهادی</h2>
        <p class="text-muted small mb-0"><?= persian_digits((string) $totalCount) ?> نفر ثبت‌نام کرده‌اند</p>
    </div>
    <a href="<?= url('admin/jehadi/index.php') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-right"></i> فراخوان‌ها
    </a>
</div>

<div class="card border-0 shadow-sm mb-3 jehadi-filter-card">
    <div class="card-body py-3">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-8">
                <label class="form-label small mb-1 fw-semibold">فیلتر بر اساس فراخوان</label>
                <select name="call_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">همه فراخوان‌ها</option>
                    <?php foreach ($calls as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= $filterCallId === (int) $c['id'] ? 'selected' : '' ?>>
                        <?= e($c['title']) ?>
                        — <?= $c['status'] === 'active' ? 'فعال' : 'لغو شده' ?>
                        (<?= persian_digits((string) $c['registration_count']) ?> نفر)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<?php if (empty($registrations)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
        ثبت‌نامی یافت نشد.
    </div>
</div>
<?php else: ?>
<div class="jehadi-reg-list">
    <?php foreach ($registrations as $i => $r): ?>
    <article class="jehadi-reg-card">
        <div class="jehadi-reg-card-head">
            <span class="jehadi-reg-num"><?= persian_digits((string) ($i + 1)) ?></span>
            <div class="jehadi-reg-title-wrap">
                <h3 class="jehadi-reg-name"><?= e($r['full_name']) ?></h3>
                <span class="jehadi-reg-call"><?= e($r['call_title']) ?></span>
            </div>
            <time class="jehadi-reg-time" datetime="<?= e($r['created_at']) ?>">
                <?= jalali_date($r['created_at'], 'time') ?>
            </time>
        </div>
        <ul class="jehadi-reg-meta">
            <li>
                <i class="bi bi-calendar3"></i>
                <span>تولد</span>
                <strong><?= jalali_date($r['birth_date'], 'short') ?></strong>
            </li>
            <li>
                <i class="bi bi-person-vcard"></i>
                <span>کد ملی</span>
                <strong dir="ltr"><?= e($r['national_id']) ?></strong>
            </li>
            <li>
                <i class="bi bi-phone"></i>
                <span>موبایل</span>
                <strong dir="ltr"><?= e($r['mobile']) ?></strong>
            </li>
        </ul>
    </article>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php
$pageContent = ob_get_clean();
$pageTitle = 'ثبت‌نام‌های جهادی';
require dirname(__DIR__, 2) . '/templates/partials/admin-layout.php';
