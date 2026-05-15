<?php
/** @var array $allCategories */
/** @var string|null $activeSlug */
/** @var int|null $activeId */
$allCategories = $allCategories ?? Category::all();
$activeSlug = $activeSlug ?? ($_GET['slug'] ?? '');
$currentScript = basename($_SERVER['SCRIPT_NAME']);
?>
<aside class="archive-sidebar">
    <div class="sidebar-box mb-3">
        <h3 class="sidebar-box-title"><i class="bi bi-grid"></i> همه اخبار</h3>
        <ul class="archive-cat-list">
            <li>
                <a href="<?= url('news.php') ?>"
                   class="<?= $currentScript === 'news.php' && $activeSlug === '' ? 'active' : '' ?>">
                    <i class="bi bi-list-ul"></i> آرشیو کامل
                </a>
            </li>
            <?php foreach ($allCategories as $cat): ?>
            <li>
                <a href="<?= url('category/' . $cat['slug']) ?>"
                   class="<?= ($activeSlug === $cat['slug'] || (isset($activeId) && (int)$activeId === (int)$cat['id'])) ? 'active' : '' ?>">
                    <i class="bi bi-folder2"></i>
                    <?= e($cat['name']) ?>
                    <span class="badge bg-light text-dark"><?= persian_digits((string)(int)$cat['post_count']) ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</aside>
