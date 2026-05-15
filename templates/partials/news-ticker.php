<?php
$tickerItems = Post::ticker(12);
if (empty($tickerItems)) {
    return;
}
?>
<div class="news-ticker" aria-label="اخبار فوری">
    <div class="news-ticker-label">
        <i class="bi bi-broadcast"></i>
        <span>خبر فوری</span>
    </div>
    <div class="news-ticker-track-wrap">
        <div class="news-ticker-track">
            <?php foreach (array_merge($tickerItems, $tickerItems) as $item): ?>
            <a href="<?= url('news/' . $item['slug']) ?>" class="news-ticker-item">
                <?php if (!empty($item['category_name'])): ?>
                    <span class="news-ticker-cat"><?= e($item['category_name']) ?></span>
                <?php endif; ?>
                <span><?= e($item['title']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
