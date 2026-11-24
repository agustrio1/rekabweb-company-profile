<?php
/** @var array $data */
$text = trim((string) ($data['text'] ?? ''));
$label = trim((string) ($data['button_label'] ?? ''));
$wa = \App\Services\Site::whatsappLink('Halo ' . \App\Services\Site::name() . ', saya ingin berkonsultasi.');
?>
<section class="wrap mt-section" aria-labelledby="h-cta">
    <div class="rounded bg-accent px-6 py-12 text-white md:px-14 md:py-16">
        <h2 id="h-cta" class="max-w-3xl text-title text-white"><?= e($data['title']) ?></h2>
        <?php if ($text !== ''): ?>
            <p class="mt-5 max-w-prose text-lg text-white/85"><?= e($text) ?></p>
        <?php endif; ?>

        <?php if ($label !== '' || $wa !== ''): ?>
            <div class="mt-9 flex flex-wrap items-center gap-4">
                <?php if ($label !== ''): ?>
                    <a href="<?= e(\App\Services\Site::link((string) $data['button_url'])) ?>"
                       class="btn bg-paper text-ink hover:bg-white"><?= e($label) ?></a>
                <?php endif; ?>
                <?php if ($wa !== ''): ?>
                    <a href="<?= e($wa) ?>" target="_blank" rel="noopener noreferrer"
                       class="btn border border-white/60 text-white hover:bg-white hover:text-ink">Chat WhatsApp</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>