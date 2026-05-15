<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
auth_check();

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$member = $id ? CharityMember::find($id) : null;

if ($id && !$member) {
    flash('error', 'عضو یافت نشد.');
    redirect('admin/members/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $data = [
        'name'       => trim($_POST['name'] ?? ''),
        'position'   => trim($_POST['position'] ?? ''),
        'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        'is_active'  => isset($_POST['is_active']),
        'photo'      => $member['photo'] ?? '',
    ];

    if ($data['name'] === '') {
        $errors[] = 'نام عضو الزامی است.';
    }

    try {
        if (!empty($_FILES['photo']['name'])) {
            if ($data['photo']) {
                delete_member_photo($data['photo']);
            }
            $data['photo'] = upload_member_photo($_FILES['photo']);
        }
    } catch (RuntimeException $e) {
        $errors[] = $e->getMessage();
    }

    if (!$id && $data['photo'] === '') {
        $errors[] = 'تصویر عضو الزامی است.';
    }

    if (!$errors) {
        CharityMember::save($data, $id);
        flash('success', $id ? 'عضو به‌روزرسانی شد.' : 'عضو اضافه شد.');
        redirect('admin/members/index.php');
    }
    $member = array_merge($member ?? [], $data);
}

$member = $member ?? [
    'name' => '', 'position' => '', 'photo' => '', 'sort_order' => 0, 'is_active' => 1,
];

ob_start();
?>

<?php foreach ($errors as $err): ?>
<div class="alert alert-danger py-2"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="editor-box">
            <form method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">نام *</label>
                    <input type="text" name="name" class="form-control" required maxlength="120"
                           value="<?= e($member['name']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">سمت</label>
                    <input type="text" name="position" class="form-control" maxlength="120"
                           value="<?= e($member['position']) ?>" placeholder="مثلاً: مدیرعامل، عضو هیئت مدیره">
                </div>
                <div class="mb-3">
                    <label class="form-label">تصویر <?= $id ? '' : '*' ?></label>
                    <input type="file" name="photo" class="form-control" accept="image/*" <?= $id ? '' : 'required' ?>>
                    <?php if (!empty($member['photo'])): ?>
                    <img src="<?= e(MEMBER_UPLOAD_URL . '/' . $member['photo']) ?>" alt="" class="thumb-preview d-block" style="max-width:140px">
                    <?php endif; ?>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label">ترتیب نمایش</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= (int) $member['sort_order'] ?>">
                    </div>
                    <div class="col-6 d-flex align-items-end">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="is_active"
                                   <?= !empty($member['is_active']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">نمایش در سایت</label>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-danger">ذخیره</button>
                    <a href="<?= url('admin/members/index.php') ?>" class="btn btn-outline-secondary">انصراف</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$pageContent = ob_get_clean();
$pageTitle = $id ? 'ویرایش عضو' : 'عضو جدید';
require dirname(__DIR__, 2) . '/templates/partials/admin-layout.php';
