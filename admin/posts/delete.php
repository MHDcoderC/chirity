<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
auth_check();

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    Post::delete($id);
    flash('success', 'خبر حذف شد.');
}
redirect('admin/posts/index.php');
