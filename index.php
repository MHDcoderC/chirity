<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (!is_installed()) {
    header('Location: ' . url('install.php'));
    exit;
}

$sliderPosts = Post::featured(6);
$sliderIds = array_column($sliderPosts, 'id');
$latest = Post::publishedList(null, 1, 12);
$latest['items'] = array_values(array_filter(
    $latest['items'],
    static fn(array $p): bool => !in_array((int) $p['id'], $sliderIds, true)
));
$latest['items'] = array_slice($latest['items'], 0, 9);

$homeCategories = Category::forHome();
$navCategories = Category::forHeader();

$pageTitle = 'صفحه اصلی';
$jehadiCall = JehadiCall::active();
require __DIR__ . '/templates/partials/head.php';
require __DIR__ . '/templates/partials/header.php';
?>

<?php if ($jehadiCall): ?>
    <?php include __DIR__ . '/templates/partials/jehadi-home-banner.php'; ?>
<?php endif; ?>

<main class="py-4">
    <div class="container">
        <?php include __DIR__ . '/templates/partials/news-slider.php'; ?>

        <div class="row g-4 mt-2">
            <div class="col-lg-8">
                <section>
                    <h2 class="section-title">آخرین اخبار</h2>
                    <div class="row g-3">
                        <?php foreach ($latest['items'] as $post): ?>
                        <div class="col-md-4 col-sm-6">
                            <?php include __DIR__ . '/templates/partials/news-card.php'; ?>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($latest['items'])): ?>
                            <p class="text-muted">هنوز خبری منتشر نشده است.</p>
                        <?php endif; ?>
                    </div>
                </section>

                <?php foreach ($homeCategories as $cat): ?>
                    <?php
                    $limit = max(1, (int) ($cat['home_posts_limit'] ?? 4));
                    $catPosts = Post::publishedInCategory((int) $cat['id'], $limit);
                    if (empty($catPosts)) {
                        continue;
                    }
                    ?>
                    <section class="mt-5">
                        <h2 class="section-title">
                            <?= e($cat['name']) ?>
                            <a href="<?= url('category/' . $cat['slug']) ?>" class="more-link">بیشتر <i class="bi bi-chevron-left"></i></a>
                        </h2>
                        <div class="row g-3">
                            <?php foreach ($catPosts as $post): ?>
                            <div class="col-md-3 col-6">
                                <?php include __DIR__ . '/templates/partials/news-card.php'; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>

                <?php if (empty($homeCategories)): ?>
                    <p class="text-muted small mt-3">
                        <i class="bi bi-info-circle"></i>
                        برای نمایش دسته‌ها در صفحه اصلی، از پنل در بخش دسته‌بندی گزینه «نمایش در صفحه اصلی» را فعال کنید.
                    </p>
                <?php endif; ?>
            </div>

            <aside class="col-lg-4">
                <div class="sidebar-box">
                    <h3 class="h6 section-title mb-0 border-0 pb-2">پربازدیدترین</h3>
                    <?php foreach (Post::featured(6) as $post): ?>
                    <a href="<?= url('news/' . $post['slug']) ?>" class="side-list-item text-decoration-none">
                        <?php if ($post['featured_image']): ?>
                            <img src="<?= e(UPLOAD_URL . '/' . $post['featured_image']) ?>" class="side-list-thumb" alt="">
                        <?php else: ?>
                            <div class="side-list-thumb placeholder-img"><i class="bi bi-image"></i></div>
                        <?php endif; ?>
                        <div>
                            <div class="side-list-title"><?= e($post['title']) ?></div>
                            <small class="text-muted"><i class="bi bi-calendar3"></i> <?= jalali_date($post['published_at'], 'short') ?></small>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </aside>
        </div>
    </div>
</main>

<?php
$extraJs = '<script src="' . asset('js/slider.js') . '"></script>';
require __DIR__ . '/templates/partials/footer.php';
