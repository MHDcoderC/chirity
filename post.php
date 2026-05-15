<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (!is_installed()) {
    header('Location: ' . url('install.php'));
    exit;
}

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    redirect('');
}

$post = Post::findBySlug($slug);
if (!$post) {
    http_response_code(404);
    $pageTitle = 'یافت نشد';
    $navCategories = Category::forHeader();
    require __DIR__ . '/templates/partials/head.php';
    require __DIR__ . '/templates/partials/header.php';
    echo '<main class="container py-5"><div class="alert alert-warning">خبر مورد نظر یافت نشد.</div></main>';
    require __DIR__ . '/templates/partials/footer.php';
    exit;
}

Post::incrementViews((int) $post['id']);
$navCategories = Category::forHeader();
$gallery = PostGallery::forPost((int) $post['id']);
$comments = Comment::forPost((int) $post['id']);
$pageTitle = $post['title'];

require __DIR__ . '/templates/partials/head.php';
require __DIR__ . '/templates/partials/header.php';
?>

<article class="article-single">
    <div class="container py-4">
        <div class="row g-4">
            <div class="col-lg-8">
                <header class="article-head">
                    <?php if ($post['category_name']): ?>
                        <a href="<?= url('category/' . $post['category_slug']) ?>" class="article-cat">
                            <?= e($post['category_name']) ?>
                        </a>
                    <?php endif; ?>
                    <h1 class="article-title"><?= e($post['title']) ?></h1>
                    <div class="article-meta">
                        <span><i class="bi bi-person"></i> <?= e($post['author_name']) ?></span>
                        <span><i class="bi bi-calendar3"></i> <?= jalali_date($post['published_at'], 'time') ?></span>
                        <span><i class="bi bi-eye"></i> <?= persian_digits(number_format((int) $post['view_count'])) ?> بازدید</span>
                    </div>
                </header>

                <?php if ($post['featured_image']): ?>
                <figure class="article-featured">
                    <img src="<?= e(UPLOAD_URL . '/' . $post['featured_image']) ?>"
                         alt="<?= e($post['title']) ?>" width="<?= FEATURED_IMAGE_WIDTH ?>" height="<?= FEATURED_IMAGE_HEIGHT ?>">
                </figure>
                <?php endif; ?>

                <?php if ($post['excerpt']): ?>
                    <p class="article-excerpt"><?= e($post['excerpt']) ?></p>
                <?php endif; ?>

                <div class="article-content">
                    <?= sanitize_html($post['content']) ?>
                </div>

                <?php include __DIR__ . '/templates/partials/post-gallery.php'; ?>
                <?php include __DIR__ . '/templates/partials/post-comments.php'; ?>
            </div>
            <div class="col-lg-4">
                <?php
                $activeSlug = $post['category_slug'] ?? '';
                include __DIR__ . '/templates/partials/category-sidebar.php';
                ?>
            </div>
        </div>
    </div>
</article>

<?php require __DIR__ . '/templates/partials/footer.php'; ?>
