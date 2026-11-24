<?php
/** @var array<int, array{label: string, path: ?string}> $items */
$items = is_array($items ?? null) ? array_values($items) : [];
$last = count($items) - 1;
?>
<?php if ($items !== []): ?>
    <nav aria-label="Breadcrumb" class="text-sm text-muted">
        <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
            <?php foreach ($items as $i => $item): ?>
                <li class="flex items-center gap-2">
                    <?php if ($i < $last && !empty($item['path'])): ?>
                        <a href="<?= e(url((string) $item['path'])) ?>" class="underline-offset-4 hover:text-ink hover:underline"><?= e($item['label']) ?></a>
                        <span aria-hidden="true">/</span>
                    <?php else: ?>
                        <span class="text-ink" aria-current="page"><?= e($item['label']) ?></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>
<?php endif; ?>