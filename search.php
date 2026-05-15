<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (!is_installed()) {
    header('Location: ' . url('install.php'));
    exit;
}

$q = trim($_GET['q'] ?? '');
$navCategories = Category::forHeader();
$allCategories = Category::all();
$pageTitle = 'جستجو';
$results = [];

if ($q !== '') {
    $stmt = db()->prepare(
        "SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM posts p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.status = 'publish' AND (p.title LIKE ? OR p.excerpt LIKE ? OR p.content LIKE ?)
         ORDER BY p.published_at DESC LIMIT 30"
    );
    $like = '%' . $q . '%';
    $stmt->execute([$like, $like, $like]);
    $results = $stmt->fetchAll();
}

require __DIR__ . '/templates/partials/head.php';
require __DIR__ . '/templates/partials/header.php';
?>

<main class="container py-4">
    <h1 class="section-title">نتایج جستجو<?= $q !== '' ? ': «' . e($q) . '»' : '' ?></h1>

    <?php if ($q === ''): ?>
        <p class="text-muted">عبارت جستجو را وارد کنید.</p>
    <?php elseif (empty($results)): ?>
        <p class="text-muted">نتیجه‌ای یافت نشد.</p>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($results as $post): ?>
            <div class="col-md-4 col-sm-6">
                <?php include __DIR__ . '/templates/partials/news-card.php'; ?>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/templates/partials/footer.php'; ?>
