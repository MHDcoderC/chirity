<?php
$script = basename($_SERVER['SCRIPT_NAME']);
$dir = basename(dirname($_SERVER['SCRIPT_NAME']));
$settingsDir = $dir === 'settings';
?>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-brand">
        <i class="bi bi-grid-1x2-fill"></i> پنل <?= e(site_name()) ?>
    </div>
    <ul class="admin-menu">
        <li class="menu-label">اصلی</li>
        <li>
            <a href="<?= url('admin/index.php') ?>" class="<?= $script === 'index.php' && $dir === 'admin' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> پیشخوان
            </a>
        </li>
        <li class="menu-label">محتوا</li>
        <li>
            <a href="<?= url('admin/posts/index.php') ?>" class="<?= $dir === 'posts' ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-text"></i> همه اخبار
            </a>
        </li>
        <li>
            <a href="<?= url('admin/posts/form.php') ?>">
                <i class="bi bi-plus-circle"></i> افزودن خبر
            </a>
        </li>
        <li>
            <a href="<?= url('admin/categories/index.php') ?>" class="<?= $dir === 'categories' ? 'active' : '' ?>">
                <i class="bi bi-folder2-open"></i> دسته‌بندی‌ها
            </a>
        </li>
        <li>
            <a href="<?= url('admin/comments/index.php') ?>" class="<?= $dir === 'comments' ? 'active' : '' ?>">
                <i class="bi bi-chat-square-text"></i> نظرات
            </a>
        </li>
        <li>
            <a href="<?= url('admin/members/index.php') ?>" class="<?= $dir === 'members' ? 'active' : '' ?>">
                <i class="bi bi-people"></i> اعضای خیریه
            </a>
        </li>
        <li class="menu-label">جهادی</li>
        <li>
            <a href="<?= url('admin/jehadi/index.php') ?>" class="<?= $dir === 'jehadi' ? 'active' : '' ?>">
                <i class="bi bi-megaphone"></i> گروه جهادی
            </a>
        </li>
        <li>
            <a href="<?= url('admin/jehadi/registrations.php') ?>" class="<?= $script === 'registrations.php' && $dir === 'jehadi' ? 'active' : '' ?>">
                <i class="bi bi-person-lines-fill"></i> ثبت‌نام جهادی
            </a>
        </li>
        <li class="menu-label">تنظیمات</li>
        <li>
            <a href="<?= url('admin/settings/site.php') ?>" class="<?= $settingsDir && $script === 'site.php' ? 'active' : '' ?>">
                <i class="bi bi-sliders"></i> تنظیمات سایت
            </a>
        </li>
        <li>
            <a href="<?= url('admin/settings/donation.php') ?>" class="<?= $settingsDir && $script === 'donation.php' ? 'active' : '' ?>">
                <i class="bi bi-credit-card"></i> کمک مالی
            </a>
        </li>
        <li class="menu-label">سایت</li>
        <li>
            <a href="<?= url() ?>" target="_blank">
                <i class="bi bi-box-arrow-up-left"></i> مشاهده سایت
            </a>
        </li>
        <li>
            <a href="<?= url('admin/logout.php') ?>">
                <i class="bi bi-box-arrow-right"></i> خروج
            </a>
        </li>
    </ul>
    <div class="px-3 py-3 mt-auto border-top border-secondary border-opacity-25 small text-muted" style="font-size:.7rem">
        <i class="bi bi-code-square"></i> <?= kh_dev_link_html('text-muted') ?>
    </div>
</aside>
