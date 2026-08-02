<?php
if (empty($jehadiCall) || ($jehadiCall['status'] ?? '') !== 'active') {
    return;
}
?>
<section class="jehadi-banner-wrap" aria-label="فراخوان گروه جهادی">
    <div class="container">
        <a href="<?= url('jehadi.php') ?>" class="jehadi-banner-card">
            <img src="<?= e(JEHADI_UPLOAD_URL . '/' . $jehadiCall['banner']) ?>"
                 alt="<?= e($jehadiCall['title']) ?>"
                 width="<?= JEHADI_BANNER_WIDTH ?>"
                 height="<?= JEHADI_BANNER_HEIGHT ?>"
                 loading="eager">
            <span class="jehadi-banner-cta">
                <i class="bi bi-megaphone"></i> مشاهده فراخوان و ثبت‌نام
            </span>
        </a>
    </div>
</section>
