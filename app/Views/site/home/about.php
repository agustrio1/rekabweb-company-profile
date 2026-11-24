<?php
/** @var array $data @var array|null $image */
$text = trim((string) ($data['text'] ?? ''));
$linkLabel = trim((string) ($data['link_label'] ?? ''));
?>
<section class="wrap mt-section" aria-labelledby="h-about">
    <div class="grid items-center gap-10 lg:grid-cols-12 lg:gap-16">
        <?php if ($image !== null): ?>
            <div class="lg:col-span-5">
                <div class="aspect-[4/3] overflow-hidden rounded bg-line">
                    <img src="<?= e($image['url']) ?>" alt="<?= e($image['alt']) ?>"
                         <?= $image['width'] > 0 ? 'width="' . e($image['width']) . '" height="' . e($image['height']) . '"' : '' ?>
                         loading="lazy" decoding="async" class="h-full w-full object-cover">
                </div>
            </div>
        <?php endif; ?>

        <div class="<?= $image !== null ? 'lg:col-span-7' : 'lg:col-span-9' ?>">
            <h2 id="h-about" class="text-title"><?= e($data['title']) ?></h2>
            <?php if ($text !== ''): ?>
                <p class="mt-6 max-w-prose whitespace-pre-line text-lg text-muted"><?= e($text) ?></p>
            <?php endif; ?>
            <?php if ($linkLabel !== ''): ?>
                <a href="<?= e(\App\Services\Site::link((string) $data['link_url'])) ?>"
                   class="mt-8 inline-flex items-center gap-2 border-b-2 border-accent pb-1 font-medium hover:text-accent">
                    <?= e($linkLabel) ?> <span aria-hidden="true">→</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>