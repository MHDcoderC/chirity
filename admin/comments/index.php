<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
auth_check();

$status = $_GET['status'] ?? 'pending';
if ($status === 'all') {
    $status = null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';
    $cid = (int) ($_POST['comment_id'] ?? 0);
    if ($cid > 0) {
        if ($action === 'approve') {
            Comment::setStatus($cid, 'approved');
            flash('success', 'نظر تأیید شد.');
        } elseif ($action === 'reject') {
            Comment::setStatus($cid, 'rejected');
            flash('success', 'نظر رد شد.');
        } elseif ($action === 'delete') {
            Comment::delete($cid);
            flash('success', 'نظر حذف شد.');
        }
    }
    redirect('admin/comments/index.php' . ($status ? '?status=' . $status : ''));
}

$comments = Comment::adminList($status);

ob_start();
?>
<div class="btn-group btn-group-sm mb-3">
    <a href="?status=pending" class="btn btn-outline-secondary <?= ($status ?? '') === 'pending' ? 'active' : '' ?>">در انتظار</a>
    <a href="?status=approved" class="btn btn-outline-secondary <?= ($status ?? '') === 'approved' ? 'active' : '' ?>">تأیید شده</a>
    <a href="?status=all" class="btn btn-outline-secondary <?= $status === null ? 'active' : '' ?>">همه</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>نویسنده</th>
                    <th>مطلب</th>
                    <th>متن</th>
                    <th>وضعیت</th>
                    <th>تاریخ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comments as $c): ?>
                <tr>
                    <td class="small"><?= e($c['author_name']) ?></td>
                    <td class="small">
                        <a href="<?= url('news/' . $c['post_slug']) ?>" target="_blank"><?= e($c['post_title']) ?></a>
                    </td>
                    <td class="small" style="max-width:280px"><?= e(mb_substr($c['body'], 0, 120)) ?>…</td>
                    <td><span class="badge bg-secondary"><?= e($c['status']) ?></span></td>
                    <td class="small text-muted"><?= jalali_date($c['created_at'], 'short') ?></td>
                    <td class="text-nowrap">
                        <form method="post" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="comment_id" value="<?= (int)$c['id'] ?>">
                            <?php if ($c['status'] !== 'approved'): ?>
                            <button name="action" value="approve" class="btn btn-sm btn-success" title="تأیید"><i class="bi bi-check"></i></button>
                            <?php endif; ?>
                            <?php if ($c['status'] !== 'rejected'): ?>
                            <button name="action" value="reject" class="btn btn-sm btn-warning" title="رد"><i class="bi bi-x"></i></button>
                            <?php endif; ?>
                            <button name="action" value="delete" class="btn btn-sm btn-outline-danger" title="حذف"
                                    onclick="return confirm('حذف شود؟')"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($comments)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">نظری یافت نشد.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$pageContent = ob_get_clean();
$pageTitle = 'مدیریت نظرات';
require dirname(__DIR__, 2) . '/templates/partials/admin-layout.php';
