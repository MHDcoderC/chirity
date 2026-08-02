<?php
if (empty($gallery)) {
    return;
}
$gid = 'postGallery' . bin2hex(random_bytes(3));
?>
<section class="post-gallery-section mb-4">
    <h2 class="h6 section-title border-0"><i class="bi bi-images"></i> گالری تصاویر</h2>
    <div class="row g-2 post-gallery-grid">
        <?php foreach ($gallery as $i => $img): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <button type="button" class="post-gallery-thumb"
                    data-bs-toggle="modal" data-bs-target="#<?= e($gid) ?>"
                    data-bs-slide-to="<?= $i ?>">
                <img src="<?= e(UPLOAD_URL . '/' . $img['image']) ?>" alt="<?= e($img['caption'] ?? '') ?>" loading="lazy">
                <?php if (!empty($img['caption'])): ?>
                    <span class="post-gallery-caption"><?= e($img['caption']) ?></span>
                <?php endif; ?>
            </button>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="modal fade post-gallery-modal" id="<?= e($gid) ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content bg-dark border-0">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div id="<?= e($gid) ?>Carousel" class="carousel slide" data-bs-interval="false">
                    <div class="carousel-inner">
                        <?php foreach ($gallery as $i => $img): ?>
                        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                            <img src="<?= e(UPLOAD_URL . '/' . $img['image']) ?>" class="d-block w-100" alt="">
                            <?php if (!empty($img['caption'])): ?>
                                <div class="carousel-caption"><p><?= e($img['caption']) ?></p></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($gallery) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#<?= e($gid) ?>Carousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#<?= e($gid) ?>Carousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
document.querySelectorAll('.post-gallery-thumb').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var modal = document.getElementById('<?= e($gid) ?>');
        var carousel = modal.querySelector('.carousel');
        if (carousel && typeof bootstrap !== 'undefined') {
            bootstrap.Carousel.getOrCreateInstance(carousel).to(parseInt(btn.dataset.bsSlideTo, 10));
        }
    });
});
</script>
