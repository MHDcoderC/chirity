<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
auth_check();

$categories = Category::all();

ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted small mb-0">مدیریت دسته‌ها — ساختار مشابه وردپرس</p>
    <a href="<?= url('admin/categories/form.php') ?>" class="btn btn-danger btn-sm">
        <i class="bi bi-plus-lg"></i> دسته جدید
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>نام</th>
                    <th>نامک</th>
                    <th>تعداد خبر</th>
                    <th>ترتیب</th>
                    <th style="width:100px"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><strong><?= e($cat['name']) ?></strong></td>
                    <td class="small" dir="ltr"><?= e($cat['slug']) ?></td>
                    <td><?= (int) $cat['post_count'] ?></td>
                    <td><?= (int) $cat['sort_order'] ?></td>
                    <td>
                        <a href="<?= url('admin/categories/form.php?id=' . $cat['id']) ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="<?= url('admin/categories/delete.php?id=' . $cat['id']) ?>" class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('این دسته حذف شود؟')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($categories)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">دسته‌ای تعریف نشده.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$pageContent = ob_get_clean();
$pageTitle = 'دسته‌بندی‌ها';
require dirname(__DIR__, 2) . '/templates/partials/admin-layout.php';
