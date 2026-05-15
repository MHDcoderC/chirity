<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
auth_check();

$counts = Post::counts();
$catCount = (int) db()->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$recent = Post::adminList(null, null, 1);

ob_start();
?>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-num text-success"><?= $counts['publish'] ?></div>
            <div class="stat-label">منتشر شده</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-num text-warning"><?= $counts['draft'] ?></div>
            <div class="stat-label">پیش‌نویس</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-num text-primary"><?= $catCount ?></div>
            <div class="stat-label">دسته‌بندی</div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span>آخرین نوشته‌ها</span>
        <a href="<?= url('admin/posts/form.php') ?>" class="btn btn-sm btn-danger">
            <i class="bi bi-plus-lg"></i> خبر جدید
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>عنوان</th>
                    <th>دسته</th>
                    <th>وضعیت</th>
                    <th>تاریخ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent['items'] as $p): ?>
                <tr>
                    <td><?= e($p['title']) ?></td>
                    <td><?= e($p['category_name'] ?? '—') ?></td>
                    <td><span class="badge bg-secondary"><?= post_status_label($p['status']) ?></span></td>
                    <td class="small text-muted"><?= jalali_date($p['updated_at'], 'long') ?></td>
                    <td>
                        <a href="<?= url('admin/posts/form.php?id=' . $p['id']) ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recent['items'])): ?>
                <tr><td colspan="5" class="text-muted text-center py-4">نوشته‌ای ثبت نشده.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$pageContent = ob_get_clean();
$pageTitle = 'پیشخوان';
require dirname(__DIR__) . '/templates/partials/admin-layout.php';
