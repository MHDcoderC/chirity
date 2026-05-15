<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
auth_check();

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$category = $id ? Category::find($id) : null;

if ($id && !$category) {
    flash('error', 'دسته یافت نشد.');
    redirect('admin/categories/index.php');
}

$parents = array_filter(Category::all(), fn($c) => !$id || (int)$c['id'] !== $id);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $data = [
        'name'             => trim($_POST['name'] ?? ''),
        'slug'             => trim($_POST['slug'] ?? ''),
        'description'      => trim($_POST['description'] ?? ''),
        'parent_id'        => (int) ($_POST['parent_id'] ?? 0) ?: null,
        'sort_order'       => (int) ($_POST['sort_order'] ?? 0),
        'show_in_header'   => isset($_POST['show_in_header']),
        'show_on_home'     => isset($_POST['show_on_home']),
        'home_posts_limit' => (int) ($_POST['home_posts_limit'] ?? 4),
    ];

    if ($data['name'] === '') {
        $errors[] = 'نام دسته الزامی است.';
    }

    if (!$errors) {
        Category::save($data, $id);
        flash('success', $id ? 'دسته به‌روزرسانی شد.' : 'دسته ایجاد شد.');
        redirect('admin/categories/index.php');
    }
    $category = $data;
}

$category = $category ?? [
    'name' => '', 'slug' => '', 'description' => '', 'parent_id' => null,
    'sort_order' => 0, 'show_in_header' => 0, 'show_on_home' => 0, 'home_posts_limit' => 4,
];

ob_start();
?>

<?php foreach ($errors as $err): ?>
<div class="alert alert-danger py-2"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="editor-box">
            <form method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">نام دسته</label>
                    <input type="text" name="name" class="form-control" value="<?= e($category['name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">نامک (slug)</label>
                    <input type="text" name="slug" class="form-control" dir="ltr" value="<?= e($category['slug']) ?>"
                           placeholder="خالی = خودکار">
                </div>
                <div class="mb-3">
                    <label class="form-label">دسته مادر</label>
                    <select name="parent_id" class="form-select">
                        <option value="">— بدون والد —</option>
                        <?php foreach ($parents as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= (int)($category['parent_id'] ?? 0) === (int)$p['id'] ? 'selected' : '' ?>>
                            <?= e($p['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">توضیحات</label>
                    <textarea name="description" class="form-control" rows="3"><?= e($category['description']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">ترتیب نمایش</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= (int) $category['sort_order'] ?>">
                </div>
                <div class="mb-4 border rounded p-3 bg-light">
                    <p class="small fw-bold mb-2">نمایش در سایت</p>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="show_in_header" id="showInHeader"
                               value="1" <?= !empty($category['show_in_header']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="showInHeader">نمایش در منوی هدر</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="show_on_home" id="showOnHome"
                               value="1" <?= !empty($category['show_on_home']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="showOnHome">نمایش در صفحه اصلی</label>
                    </div>
                    <div>
                        <label class="form-label small">تعداد خبر در صفحه اصلی</label>
                        <input type="number" name="home_posts_limit" class="form-control form-control-sm"
                               min="1" max="12" value="<?= (int) ($category['home_posts_limit'] ?? 4) ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-check-lg"></i> ذخیره
                </button>
                <a href="<?= url('admin/categories/index.php') ?>" class="btn btn-outline-secondary">انصراف</a>
            </form>
        </div>
    </div>
</div>

<?php
$pageContent = ob_get_clean();
$pageTitle = $id ? 'ویرایش دسته' : 'دسته جدید';
require dirname(__DIR__, 2) . '/templates/partials/admin-layout.php';
