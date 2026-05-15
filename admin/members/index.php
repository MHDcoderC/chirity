<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
auth_check();

$members = CharityMember::all();

ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted small mb-0">نام، سمت و تصویر اعضا — در اسلایدر انتهای صفحات نمایش داده می‌شود.</p>
    <a href="<?= url('admin/members/form.php') ?>" class="btn btn-danger btn-sm">
        <i class="bi bi-plus-lg"></i> عضو جدید
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width:70px">تصویر</th>
                    <th>نام</th>
                    <th>سمت</th>
                    <th>ترتیب</th>
                    <th>وضعیت</th>
                    <th style="width:100px"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $m): ?>
                <tr>
                    <td>
                        <img src="<?= e(MEMBER_UPLOAD_URL . '/' . $m['photo']) ?>"
                             alt="" class="rounded" width="48" height="48" style="object-fit:cover">
                    </td>
                    <td><strong><?= e($m['name']) ?></strong></td>
                    <td><?= e($m['position']) ?></td>
                    <td><?= (int) $m['sort_order'] ?></td>
                    <td>
                        <?php if ($m['is_active']): ?>
                            <span class="badge bg-success">فعال</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">غیرفعال</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= url('admin/members/form.php?id=' . $m['id']) ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="<?= url('admin/members/delete.php?id=' . $m['id']) ?>" class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('این عضو حذف شود؟')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($members)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">هنوز عضوی ثبت نشده.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$pageContent = ob_get_clean();
$pageTitle = 'اعضای خیریه';
require dirname(__DIR__, 2) . '/templates/partials/admin-layout.php';
