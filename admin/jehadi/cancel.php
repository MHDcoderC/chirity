<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
auth_check();

$id = (int) ($_GET['id'] ?? 0);
$call = $id > 0 ? JehadiCall::find($id) : null;

if ($call && ($call['status'] ?? '') === 'active') {
    JehadiCall::cancel($id);
    flash('success', 'فراخوان لغو شد و از سایت حذف می‌شود.');
} else {
    flash('error', 'فراخوان فعالی برای لغو یافت نشد.');
}

redirect('admin/jehadi/index.php');
