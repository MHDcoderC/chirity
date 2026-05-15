<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('');
}

csrf_verify();

$postId = (int) ($_POST['post_id'] ?? 0);
$slug = trim($_POST['post_slug'] ?? '');
$name = trim($_POST['author_name'] ?? '');
$email = trim($_POST['author_email'] ?? '');
$body = trim($_POST['body'] ?? '');
$parentId = (int) ($_POST['parent_id'] ?? 0) ?: null;

$redirectTo = $slug !== '' ? url('news/' . $slug) : url();

if ($postId < 1 || $name === '' || mb_strlen($body) < 3) {
    flash('error', 'نام و متن نظر (حداقل ۳ کاراکتر) الزامی است.');
    redirect($redirectTo . '#comments');
}

$post = Post::find($postId);
if (!$post || $post['status'] !== 'publish' || empty($post['allow_comments'])) {
    flash('error', 'امکان ثبت نظر برای این مطلب وجود ندارد.');
    redirect($redirectTo);
}

if ($parentId) {
    $parent = Comment::find($parentId);
    if (!$parent || (int) $parent['post_id'] !== $postId) {
        flash('error', 'پاسخ به نظر نامعتبر است.');
        redirect($redirectTo . '#comments');
    }
}

$key = 'comment_last_' . $postId . ($parentId ? '_' . $parentId : '');
if (isset($_SESSION[$key]) && time() - $_SESSION[$key] < 60) {
    flash('error', 'لطفاً یک دقیقه صبر کنید و دوباره تلاش کنید.');
    redirect($redirectTo . '#comments');
}

Comment::create($postId, $name, $email, $body, $parentId);
$_SESSION[$key] = time();

$msg = $parentId
    ? 'پاسخ شما ثبت شد و پس از تأیید نمایش داده می‌شود.'
    : 'نظر شما ثبت شد و پس از تأیید نمایش داده می‌شود.';
flash('success', $msg);
redirect($redirectTo . ($parentId ? '#comment-' . $parentId : '#comments'));
