<?php
/** @var array $item baris proyek dari Catalog */
$meta = array_filter([
    (string) ($item['category'] ?? ''),
    (string) ($item['location'] ?? ''),
    $item['completed_year'] !== null ? (string) $item['completed_year'] : '',
], static fn (string $v): bool => $v !== '');
?>
<a href="<?= e(url('/proyek/' . $item['slug'])) ?>" class="group block">
    <div class="aspect-[4/3] overflow-hidden rounded bg-line">
        <?php $this->partial('site/partials/img', [
            'image' => $item['image'] ?? null,
            'class' => 'h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]',
        ]); ?>
    </div>
    <?php if ($meta !== []): ?>
        <p class="eyebrow mt-4"><?= e(implode(' · ', $meta)) ?></p>
    <?php endif; ?>
    <h3 class="mt-2 text-heading transition-colors group-hover:text-accent"><?= e($item['title']) ?></h3>
    <?php if (trim((string) $item['summary']) !== ''): ?>
        <p class="mt-2 max-w-prose text-muted"><?= e($item['summary']) ?></p>
    <?php endif; ?>
</a>