<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
auth_check();

$status = $_GET['status'] ?? null;
$catId = isset($_GET['cat']) ? (int) $_GET['cat'] : null;
$page = max(1, (int) ($_GET['page'] ?? 1));
$list = Post::adminList($status ?: null, $catId ?: null, $page);
$categories = Category::forSelect();

ob_start();
?>
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <div class="btn-group btn-group-sm">
        <a href="?" class="btn btn-outline-secondary <?= !$status ? 'active' : '' ?>">همه</a>
        <?php foreach (POST_STATUS as $key => $label): ?>
        <a href="?status=<?= $key ?>" class="btn btn-outline-secondary <?= $status === $key ? 'active' : '' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </div>
    <a href="<?= url('admin/posts/form.php') ?>" class="btn btn-danger btn-sm">
        <i class="bi bi-plus-lg"></i> افزودن خبر
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width:50px"></th>
                    <th>عنوان</th>
                    <th>دسته</th>
                    <th>نویسنده</th>
                    <th>وضعیت</th>
                    <th>تاریخ</th>
                    <th style="width:120px"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($list['items'] as $p): ?>
                <tr>
                    <td>
                        <?php if ($p['featured_image']): ?>
                            <img src="<?= e(UPLOAD_URL . '/' . $p['featured_image']) ?>" width="40" height="30" style="object-fit:cover" alt="">
                        <?php else: ?>
                            <span class="text-muted"><i class="bi bi-image"></i></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= e($p['title']) ?></strong>
                        <?php if ($p['status'] === 'publish'): ?>
                            <a href="<?= url('news/' . $p['slug']) ?>" target="_blank" class="small ms-1"><i class="bi bi-box-arrow-up-left"></i></a>
                        <?php endif; ?>
                    </td>
                    <td><?= e($p['category_name'] ?? '—') ?></td>
                    <td class="small"><?= e($p['author_name']) ?></td>
                    <td><span class="badge bg-secondary"><?= post_status_label($p['status']) ?></span></td>
                    <td class="small text-muted"><?= jalali_date($p['updated_at'], 'long') ?></td>
                    <td>
                        <a href="<?= url('admin/posts/form.php?id=' . $p['id']) ?>" class="btn btn-sm btn-outline-primary" title="ویرایش">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="<?= url('admin/posts/delete.php?id=' . $p['id']) ?>" class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('این خبر حذف شود؟')" title="حذف">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($list['items'])): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">موردی یافت نشد.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($list['pages'] > 1): ?>
<nav class="mt-3">
    <ul class="pagination pagination-sm justify-content-center">
        <?php for ($i = 1; $i <= $list['pages']; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?><?= $status ? '&status=' . e($status) : '' ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif;

$pageContent = ob_get_clean();
$pageTitle = 'همه اخبار';
require dirname(__DIR__, 2) . '/templates/partials/admin-layout.php';
