<?php
/** @var array $data @var array<int, array> $items */
$subtitle = trim((string) ($data['subtitle'] ?? ''));
?>
<section class="wrap mt-section" aria-labelledby="h-services">
    <div class="grid gap-10 lg:grid-cols-12 lg:gap-16">
        <div class="lg:col-span-4">
            <h2 id="h-services" class="text-title"><?= e($data['title']) ?></h2>
            <?php if ($subtitle !== ''): ?>
                <p class="mt-4 text-muted"><?= e($subtitle) ?></p>
            <?php endif; ?>
            <a href="<?= e(url('/layanan')) ?>"
               class="mt-6 inline-flex items-center gap-2 border-b-2 border-accent pb-1 font-medium hover:text-accent">
                Semua layanan <span aria-hidden="true">→</span>
            </a>
        </div>

        <ol class="border-t border-line lg:col-span-8">
            <?php foreach ($items as $i => $item): ?>
                <li class="border-b border-line">
                    <a href="<?= e(url('/layanan/' . $item['slug'])) ?>" class="group flex items-start gap-5 py-6 md:gap-8 md:py-7">
                        <span class="w-8 shrink-0 pt-1 font-serif text-xl text-muted"><?= e(sprintf('%02d', $i + 1)) ?></span>
                        <span class="min-w-0 flex-1">
                            <span class="block font-serif text-heading transition-colors group-hover:text-accent"><?= e($item['title']) ?></span>
                            <?php if (trim((string) $item['summary']) !== ''): ?>
                                <span class="mt-2 block max-w-prose text-muted"><?= e($item['summary']) ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="pt-1 text-xl transition-transform group-hover:translate-x-1" aria-hidden="true">→</span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>