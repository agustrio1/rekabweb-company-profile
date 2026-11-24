<?php
/** @var string $title */
/** @var string $text */
/** @var string $label */
/** @var string $url */
$text = trim((string) ($text ?? ''));
$wa = \App\Services\Site::whatsappLink('Halo ' . \App\Services\Site::name() . ', saya ingin berkonsultasi.');
?>
<section class="wrap mt-section" aria-labelledby="h-cta-band">
    <div class="rounded bg-accent px-6 py-12 text-white md:px-14 md:py-16">
        <h2 id="h-cta-band" class="max-w-3xl text-title text-white"><?= e($title) ?></h2>
        <?php if ($text !== ''): ?>
            <p class="mt-5 max-w-prose text-lg text-white/85"><?= e($text) ?></p>
        <?php endif; ?>
        <div class="mt-9 flex flex-wrap items-center gap-4">
            <a href="<?= e($url) ?>" class="btn bg-paper text-ink hover:bg-white"><?= e($label) ?></a>
            <?php if ($wa !== ''): ?>
                <a href="<?= e($wa) ?>" target="_blank" rel="noopener noreferrer"
                   class="btn border border-white/60 text-white hover:bg-white hover:text-ink">Chat WhatsApp</a>
            <?php endif; ?>
        </div>
    </div>
</section>