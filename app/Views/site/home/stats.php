<?php
/** @var array $data @var array<int, array> $items */
$title = trim((string) ($data['title'] ?? ''));
?>
<section class="mt-section bg-deep text-paper" <?= $title !== '' ? 'aria-labelledby="h-stats"' : 'aria-label="Pencapaian"' ?>>
    <div class="wrap py-14 md:py-20">
        <?php if ($title !== ''): ?>
            <h2 id="h-stats" class="max-w-2xl text-title text-paper"><?= e($title) ?></h2>
        <?php endif; ?>

        <dl class="<?= $title !== '' ? 'mt-12' : '' ?> grid grid-cols-2 gap-x-6 gap-y-10 lg:grid-cols-4">
            <?php foreach ($items as $item): ?>
                <div class="border-t border-paper/30 pt-5">
                    <dt class="order-2 mt-2 text-paper/70"><?= e($item['label']) ?></dt>
                    <dd class="font-serif text-title"><?= e($item['value']) ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    </div>
</section>