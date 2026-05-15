<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
auth_check();

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    if (Category::delete($id)) {
        flash('success', 'دسته حذف شد.');
    } else {
        flash('error', 'این دسته دارای خبر است و قابل حذف نیست.');
    }
}
redirect('admin/categories/index.php');
