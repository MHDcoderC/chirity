<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
auth_check();

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$post = $id ? Post::find($id) : null;

if ($id && !$post) {
    flash('error', 'نوشته یافت نشد.');
    redirect('admin/posts/index.php');
}

$categories = Category::forSelect();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $data = [
        'title'          => trim($_POST['title'] ?? ''),
        'slug'           => trim($_POST['slug'] ?? ''),
        'excerpt'        => trim($_POST['excerpt'] ?? ''),
        'content'        => sanitize_html($_POST['content'] ?? ''),
        'category_id'    => (int) ($_POST['category_id'] ?? 0) ?: null,
        'status'         => $_POST['status'] ?? 'draft',
        'published_at'   => $_POST['published_at'] ?? null,
        'featured_image' => $post['featured_image'] ?? null,
        'show_in_ticker' => isset($_POST['show_in_ticker']),
        'allow_comments' => isset($_POST['allow_comments']),
    ];

    if ($data['title'] === '') {
        $errors[] = 'عنوان الزامی است.';
    } elseif (mb_strlen(trim($_POST['title'] ?? '')) > TITLE_MAX_LENGTH) {
        $errors[] = 'عنوان حداکثر ' . persian_digits((string) TITLE_MAX_LENGTH) . ' کاراکتر مجاز است.';
    }
    if (mb_strlen(trim($_POST['excerpt'] ?? '')) > EXCERPT_MAX_LENGTH) {
        $errors[] = 'چکیده حداکثر ' . persian_digits((string) EXCERPT_MAX_LENGTH) . ' کاراکتر مجاز است.';
    }
    if (!array_key_exists($data['status'], POST_STATUS)) {
        $data['status'] = 'draft';
    }
    if ($data['excerpt'] === '' && $data['content'] !== '') {
        $data['excerpt'] = excerpt_from_content($data['content']);
    }
    $data = normalize_post_fields($data);

    if (!$errors) {
        try {
            if (!empty($_POST['remove_image']) && $data['featured_image']) {
                delete_upload($data['featured_image']);
                $data['featured_image'] = null;
            }
            if (!empty($_FILES['featured_image']['name'])) {
                if ($data['featured_image']) {
                    delete_upload($data['featured_image']);
                }
                $data['featured_image'] = upload_image($_FILES['featured_image']);
            }

            $savedId = Post::save($data, (int) auth_user()['id'], $id);

            if (!empty($_POST['delete_gallery']) && is_array($_POST['delete_gallery'])) {
                foreach ($_POST['delete_gallery'] as $gid) {
                    PostGallery::delete((int) $gid);
                }
            }
            if (!empty($_FILES['gallery']['name'][0])) {
                $files = $_FILES['gallery'];
                $count = count($files['name']);
                for ($i = 0; $i < $count; $i++) {
                    if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                        continue;
                    }
                    $file = [
                        'name'     => $files['name'][$i],
                        'type'     => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error'    => $files['error'][$i],
                        'size'     => $files['size'][$i],
                    ];
                    $img = upload_image($file);
                    if ($img) {
                        PostGallery::add($savedId, $img);
                    }
                }
            }

            flash('success', $id ? 'خبر به‌روزرسانی شد.' : 'خبر ذخیره شد.');
            redirect('admin/posts/form.php?id=' . $savedId);
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }
    }
    $post = array_merge($post ?? [], $data);
}

$post = $post ?? [
    'title' => '', 'slug' => '', 'excerpt' => '', 'content' => '',
    'category_id' => null, 'status' => 'draft', 'featured_image' => null,
    'show_in_ticker' => 0, 'allow_comments' => 1,
];
$gallery = $id ? PostGallery::forPost($id) : [];

ob_start();
?>

<?php foreach ($errors as $err): ?>
<div class="alert alert-danger py-2"><?= e($err) ?></div>
<?php endforeach; ?>

