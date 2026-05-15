<?php
/** @var string $pageTitle */
/** @var string $pageContent */
$pageTitle = $pageTitle ?? 'پنل مدیریت';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | پنل <?= e(site_name()) ?></title>
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/css/bootstrap.rtl.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/fonts.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/quill/quill.snow.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="admin-body">
<div class="admin-wrap">
    <?php require __DIR__ . '/admin-sidebar.php'; ?>
    <main class="admin-main">
        <div class="admin-topbar">
            <button class="btn btn-sm btn-outline-secondary d-lg-none" type="button" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="h5 mb-0"><?= e($pageTitle) ?></h1>
            <span class="text-muted small">
                <i class="bi bi-person-circle"></i>
                <?= e(auth_user()['display_name'] ?? '') ?>
            </span>
        </div>
        <div class="admin-content">
            <?php
            $ok = flash('success');
            $err = flash('error');
            if ($ok): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= e($ok) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif;
            if ($err): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= e($err) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif;
            echo $pageContent;
            ?>
        </div>
    </main>
</div>
<script src="<?= asset('vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script>
document.getElementById('sidebarToggle')?.addEventListener('click', function() {
    document.getElementById('adminSidebar').classList.toggle('show');
});
</script>
<p class="admin-dev-footer mb-0">
    <?= e(site_name()) ?> · <?= e(dev_credit_line()) ?>
</p>
</body>
</html>
