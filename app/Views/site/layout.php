<?php
/** @var string $content HTML hasil render view */
$page = is_array($page ?? null) ? $page : [];
$meta = \App\Services\Seo::build($page);
$active = (string) ($page['active'] ?? '');
$favicon = setting_image('favicon');
$name = \App\Services\Site::name();
$wa = \App\Services\Site::whatsappLink('Halo ' . $name . ', saya ingin berkonsultasi.');
$ldFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($meta['title']) ?></title>
    <?php if ($meta['description'] !== ''): ?>
        <meta name="description" content="<?= e($meta['description']) ?>">
    <?php endif; ?>
    <meta name="robots" content="<?= e($meta['robots']) ?>">
    <?php if ($meta['canonical'] !== null): ?>
        <link rel="canonical" href="<?= e($meta['canonical']) ?>">
        <meta property="og:url" content="<?= e($meta['canonical']) ?>">
    <?php endif; ?>
    <?php if ($favicon !== ''): ?>
        <link rel="icon" href="<?= e($favicon) ?>">
    <?php endif; ?>
    <?php if ($meta['verification'] !== ''): ?>
        <meta name="google-site-verification" content="<?= e($meta['verification']) ?>">
    <?php endif; ?>

    <meta property="og:site_name" content="<?= e($name) ?>">
    <meta property="og:locale" content="id_ID">
    <meta property="og:type" content="<?= e($meta['type']) ?>">
    <meta property="og:title" content="<?= e($meta['title']) ?>">
    <?php if ($meta['description'] !== ''): ?>
        <meta property="og:description" content="<?= e($meta['description']) ?>">
    <?php endif; ?>
    <?php if ($meta['image'] !== ''): ?>
        <meta property="og:image" content="<?= e($meta['image']) ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="<?= $meta['image'] !== '' ? 'summary_large_image' : 'summary' ?>">

    <?php foreach ($meta['jsonld'] as $schema): ?>
        <script type="application/ld+json"><?= json_encode($schema, $ldFlags) ?: '{}' ?></script>
    <?php endforeach; ?>

    <?= vite('resources/js/public.js') ?>
</head>
<body class="flex min-h-screen flex-col">
<a href="#konten"
   class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-ink focus:px-4 focus:py-2 focus:text-paper">
    Lewati ke konten
</a>

<?php $this->partial('site/partials/header', ['active' => $active]); ?>

<main id="konten" class="flex-1">
    <?php /* $content adalah HTML hasil render view, bukan input pengguna */ ?>
    <?= $content ?>
</main>

<?php $this->partial('site/partials/footer'); ?>

<?php if ($wa !== ''): ?>
    <a href="<?= e($wa) ?>" target="_blank" rel="noopener noreferrer"
       class="fixed bottom-5 right-5 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-deep text-white shadow-lg transition-colors hover:bg-ink"
       aria-label="Chat lewat WhatsApp">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
    </a>
<?php endif; ?>
</body>
</html>