<?php
/** @var array $data @var array<int, array> $items */
$subtitle = trim((string) ($data['subtitle'] ?? ''));
?>
<section class="wrap mt-section" aria-labelledby="h-portfolio">
    <div class="flex flex-wrap items-end justify-between gap-6">
        <div class="max-w-2xl">
            <h2 id="h-portfolio" class="text-title"><?= e($data['title']) ?></h2>
            <?php if ($subtitle !== ''): ?>
                <p class="mt-4 text-lg text-muted"><?= e($subtitle) ?></p>
            <?php endif; ?>
        </div>
        <a href="<?= e(url('/proyek')) ?>"
           class="inline-flex items-center gap-2 border-b-2 border-accent pb-1 font-medium hover:text-accent">
            Semua proyek <span aria-hidden="true">→</span>
        </a>
    </div>

    <ul class="mt-12 grid gap-x-6 gap-y-10 md:grid-cols-2">
        <?php foreach ($items as $i => $item): ?>
            <?php
            $first = $i === 0 && count($items) > 1;
            $meta = array_filter([
                (string) ($item['category'] ?? ''),
                (string) ($item['location'] ?? ''),
                $item['completed_year'] !== null ? (string) $item['completed_year'] : '',
            ], static fn (string $v): bool => $v !== '');
            $img = $item['image'] ?? null;
            ?>
            <li class="<?= $first ? 'md:col-span-2' : '' ?>">
                <a href="<?= e(url('/proyek/' . $item['slug'])) ?>" class="group block">
                    <div class="<?= $first ? 'aspect-[16/9]' : 'aspect-[4/3]' ?> overflow-hidden rounded bg-line">
                        <?php if ($img !== null): ?>
                            <img src="<?= e($img['url']) ?>" alt="<?= e($img['alt']) ?>"
                                 <?= $img['width'] > 0 ? 'width="' . e($img['width']) . '" height="' . e($img['height']) . '"' : '' ?>
                                 loading="lazy" decoding="async"
                                 class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]">
                        <?php endif; ?>
                    </div>
                    <?php if ($meta !== []): ?>
                        <p class="eyebrow mt-4"><?= e(implode(' · ', $meta)) ?></p>
                    <?php endif; ?>
                    <h3 class="mt-2 text-heading transition-colors group-hover:text-accent"><?= e($item['title']) ?></h3>
                    <?php if (trim((string) $item['summary']) !== ''): ?>
                        <p class="mt-2 max-w-prose text-muted"><?= e($item['summary']) ?></p>
                    <?php endif; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>