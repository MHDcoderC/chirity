<?php
$pageTitle = $pageTitle ?? site_name();
$extraCss = $extraCss ?? '';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | <?= e(site_name()) ?></title>
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/css/bootstrap.rtl.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/fonts.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <?= $extraCss ?>
</head>
<body>
<script>window.KH_BASE = <?= json_encode(APP_URL === '' ? '' : rtrim(APP_URL, '/'), JSON_UNESCAPED_UNICODE) ?>;</script>
