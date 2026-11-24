<?php
/** @var array $data @var array<int, array> $items */
$subtitle = trim((string) ($data['subtitle'] ?? ''));
?>
<section class="mt-section border-y border-line bg-surface" aria-labelledby="h-process">
    <div class="wrap py-14 md:py-20">
        <div class="max-w-2xl">
            <h2 id="h-process" class="text-title"><?= e($data['title']) ?></h2>
            <?php if ($subtitle !== ''): ?>
                <p class="mt-4 text-lg text-muted"><?= e($subtitle) ?></p>
            <?php endif; ?>
        </div>

        <ol class="mt-12 grid gap-x-8 gap-y-10 md:grid-cols-2 lg:grid-cols-4">
            <?php foreach ($items as $i => $item): ?>
                <li class="border-t border-line pt-5">
                    <span class="font-serif text-title text-accent"><?= e($i + 1) ?></span>
                    <h3 class="mt-3 text-heading"><?= e($item['title']) ?></h3>
                    <?php if (trim((string) ($item['text'] ?? '')) !== ''): ?>
                        <p class="mt-2 text-muted"><?= e($item['text']) ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>