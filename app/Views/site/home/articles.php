<?php
/** @var array $data @var array<int, array> $items */
$subtitle = trim((string) ($data['subtitle'] ?? ''));
?>
<section class="wrap mt-section" aria-labelledby="h-articles">
    <div class="flex flex-wrap items-end justify-between gap-6">
        <div class="max-w-2xl">
            <h2 id="h-articles" class="text-title"><?= e($data['title']) ?></h2>
            <?php if ($subtitle !== ''): ?>
                <p class="mt-4 text-lg text-muted"><?= e($subtitle) ?></p>
            <?php endif; ?>
        </div>
        <a href="<?= e(url('/blog')) ?>"
           class="inline-flex items-center gap-2 border-b-2 border-accent pb-1 font-medium hover:text-accent">
            Semua artikel <span aria-hidden="true">→</span>
        </a>
    </div>

    <ul class="mt-12 border-t border-line">
        <?php foreach ($items as $item): ?>
            <?php
            $img = $item['image'] ?? null;
            $date = \App\Services\SiteContent::dateId((string) $item['published_at']);
            $meta = array_filter([$date, (string) ($item['category'] ?? '')], static fn (string $v): bool => $v !== '');
            ?>
            <li class="border-b border-line">
                <a href="<?= e(url('/blog/' . $item['slug'])) ?>"
                   class="group grid gap-5 py-7 sm:grid-cols-[14rem_1fr] sm:gap-8">
                    <div class="aspect-[16/10] overflow-hidden rounded bg-line">
                        <?php if ($img !== null): ?>
                            <img src="<?= e($img['url']) ?>" alt="<?= e($img['alt']) ?>"
                                 <?= $img['width'] > 0 ? 'width="' . e($img['width']) . '" height="' . e($img['height']) . '"' : '' ?>
                                 loading="lazy" decoding="async" class="h-full w-full object-cover">
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0">
                        <?php if ($meta !== []): ?>
                            <p class="eyebrow"><?= e(implode(' · ', $meta)) ?></p>
                        <?php endif; ?>
                        <h3 class="mt-2 text-heading transition-colors group-hover:text-accent"><?= e($item['title']) ?></h3>
                        <?php if (trim((string) $item['summary']) !== ''): ?>
                            <p class="mt-2 max-w-prose text-muted"><?= e($item['summary']) ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>