<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="post-editor-layout">
        <div class="editor-box">
            <div class="mb-3">
                <input type="text" name="title" id="post-title" class="form-control border-0 px-0 fw-bold"
                       style="font-size:1.1rem"
                       placeholder="عنوان خبر را وارد کنید..." value="<?= e($post['title']) ?>"
                       maxlength="<?= TITLE_MAX_LENGTH ?>" required>
                <div class="form-text text-end small text-muted" id="title-counter"></div>
            </div>
            <div class="mb-3">
                <label class="form-label small text-muted">نامک (slug)</label>
                <input type="text" name="slug" class="form-control form-control-sm" dir="ltr"
                       value="<?= e($post['slug']) ?>" placeholder="خالی = خودکار از عنوان">
            </div>
            <div class="mb-3">
                <label class="form-label small text-muted d-flex justify-content-between align-items-center">
                    <span>چکیده</span>
                    <span class="fw-normal" id="excerpt-counter"></span>
                </label>
                <textarea name="excerpt" id="post-excerpt" class="form-control" rows="3"
                          maxlength="<?= EXCERPT_MAX_LENGTH ?>"
                          placeholder="خلاصه کوتاه (حداکثر <?= EXCERPT_MAX_LENGTH ?> کاراکتر)"><?= e($post['excerpt']) ?></textarea>
                <div class="form-text small text-muted">در اسلایدر نمایش داده می‌شود. خالی = خودکار از متن.</div>
            </div>
            <div class="mb-2">
                <label class="form-label small text-muted">متن خبر</label>
            </div>
            <div id="editor-container"><?= $post['content'] ?></div>
            <input type="hidden" name="content" id="content-input">
        </div>

        <div>
            <div class="meta-box">
                <div class="meta-box-header">انتشار</div>
                <div class="meta-box-body">
                    <select name="status" class="form-select mb-3">
                        <?php foreach (POST_STATUS as $key => $label): ?>
                        <option value="<?= $key ?>" <?= ($post['status'] ?? '') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-danger w-100 mb-2">
                        <i class="bi bi-check-lg"></i> <?= $id ? 'به‌روزرسانی' : 'انتشار / ذخیره' ?>
                    </button>
                    <a href="<?= url('admin/posts/index.php') ?>" class="btn btn-outline-secondary w-100 btn-sm">انصراف</a>
                </div>
            </div>

            <div class="meta-box">
                <div class="meta-box-header">دسته‌بندی</div>
                <div class="meta-box-body">
                    <select name="category_id" class="form-select">
                        <option value="">بدون دسته</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (int)($post['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="meta-box">
                <div class="meta-box-header">تنظیمات نمایش</div>
                <div class="meta-box-body">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="show_in_ticker" id="showTicker"
                               value="1" <?= !empty($post['show_in_ticker']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="showTicker">نمایش در تیکر خبر فوری</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="allow_comments" id="allowComments"
                               value="1" <?= !isset($post['allow_comments']) || $post['allow_comments'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="allowComments">امکان ثبت نظر</label>
                    </div>
                </div>
            </div>

            <div class="meta-box">
                <div class="meta-box-header">گالری تصاویر</div>
                <div class="meta-box-body">
                    <?php foreach ($gallery as $img): ?>
                    <div class="d-flex gap-2 align-items-center mb-2 border-bottom pb-2">
                        <img src="<?= e(UPLOAD_URL . '/' . $img['image']) ?>" width="56" height="42" style="object-fit:cover" alt="">
                        <label class="form-check-label small flex-grow-1">
                            <input type="checkbox" name="delete_gallery[]" value="<?= (int)$img['id'] ?>"> حذف
                        </label>
                    </div>
                    <?php endforeach; ?>
                    <label class="form-label small mt-2">افزودن تصاویر</label>
                    <input type="file" name="gallery[]" class="form-control form-control-sm" accept="image/*" multiple>
                </div>
            </div>

            <div class="meta-box">
                <div class="meta-box-header">تصویر شاخص</div>
                <div class="meta-box-body">
                    <?php if (!empty($post['featured_image'])): ?>
                        <img src="<?= e(UPLOAD_URL . '/' . $post['featured_image']) ?>" class="thumb-preview" alt="">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="remove_image" id="removeImage" value="1">
                            <label class="form-check-label small" for="removeImage">حذف تصویر</label>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="featured_image" class="form-control form-control-sm mt-2" accept="image/*">
                    <div class="form-text small text-muted">نسبت ۱۶:۹ — پس از آپلود به <?= FEATURED_IMAGE_WIDTH ?>×<?= FEATURED_IMAGE_HEIGHT ?> تنظیم می‌شود.</div>
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
            ['link', 'image'],
            ['clean']
        ]
    }
});
document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('content-input').value = quill.root.innerHTML;
});

(function () {
    var titleMax = <?= TITLE_MAX_LENGTH ?>;
    var excerptMax = <?= EXCERPT_MAX_LENGTH ?>;
    var titleEl = document.getElementById('post-title');
    var excerptEl = document.getElementById('post-excerpt');
    var titleCounter = document.getElementById('title-counter');
    var excerptCounter = document.getElementById('excerpt-counter');

    function updateCounter(el, counter, max) {
        if (!el || !counter) return;
        var len = (el.value || '').length;
        counter.textContent = len + ' / ' + max;
        counter.classList.toggle('text-danger', len >= max);
    }

    titleEl?.addEventListener('input', function () { updateCounter(titleEl, titleCounter, titleMax); });
    excerptEl?.addEventListener('input', function () { updateCounter(excerptEl, excerptCounter, excerptMax); });
    updateCounter(titleEl, titleCounter, titleMax);
    updateCounter(excerptEl, excerptCounter, excerptMax);
})();
</script>
<?php
$pageContent = ob_get_clean();
$pageTitle = $id ? 'ویرایش خبر' : 'افزودن خبر';
require dirname(__DIR__, 2) . '/templates/partials/admin-layout.php';
