<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (!is_installed()) {
    header('Location: ' . url('install.php'));
    exit;
}

$slug = trim($_GET['slug'] ?? '');
$category = $slug !== '' ? Category::findBySlug($slug) : null;

if (!$category) {
    redirect('');
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$list = Post::publishedList((int) $category['id'], $page);
$navCategories = Category::forHeader();
$allCategories = Category::all();
$pageTitle = $category['name'];
$activeSlug = $slug;
$activeId = (int) $category['id'];

require __DIR__ . '/templates/partials/head.php';
require __DIR__ . '/templates/partials/header.php';
?>

<main class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3 order-lg-1 order-2">
            <?php include __DIR__ . '/templates/partials/category-sidebar.php'; ?>
        </div>
        <div class="col-lg-9 order-lg-2 order-1">
            <h1 class="section-title"><?= e($category['name']) ?></h1>
            <?php if ($category['description']): ?>
                <p class="text-muted mb-4"><?= e($category['description']) ?></p>
            <?php endif; ?>

            <div class="row g-3">
                <?php foreach ($list['items'] as $post): ?>
                <div class="col-md-4 col-sm-6">
                    <?php include __DIR__ . '/templates/partials/news-card.php'; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($list['items'])): ?>
                <p class="text-muted py-4">در این دسته هنوز خبری منتشر نشده.</p>
            <?php endif; ?>

            <?php if ($list['pages'] > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center flex-wrap">
                    <?php for ($p = 1; $p <= $list['pages']; $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?slug=<?= e($slug) ?>&page=<?= $p ?>"><?= persian_digits((string)$p) ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require __DIR__ . '/templates/partials/footer.php'; ?>
