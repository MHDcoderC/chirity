<?php
/**
 * اسلایدر اخبار ویژه — صفحه اصلی
 * @var array $sliderPosts
 */
if (empty($sliderPosts)) {
    return;
}
$carouselId = 'newsHeroCarousel';
?>
<section class="news-slider-section mb-4" aria-label="اخبار ویژه">
    <div class="row g-0 news-slider-grid">
        <div class="col-lg-8">
            <div id="<?= e($carouselId) ?>" class="carousel slide carousel-fade news-hero-carousel"
                 data-bs-ride="carousel" data-bs-interval="6000" data-bs-pause="hover">
                <div class="carousel-indicators news-carousel-indicators">
                    <?php foreach ($sliderPosts as $i => $post): ?>
                    <button type="button"
                            data-bs-target="#<?= e($carouselId) ?>"
                            data-bs-slide-to="<?= $i ?>"
                            class="<?= $i === 0 ? 'active' : '' ?>"
                            aria-label="اسلاید <?= $i + 1 ?>"></button>
                    <?php endforeach; ?>
                </div>

                <div class="carousel-inner">
                    <?php foreach ($sliderPosts as $i => $post):
                        $slideTitle = truncate_text((string) $post['title'], TITLE_MAX_LENGTH);
                        $slideExcerpt = !empty($post['excerpt'])
                            ? truncate_text((string) $post['excerpt'], EXCERPT_SLIDER_LENGTH)
                            : '';
                    ?>
                    <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                        <a href="<?= url('news/' . $post['slug']) ?>" class="news-slide-link">
                            <div class="news-slide-media">
                                <?php if (!empty($post['featured_image'])): ?>
                                    <img src="<?= e(UPLOAD_URL . '/' . $post['featured_image']) ?>"
                                         class="news-slide-img"
                                         alt="<?= e($slideTitle) ?>"
                                         width="<?= FEATURED_IMAGE_WIDTH ?>"
                                         height="<?= FEATURED_IMAGE_HEIGHT ?>"
                                         loading="<?= $i === 0 ? 'eager' : 'lazy' ?>">
                                <?php else: ?>
                                    <div class="news-slide-img placeholder-img"><i class="bi bi-image"></i></div>
                                <?php endif; ?>
                            </div>
                            <div class="news-slide-overlay">
                                <?php if (!empty($post['category_name'])): ?>
                                    <span class="news-slide-cat">
                                        <i class="bi bi-bookmark-fill"></i>
                                        <?= e($post['category_name']) ?>
                                    </span>
                                <?php endif; ?>
                                <h2 class="news-slide-title"><?= e($slideTitle) ?></h2>
                                <?php if ($slideExcerpt !== ''): ?>
                                    <p class="news-slide-excerpt d-none d-md-block"><?= e($slideExcerpt) ?></p>
                                <?php endif; ?>
                                <div class="news-slide-meta">
                                    <span><i class="bi bi-calendar3"></i> <?= jalali_date($post['published_at'], 'time') ?></span>
                                    <span class="news-slide-more">
                                        ادامه مطلب <i class="bi bi-chevron-left"></i>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>

                <button class="carousel-control-prev news-carousel-control" type="button"
                        data-bs-target="#<?= e($carouselId) ?>" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">قبلی</span>
                </button>
                <button class="carousel-control-next news-carousel-control" type="button"
                        data-bs-target="#<?= e($carouselId) ?>" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">بعدی</span>
                </button>

                <div class="news-slide-progress" aria-hidden="true">
                    <span class="news-slide-progress-bar"></span>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="news-slider-thumbs" id="newsSliderThumbs">
                <div class="news-thumbs-header">
                    <i class="bi bi-lightning-charge-fill"></i> تیترهای ویژه
                </div>
                <?php foreach ($sliderPosts as $i => $post): ?>
                <button type="button"
                        class="news-thumb-item <?= $i === 0 ? 'active' : '' ?>"
                        data-bs-target="#<?= e($carouselId) ?>"
                        data-bs-slide-to="<?= $i ?>"
                        aria-label="<?= e(truncate_text((string) $post['title'], TITLE_MAX_LENGTH)) ?>">
                    <span class="news-thumb-num"><?= persian_digits((string) ($i + 1)) ?></span>
                    <?php if (!empty($post['featured_image'])): ?>
                        <img src="<?= e(UPLOAD_URL . '/' . $post['featured_image']) ?>" alt="" loading="lazy">
                    <?php else: ?>
                        <span class="news-thumb-placeholder"><i class="bi bi-image"></i></span>
                    <?php endif; ?>
                    <span class="news-thumb-body">
                        <?php if (!empty($post['category_name'])): ?>
                            <span class="news-thumb-cat"><?= e($post['category_name']) ?></span>
                        <?php endif; ?>
                        <span class="news-thumb-title"><?= e(truncate_text((string) $post['title'], 70)) ?></span>
                        <span class="news-thumb-date">
                            <i class="bi bi-clock"></i>
                            <?= jalali_date($post['published_at'], 'short') ?>
                        </span>
                    </span>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
