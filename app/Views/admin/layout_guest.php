<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title ?? 'Masuk') ?> | <?= e(config('app.name')) ?></title>
    <?= vite('resources/js/admin.js') ?>
</head>
<body class="flex min-h-screen flex-col items-center justify-center gap-6 px-4 py-10">
<div class="flex items-center gap-2 text-sm font-semibold">
    <span class="flex h-7 w-7 items-center justify-center rounded-md bg-accent text-white">
        <?= e(mb_strtoupper(mb_substr((string) config('app.name'), 0, 1))) ?>
    </span>
    <?= e(config('app.name')) ?>
</div>

<main class="w-full max-w-sm">
    <?php /* $content adalah HTML hasil render view, bukan input pengguna */ ?>
    <?= $content ?>
</main>

<?php $this->partial('admin/partials/toaster'); ?>
</body>
</html>