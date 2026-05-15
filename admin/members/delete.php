<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
auth_check();

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0 && CharityMember::find($id)) {
    CharityMember::delete($id);
    flash('success', 'عضو حذف شد.');
} else {
    flash('error', 'عضو یافت نشد.');
}

redirect('admin/members/index.php');
