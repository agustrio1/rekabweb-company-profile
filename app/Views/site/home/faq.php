<?php
/** @var array $data @var array<int, array> $items */
$subtitle = trim((string) ($data['subtitle'] ?? ''));
?>
<section class="wrap mt-section" aria-labelledby="h-faq">
    <div class="grid gap-10 lg:grid-cols-12 lg:gap-16">
        <div class="lg:col-span-4">
            <h2 id="h-faq" class="text-title"><?= e($data['title']) ?></h2>
            <?php if ($subtitle !== ''): ?>
                <p class="mt-4 text-muted"><?= e($subtitle) ?></p>
            <?php endif; ?>
            <a href="<?= e(url('/faq')) ?>"
               class="mt-6 inline-flex items-center gap-2 border-b-2 border-accent pb-1 font-medium hover:text-accent">
                Semua pertanyaan <span aria-hidden="true">→</span>
            </a>
        </div>

        <div class="border-t border-line lg:col-span-8" x-data="{ open: null }">
            <?php foreach ($items as $i => $item): ?>
                <?php $n = (int) $i; ?>
                <div class="border-b border-line">
                    <h3>
                        <button type="button"
                                class="flex w-full items-start justify-between gap-6 py-5 text-left font-serif text-xl"
                                @click="open = open === <?= $n ?> ? null : <?= $n ?>"
                                :aria-expanded="open === <?= $n ?>"
                                aria-controls="faq-<?= $n ?>">
                            <span><?= e($item['question']) ?></span>
                            <span class="shrink-0 font-sans text-2xl leading-none text-accent" aria-hidden="true"
                                  x-text="open === <?= $n ?> ? '−' : '+'">+</span>
                        </button>
                    </h3>
                    <div id="faq-<?= $n ?>" x-show="open === <?= $n ?>" x-cloak
                         class="max-w-prose whitespace-pre-line pb-6 text-muted"><?= e($item['answer']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>