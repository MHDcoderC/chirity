<?php
$navCategories = $navCategories ?? Category::forHeader();
$allCategories = Category::all();
$currentSlug = $_GET['slug'] ?? '';
$scriptBase = basename($_SERVER['SCRIPT_NAME'] ?? '');
$isHome = $scriptBase === 'index.php';
$isNewsArchive = $scriptBase === 'news.php';
?>
<header class="site-header">
    <?php include __DIR__ . '/news-ticker.php'; ?>
    <div class="site-header-top">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span><i class="bi bi-calendar3"></i> <?= jalali_today('full') ?></span>
            <span><i class="bi bi-heart-pulse"></i> <?= e(site_name()) ?></span>
        </div>
    </div>
    <div class="container py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <a href="<?= url() ?>" class="site-logo">
                <span class="site-logo-icon"><i class="bi bi-heart-pulse-fill"></i></span>
                <span>
                    <?= e(site_name()) ?>
                    <?php if (site_tagline() !== ''): ?>
                    <small class="site-logo-sub"><?= e(site_tagline()) ?></small>
                    <?php endif; ?>
                </span>
            </a>
            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                <?php if (donation_is_configured()): ?>
                <button type="button" class="btn-donation-header" data-bs-toggle="modal" data-bs-target="#donationModal" title="کمک مالی" aria-label="کمک مالی">
                    <i class="bi bi-credit-card-2" aria-hidden="true"></i>
                    <span>کمک مالی</span>
                </button>
                <?php endif; ?>
                <form action="<?= url('search.php') ?>" method="get" class="site-search d-none d-md-flex" style="max-width:300px;width:100%">
                    <div class="input-group input-group-sm">
                        <input type="search" name="q" class="form-control" placeholder="جستجو..." value="<?= e($_GET['q'] ?? '') ?>">
                        <button class="btn btn-danger" type="submit" aria-label="جستجو">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <nav class="site-nav">
        <div class="container">
            <ul class="nav flex-wrap">
                <li class="nav-item">
                    <a class="nav-link <?= $isHome ? 'active' : '' ?>" href="<?= url() ?>">
                        <i class="bi bi-house"></i> صفحه اصلی
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isNewsArchive ? 'active' : '' ?>" href="<?= url('news.php') ?>">
                        <i class="bi bi-newspaper"></i> همه اخبار
                    </a>
                </li>
                <?php foreach ($navCategories as $cat): ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentSlug === $cat['slug']) ? 'active' : '' ?>"
                       href="<?= url('category/' . $cat['slug']) ?>">
                        <i class="bi bi-folder2"></i> <?= e($cat['name']) ?>
                    </a>
                </li>
                <?php endforeach; ?>
                <?php if (donation_is_configured()): ?>
                <li class="nav-item d-md-none">
                    <button type="button" class="nav-link border-0 bg-transparent btn-donation-nav"
                            data-bs-toggle="modal" data-bs-target="#donationModal">
                        <i class="bi bi-credit-card-2"></i> کمک مالی
                    </button>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
</header>
