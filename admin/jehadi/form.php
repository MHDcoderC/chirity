<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
auth_check();

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$call = $id ? JehadiCall::find($id) : null;

if ($id && !$call) {
    flash('error', 'فراخوان یافت نشد.');
    redirect('admin/jehadi/index.php');
}

if (!$id && JehadiCall::hasActive()) {
    flash('error', 'تا زمان وجود فراخوان فعال، فراخوان جدید قابل ایجاد نیست. ابتدا فراخوان فعلی را لغو کنید.');
    redirect('admin/jehadi/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $dateFrom = parse_jalali_post_fields('date_from');
    $dateTo = parse_jalali_post_fields('date_to');

    $data = [
        'title'             => trim($_POST['title'] ?? ''),
        'description'       => sanitize_html($_POST['description'] ?? ''),
        'date_from'         => $dateFrom ?? '',
        'date_to'           => $dateTo ?? '',
        'max_registrations' => (int) ($_POST['max_registrations'] ?? 50),
        'banner'            => $call['banner'] ?? '',
        'status'            => 'active',
    ];

    if ($data['title'] === '') {
        $errors[] = 'عنوان فراخوان الزامی است.';
    }
    if (!$dateFrom || !$dateTo) {
        $errors[] = 'تاریخ شروع و پایان شمسی را کامل انتخاب کنید.';
    } elseif ($data['date_from'] > $data['date_to']) {
        $errors[] = 'تاریخ پایان باید بعد از تاریخ شروع باشد.';
    }
    if ($data['max_registrations'] < 1) {
        $errors[] = 'حداکثر ثبت‌نام باید حداقل ۱ باشد.';
    }

    try {
        if (!empty($_FILES['banner']['name'])) {
            if ($data['banner']) {
                delete_jehadi_banner($data['banner']);
            }
            $data['banner'] = upload_jehadi_banner($_FILES['banner']);
        }
    } catch (RuntimeException $e) {
        $errors[] = $e->getMessage();
    }

    if (!$id && $data['banner'] === '') {
        $errors[] = 'تصویر بنر تمام‌عرض الزامی است.';
    }

    if (!$errors) {
        try {
            JehadiCall::save($data, $id);
            flash('success', $id ? 'فراخوان به‌روزرسانی شد.' : 'فراخوان ایجاد و فعال شد.');
            redirect('admin/jehadi/index.php');
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }
    $call = array_merge($call ?? [], $data);
}

$call = $call ?? [
    'title' => '', 'description' => '', 'banner' => '',
    'date_from' => '', 'date_to' => '', 'max_registrations' => 50,
];

ob_start();
?>

<?php foreach ($errors as $err): ?>
<div class="alert alert-danger py-2"><?= e($err) ?></div>
<?php endforeach; ?>

<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div> class="post-editor-layout">
        <div class="editor-box">
            <div class="mb-3">
                <label class="form-label">عنوان فراخوان *</label>
                <input type="text" name="title" class="form-control" required maxlength="<?= JEHADI_TITLE_MAX ?>"
                       value="<?= e($call['title']) ?>">
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <?php
                    $prefix = 'date_from';
                    $label = 'شروع فراخوان (شمسی) *';
                    $gregorian = $call['date_from'] ?? null;
                    include dirname(__DIR__, 2) . '/templates/partials/jalali-date-fields.php';
                    ?>
                </div>
                <div class="col-md-6">
                    <?php
                    $prefix = 'date_to';
                    $label = 'پایان فراخوان (شمسی) *';
                    $gregorian = $call['date_to'] ?? null;
                    include dirname(__DIR__, 2) . '/templates/partials/jalali-date-fields.php';
                    ?>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label small text-muted">توضیحات فراخوان</label>
            </div>
            <div id="editor-container"><?= $call['description'] ?></div>
            <input type="hidden" name="description" id="content-input">
        </div>

        <div>
            <div class="meta-box">
                <div> class="meta-box-header">ظرفیت و بنر</div>
                <div class="meta-box-body">
                    <label class="form-label">حداکثر ثبت‌نام *</label>
                    <input type="number" name="max_registrations" class="form-control mb-3" min="1" max="99999"
                           value="<?= (int) $call['max_registrations'] ?>" required>
                    <label class="form-label">بنر تمام‌عرض <?= $id ? '' : '*' ?></label>
                    <?php if (!empty($call['banner'])): ?>
                    <img src="<?= e(JEHADI_UPLOAD_URL . '/' . $call['banner']) ?>" class="thumb-preview d-block mb-2" alt="">
                    <?php endif; ?>
                    <input type="file" name="banner" class="form-control form-control-sm" accept="image/*" <?= $id ? '' : 'required' ?>>
                    <div class="form-text small text-muted">
                        ابعاد پیشنهادی: <?= JEHADI_BANNER_WIDTH ?>×<?= JEHADI_BANNER_HEIGHT ?> پیکسل (تمام‌عرض)
                    </div>
                    <button type="submit" class="btn btn-danger w-100 mt-3">
                        <i class="bi bi-check-lg"></i> <?= $id ? 'ذخیره تغییرات' : 'انتشار فراخوان' ?>
                    </button>
                    <a href="<?= url('admin/jehadi/index.php') ?>" class="btn btn-outline-secondary w-100 btn-sm mt-2">بازگشت</a>
                </div>
            </div>
        </div>
    </div>
</form>

<script src="<?= asset('vendor/quill/quill.js') ?>"></script>
<script>
const quill = new Quill('#editor-container', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ header: [2, 3, false] }],
            ['bold', 'italic', 'underline'],
            [{ list: 'ordered' }, { list: 'bullet' }],
            ['link'],
            ['clean']
        ]
    }
});
document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('content-input').value = quill.root.innerHTML;
});
</script>

<?php
$pageContent = ob_get_clean();
$pageTitle = $id ? 'ویرایش فراخوان' : 'ایجاد گروه جهادی';
require dirname(__DIR__, 2) . '/templates/partials/admin-layout.php';
