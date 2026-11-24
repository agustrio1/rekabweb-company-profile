<?php
/** @var array|null $image  hasil SiteContent::image() */
/** @var string     $class  class CSS gambar */
/** @var bool       $lazy   true = lazy load, false = prioritas tinggi (gambar paling atas) */
$image = is_array($image ?? null) ? $image : null;
$lazy = $lazy ?? true;
?>
<?php if ($image !== null): ?>
    <img src="<?= e($image['url']) ?>" alt="<?= e($image['alt']) ?>"
         <?= $image['width'] > 0 ? 'width="' . e($image['width']) . '" height="' . e($image['height']) . '"' : '' ?>
         <?= $lazy ? 'loading="lazy"' : 'fetchpriority="high"' ?> decoding="async"
         class="<?= e($class ?? 'h-full w-full object-cover') ?>">
<?php endif; ?>