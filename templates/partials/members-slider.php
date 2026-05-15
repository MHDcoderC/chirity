<?php
$members = CharityMember::active();
if (empty($members)) {
    return;
}
?>
<section class="members-section" aria-label="اعضای خیریه">
    <div class="container">
        <div class="members-section-head">
            <h2 class="members-section-title">
                <span class="members-title-line"></span>
                اعضای خیریه
            </h2>
            <p class="members-section-sub text-muted mb-0">همراهان و مدیران <?= e(site_name()) ?></p>
        </div>
        <div id="membersCarousel" class="carousel slide members-carousel" data-bs-ride="carousel"
             data-bs-interval="5000" data-bs-pause="hover">
            <div class="carousel-inner">
                <?php
                $chunks = array_chunk($members, 4);
                foreach ($chunks as $i => $chunk):
                ?>
                <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                    <div class="row g-4 justify-content-center members-row">
                        <?php foreach ($chunk as $m): ?>
                        <div class="col-6 col-md-3">
                            <article class="member-card">
                                <div class="member-photo-wrap">
                                    <img src="<?= e(MEMBER_UPLOAD_URL . '/' . $m['photo']) ?>"
                                         alt="<?= e($m['name']) ?>"
                                         class="member-photo"
                                         loading="lazy"
                                         width="160"
                                         height="160">
                                </div>
                                <h3 class="member-name"><?= e($m['name']) ?></h3>
                                <?php if ($m['position'] !== ''): ?>
                                <p class="member-position"><?= e($m['position']) ?></p>
                                <?php endif; ?>
                            </article>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($chunks) > 1): ?>
            <button class="carousel-control-prev members-control" type="button"
                    data-bs-target="#membersCarousel" data-bs-slide="prev" aria-label="قبلی">
                <span class="members-control-icon"><i class="bi bi-chevron-right"></i></span>
            </button>
            <button class="carousel-control-next members-control" type="button"
                    data-bs-target="#membersCarousel" data-bs-slide="next" aria-label="بعدی">
                <span class="members-control-icon"><i class="bi bi-chevron-left"></i></span>
            </button>
            <div class="carousel-indicators members-indicators">
                <?php foreach ($chunks as $i => $_): ?>
                <button type="button" data-bs-target="#membersCarousel"
                        data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>"
                        aria-label="اسلاید <?= $i + 1 ?>"></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
