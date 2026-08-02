<?php
?>
<article class="news-card">
    <a href="<?= url('news/' . $post['slug']) ?>">
        <?php if (!empty($post['featured_image'])): ?>
            <img src="<?= e(UPLOAD_URL . '/' . $post['featured_image']) ?>" class="news-card-img" alt="<?= e($post['title']) ?>">
        <?php else: ?>
            <div class="news-card-img placeholder-img"><i class="bi bi-image"></i></div>
        <?php endif; ?>
    </a>
    <div class="news-card-body">
        <?php if (!empty($post['category_name'])): ?>
            <div class="news-card-cat"><?= e($post['category_name']) ?></div>
        <?php endif; ?>
        <h3 class="news-card-title">
            <a href="<?= url('news/' . $post['slug']) ?>" class="text-dark"><?= e($post['title']) ?></a>
        </h3>
        <div class="news-card-meta">
            <i class="bi bi-calendar3"></i>
            <span><?= jalali_date($post['published_at'] ?? $post['created_at'], 'long') ?></span>
        </div>
    </div>
</article>
