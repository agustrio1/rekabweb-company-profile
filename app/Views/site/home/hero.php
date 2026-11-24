<?php
/** @var array $data @var array|null $image */
$eyebrow = trim((string) ($data['eyebrow'] ?? ''));
$description = trim((string) ($data['description'] ?? ''));
$ctaLabel = trim((string) ($data['cta_label'] ?? ''));
$secLabel = trim((string) ($data['secondary_label'] ?? ''));
?>
<section class="wrap pt-10 md:pt-16" aria-labelledby="h-hero">
    <div class="grid items-center gap-10 lg:grid-cols-12 lg:gap-14">
        <div class="<?= $image !== null ? 'lg:col-span-7' : 'lg:col-span-10' ?>">
            <?php if ($eyebrow !== ''): ?>
                <p class="eyebrow"><?= e($eyebrow) ?></p>
            <?php endif; ?>

            <h1 id="h-hero" class="<?= $eyebrow !== '' ? 'mt-4' : '' ?> text-display"><?= e($data['headline']) ?></h1>

            <?php if ($description !== ''): ?>
                <p class="mt-6 max-w-prose text-lg text-muted"><?= e($description) ?></p>
            <?php endif; ?>

            <?php if ($ctaLabel !== '' || $secLabel !== ''): ?>
                <div class="mt-9 flex flex-wrap items-center gap-4">
                    <?php if ($ctaLabel !== ''): ?>
                        <a href="<?= e(\App\Services\Site::link((string) $data['cta_url'])) ?>" class="btn btn-primary"><?= e($ctaLabel) ?></a>
                    <?php endif; ?>
                    <?php if ($secLabel !== ''): ?>
                        <a href="<?= e(\App\Services\Site::link((string) $data['secondary_url'])) ?>" class="btn btn-outline"><?= e($secLabel) ?></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($image !== null): ?>
            <div class="lg:col-span-5">
                <div class="aspect-[4/5] overflow-hidden rounded bg-line">
                    <img src="<?= e($image['url']) ?>" alt="<?= e($image['alt']) ?>"
                         <?= $image['width'] > 0 ? 'width="' . e($image['width']) . '" height="' . e($image['height']) . '"' : '' ?>
                         fetchpriority="high" decoding="async" class="h-full w-full object-cover">
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>