<?php
/** @var array $data @var array<int, array> $items */
$subtitle = trim((string) ($data['subtitle'] ?? ''));
?>
<section class="wrap mt-section" aria-labelledby="h-advantages">
    <div class="max-w-2xl">
        <h2 id="h-advantages" class="text-title"><?= e($data['title']) ?></h2>
        <?php if ($subtitle !== ''): ?>
            <p class="mt-4 text-lg text-muted"><?= e($subtitle) ?></p>
        <?php endif; ?>
    </div>

    <ul class="mt-12 grid gap-x-10 gap-y-10 md:grid-cols-3">
        <?php foreach ($items as $item): ?>
            <li class="border-t-2 border-ink pt-5">
                <h3 class="text-heading"><?= e($item['title']) ?></h3>
                <?php if (trim((string) ($item['text'] ?? '')) !== ''): ?>
                    <p class="mt-3 text-muted"><?= e($item['text']) ?></p>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</section>