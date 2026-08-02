<?php
$allow = !empty($post['allow_comments']);
?>
<section id="comments" class="post-comments-section mt-5">
    <h2 class="h5 section-title">
        <i class="bi bi-chat-dots"></i>
        نظرات
        <span class="badge bg-secondary"><?= persian_digits((string) count($comments)) ?></span>
    </h2>

    <?php if ($ok = flash('success')): ?>
        <div class="alert alert-success py-2"><?= e($ok) ?></div>
    <?php endif; ?>
    <?php if ($err = flash('error')): ?>
        <div class="alert alert-danger py-2"><?= e($err) ?></div>
    <?php endif; ?>

    <?php if ($allow): ?>
    <form method="post" action="<?= url('comment-submit.php') ?>" id="comment-new" class="comment-form card border mb-4">
        <div class="card-body">
            <h3 class="h6 mb-3">ثبت نظر</h3>
            <?= csrf_field() ?>
            <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
            <input type="hidden" name="post_slug" value="<?= e($post['slug']) ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">نام *</label>
                    <input type="text" name="author_name" class="form-control" required maxlength="100">
                </div>
                <div class="col-md-6">
                    <label class="form-label">ایمیل (اختیاری)</label>
                    <input type="email" name="author_email" class="form-control" maxlength="120">
                </div>
                <div class="col-12">
                    <label class="form-label">متن نظر *</label>
                    <textarea name="body" class="form-control" rows="4" required minlength="3"></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-send"></i> ارسال نظر
                    </button>
                </div>
            </div>
        </div>
    </form>
    <?php else: ?>
    <p class="text-muted small">ثبت نظر برای این مطلب غیرفعال است.</p>
    <?php endif; ?>

    <?php if (empty($comments)): ?>
        <p class="text-muted">هنوز نظری تأیید نشده است.</p>
    <?php else: ?>
        <ul class="comment-list list-unstyled">
            <?php foreach ($comments as $c): ?>
            <li class="comment-item" id="comment-<?= (int) $c['id'] ?>">
                <div class="comment-avatar"><i class="bi bi-person-circle"></i></div>
                <div class="comment-body">
                    <div class="comment-meta">
                        <strong><?= e($c['author_name']) ?></strong>
                        <span><?= jalali_date($c['created_at'], 'time') ?></span>
                    </div>
                    <p><?= nl2br(e($c['body'])) ?></p>

                    <?php if ($allow): ?>
                    <button type="button" class="btn btn-link btn-sm comment-reply-toggle px-0"
                            data-reply-for="<?= (int) $c['id'] ?>">
                        <i class="bi bi-reply"></i> پاسخ
                    </button>
                    <form method="post" action="<?= url('comment-submit.php') ?>"
                          class="comment-reply-form card border mt-2 d-none"
                          id="reply-form-<?= (int) $c['id'] ?>">
                        <div class="card-body py-3">
                            <h4 class="h6 mb-2">پاسخ به <?= e($c['author_name']) ?></h4>
                            <?= csrf_field() ?>
                            <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
                            <input type="hidden" name="post_slug" value="<?= e($post['slug']) ?>">
                            <input type="hidden" name="parent_id" value="<?= (int) $c['id'] ?>">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <input type="text" name="author_name" class="form-control form-control-sm"
                                           placeholder="نام *" required maxlength="100">
                                </div>
                                <div class="col-md-6">
                                    <input type="email" name="author_email" class="form-control form-control-sm"
                                           placeholder="ایمیل (اختیاری)" maxlength="120">
                                </div>
                                <div class="col-12">
                                    <textarea name="body" class="form-control form-control-sm" rows="3"
                                              placeholder="متن پاسخ *" required minlength="3"></textarea>
                                </div>
                                <div class="col-12 d-flex gap-2">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="bi bi-send"></i> ارسال پاسخ
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm comment-reply-cancel"
                                            data-reply-for="<?= (int) $c['id'] ?>">انصراف</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <?php endif; ?>

                    <?php if (!empty($c['replies'])): ?>
                    <ul class="comment-replies list-unstyled">
                        <?php foreach ($c['replies'] as $r): ?>
                        <li class="comment-item comment-reply" id="comment-<?= (int) $r['id'] ?>">
                            <div class="comment-body">
                                <div class="comment-meta">
                                    <strong><?= e($r['author_name']) ?></strong>
                                    <span><?= jalali_date($r['created_at'], 'time') ?></span>
                                </div>
                                <p><?= nl2br(e($r['body'])) ?></p>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<?php if ($allow): ?>
<script>
(function () {
    document.querySelectorAll('.comment-reply-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-reply-for');
            document.querySelectorAll('.comment-reply-form').forEach(function (f) {
                f.classList.add('d-none');
            });
            var form = document.getElementById('reply-form-' + id);
            if (form) {
                form.classList.remove('d-none');
                form.querySelector('textarea')?.focus();
            }
        });
    });
    document.querySelectorAll('.comment-reply-cancel').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-reply-for');
            document.getElementById('reply-form-' + id)?.classList.add('d-none');
        });
    });
    if (location.hash && location.hash.indexOf('comment-') === 1) {
        document.querySelector(location.hash)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
})();
</script>
<?php endif; ?>
