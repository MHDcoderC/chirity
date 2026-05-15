<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
auth_check();

$calls = JehadiCall::all();
$activeCall = JehadiCall::active();

ob_start();
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <p class="text-muted small mb-0">مدیریت فراخوان‌های گروه جهادی — فقط یک فراخوان فعال هم‌زمان.</p>
    <div class="d-flex gap-2">
        <?php if (!$activeCall): ?>
        <a href="<?= url('admin/jehadi/form.php') ?>" class="btn btn-danger btn-sm">
            <i class="bi bi-plus-lg"></i> ایجاد فراخوان
        </a>
        <?php else: ?>
        <a href="<?= url('admin/jehadi/form.php?id=' . $activeCall['id']) ?>" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-pencil"></i> ویرایش فراخوان فعال
        </a>
        <?php endif; ?>
        <a href="<?= url('admin/jehadi/registrations.php') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-person-lines-fill"></i> ثبت‌نام‌ها
        </a>
    </div>
</div>

<?php if ($activeCall): ?>
<div class="alert alert-success py-2 small">
    <i class="bi bi-megaphone"></i>
    فراخوان فعال: <strong><?= e($activeCall['title']) ?></strong>
    — <?= persian_digits((string) $activeCall['registration_count']) ?> / <?= persian_digits((string) $activeCall['max_registrations']) ?> ثبت‌نام
    <a href="<?= url('jehadi.php') ?>" target="_blank" class="alert-link ms-2">مشاهده در سایت</a>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>عنوان</th>
                    <th>بازه فراخوان</th>
                    <th>ظرفیت</th>
                    <th>ثبت‌نام</th>
                    <th>وضعیت</th>
                    <th style="width:140px"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($calls as $c): ?>
                <tr>
                    <td><strong><?= e($c['title']) ?></strong></td>
                    <td class="small">
                        <?= jalali_date($c['date_from'], 'short') ?>
                        —
                        <?= jalali_date($c['date_to'], 'short') ?>
                    </td>
                    <td><?= persian_digits((string) $c['max_registrations']) ?></td>
                    <td><?= persian_digits((string) $c['registration_count']) ?></td>
                    <td>
                        <?php if ($c['status'] === 'active'): ?>
                            <span class="badge bg-success">فعال</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">لغو شده</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= url('admin/jehadi/form.php?id=' . $c['id']) ?>" class="btn btn-sm btn-outline-primary" title="ویرایش">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <?php if ($c['status'] === 'active'): ?>
                        <a href="<?= url('admin/jehadi/cancel.php?id=' . $c['id']) ?>" class="btn btn-sm btn-outline-warning"
                           onclick="return confirm('فراخوان لغو شود؟ بنر از سایت حذف می‌شود.')"
                           title="لغو فراخوان">
                            <i class="bi bi-x-circle"></i>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($calls)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">فراخوانی ثبت نشده.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$pageContent = ob_get_clean();
$pageTitle = 'گروه جهادی';
require dirname(__DIR__, 2) . '/templates/partials/admin-layout.php';
