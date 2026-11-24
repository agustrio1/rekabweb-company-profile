<?php
$this->layout('site/layout', ['page' => [
    'title' => 'Layanan',
    'description' => 'Layanan ' . \App\Services\Site::name() . '.',
    'path' => '/layanan',
    'active' => 'services',
    'jsonld' => [\App\Services\Seo::breadcrumbs([
        ['label' => 'Beranda', 'path' => '/'],
        ['label' => 'Layanan', 'path' => null],
    ])],
]]);
?>
<section class="wrap pt-10 md:pt-14">
    <?php $this->partial('site/partials/breadcrumb', ['items' => [
        ['label' => 'Beranda', 'path' => '/'],
        ['label' => 'Layanan', 'path' => null],
    ]]); ?>

    <h1 class="mt-6 max-w-3xl text-display">Layanan</h1>
    <p class="mt-5 max-w-prose text-lg text-muted">
        Pilih layanan yang sesuai kebutuhan Anda. Belum yakin mana yang tepat? Ceritakan kebutuhan Anda dan kami bantu arahkan.
    </p>
</section>

<section class="wrap mt-12 md:mt-16" aria-label="Daftar layanan">
    <?php if ($items === []): ?>
        <p class="border-t border-line py-12 text-muted">Layanan akan segera ditampilkan di sini.</p>
    <?php else: ?>
        <ul class="border-t border-line">
            <?php foreach ($items as $item): ?>
                <li class="border-b border-line">
                    <a href="<?= e(url('/layanan/' . $item['slug'])) ?>"
                       class="group grid gap-5 py-8 sm:grid-cols-[16rem_1fr] sm:gap-10">
                        <div class="aspect-[4/3] overflow-hidden rounded bg-line">
                            <?php $this->partial('site/partials/img', ['image' => $item['image'] ?? null]); ?>
                        </div>
                        <div class="min-w-0 self-center">
                            <h2 class="text-heading transition-colors group-hover:text-accent"><?= e($item['title']) ?></h2>
                            <?php if (trim((string) $item['summary']) !== ''): ?>
                                <p class="mt-3 max-w-prose text-muted"><?= e($item['summary']) ?></p>
                            <?php endif; ?>
                            <span class="mt-5 inline-flex items-center gap-2 font-medium">
                                Selengkapnya <span class="transition-transform group-hover:translate-x-1" aria-hidden="true">→</span>
                            </span>
                        </div>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<?php
$cta = \App\Services\Site::cta();
$this->partial('site/partials/cta-band', [
    'title' => 'Siap berdiskusi tentang kebutuhan Anda?',
    'text' => 'Sampaikan kebutuhan Anda dan kami balas dengan langkah berikutnya.',
    'label' => $cta['label'],
    'url' => $cta['url'],
]);
?>