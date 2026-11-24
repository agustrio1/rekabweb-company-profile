<?php
/** @var array $data @var array<int, array> $items */
$subtitle = trim((string) ($data['subtitle'] ?? ''));
$featured = $items[0];
$rest = array_slice($items, 1);

$person = static function (array $t): void {
    $sub = trim((string) $t['job_title'] . ((string) $t['job_title'] !== '' && (string) $t['company'] !== '' ? ', ' : '') . (string) $t['company']);
    $img = $t['image'] ?? null;
    ?>
    <figcaption class="mt-6 flex items-center gap-4">
        <?php if ($img !== null): ?>
            <img src="<?= e($img['url']) ?>" alt="" loading="lazy" decoding="async"
                 class="h-12 w-12 shrink-0 rounded-full border border-line object-cover">
        <?php else: ?>
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-line bg-surface font-serif text-lg" aria-hidden="true">
                <?= e(mb_strtoupper(mb_substr((string) $t['name'], 0, 1))) ?>
            </span>
        <?php endif; ?>
        <span class="min-w-0">
            <span class="block font-medium"><?= e($t['name']) ?></span>
            <?php if ($sub !== ''): ?><span class="block text-sm text-muted"><?= e($sub) ?></span><?php endif; ?>
        </span>
    </figcaption>
    <?php
};
?>
<section class="wrap mt-section" aria-labelledby="h-testimonials">
    <div class="max-w-2xl">
        <h2 id="h-testimonials" class="text-title"><?= e($data['title']) ?></h2>
        <?php if ($subtitle !== ''): ?>
            <p class="mt-4 text-lg text-muted"><?= e($subtitle) ?></p>
        <?php endif; ?>
    </div>

    <figure class="mt-12 max-w-4xl border-l-4 border-accent pl-6 md:pl-10">
        <blockquote class="whitespace-pre-line font-serif text-heading md:text-title"><?= e($featured['quote']) ?></blockquote>
        <?php $person($featured); ?>
    </figure>

    <?php if ($rest !== []): ?>
        <div class="mt-12 grid gap-x-10 gap-y-10 border-t border-line pt-10 md:grid-cols-2">
            <?php foreach ($rest as $t): ?>
                <figure>
                    <blockquote class="whitespace-pre-line text-muted"><?= e($t['quote']) ?></blockquote>
                    <?php $person($t); ?>
                </figure>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>