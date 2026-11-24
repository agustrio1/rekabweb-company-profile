<?php
use App\Services\Seo;
use App\Services\Site;

$crumbs = [
    ['label' => 'Beranda', 'path' => '/'],
    ['label' => 'FAQ', 'path' => null],
];

$jsonld = [Seo::breadcrumbs([
    ['label' => 'Beranda', 'path' => '/'],
    ['label' => 'FAQ', 'path' => '/faq'],
])];

if ($faqs !== []) {
    $jsonld[] = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(static fn (array $f): array => [
            '@type' => 'Question',
            'name' => (string) $f['question'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => (string) $f['answer']],
        ], $faqs),
    ];
}

$this->layout('site/layout', ['page' => [
    'title' => 'Pertanyaan umum',
    'description' => 'Jawaban atas pertanyaan yang sering diajukan tentang ' . Site::name() . '.',
    'path' => '/faq',
    'active' => '',
    'jsonld' => $jsonld,
]]);

// Kelompokkan per kategori. Tanpa kategori masuk "Umum" kalau ada kategori lain.
$groups = [];
$hasNamed = false;
foreach ($faqs as $f) {
    if (trim((string) $f['category']) !== '') {
        $hasNamed = true;
    }
}
foreach ($faqs as $f) {
    $cat = trim((string) $f['category']);
    $key = $cat !== '' ? $cat : ($hasNamed ? 'Umum' : '');
    $groups[$key][] = $f;
}

$cta = Site::cta();
$n = 0;
?>
<section class="wrap pt-10 md:pt-14">
    <?php $this->partial('site/partials/breadcrumb', ['items' => $crumbs]); ?>
    <h1 class="mt-6 max-w-3xl text-display">Pertanyaan umum</h1>
    <p class="mt-5 max-w-prose text-lg text-muted">
        Jawaban atas pertanyaan yang paling sering kami terima. Tidak menemukan jawabannya? Hubungi kami.
    </p>
</section>

<section class="wrap mt-12 md:mt-16" aria-label="Daftar pertanyaan" x-data="{ open: null }">
    <?php if ($faqs === []): ?>
        <p class="border-t border-line py-12 text-muted">Pertanyaan umum akan segera ditampilkan di sini.</p>
    <?php else: ?>
        <?php foreach ($groups as $label => $items): ?>
            <div class="mb-14 grid gap-6 last:mb-0 lg:grid-cols-12 lg:gap-16">
                <?php if ($label !== ''): ?>
                    <h2 class="text-heading lg:col-span-4"><?= e($label) ?></h2>
                <?php endif; ?>

                <div class="border-t border-line <?= $label !== '' ? 'lg:col-span-8' : 'lg:col-span-12' ?>">
                    <?php foreach ($items as $item): ?>
                        <?php $i = $n++; ?>
                        <div class="border-b border-line">
                            <h3>
                                <button type="button"
                                        class="flex w-full items-start justify-between gap-6 py-5 text-left font-serif text-xl"
                                        @click="open = open === <?= $i ?> ? null : <?= $i ?>"
                                        :aria-expanded="open === <?= $i ?>"
                                        aria-controls="pfaq-<?= $i ?>">
                                    <span><?= e($item['question']) ?></span>
                                    <span class="shrink-0 font-sans text-2xl leading-none text-accent" aria-hidden="true"
                                          x-text="open === <?= $i ?> ? '−' : '+'">+</span>
                                </button>
                            </h3>
                            <div id="pfaq-<?= $i ?>" x-show="open === <?= $i ?>" x-cloak
                                 class="max-w-prose whitespace-pre-line pb-6 text-muted"><?= e($item['answer']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<?php $this->partial('site/partials/cta-band', [
    'title' => 'Masih ada pertanyaan?',
    'text' => 'Tanyakan langsung dan kami jawab secepatnya.',
    'label' => $cta['label'],
    'url' => $cta['url'],
]); ?>