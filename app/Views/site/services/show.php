
<?php
use App\Services\Seo;
use App\Services\Site;

$path = '/layanan/' . $service['slug'];
$d = $service['details'];
$summary = trim((string) $service['summary']);
$image = $service['image'] ?? null;
$content = trim((string) $service['content']);

$description = trim((string) $service['seo_description']);
if ($description === '') {
    $description = $summary !== '' ? $summary : Seo::excerpt($content, 160);
}

$crumbs = [
    ['label' => 'Beranda', 'path' => '/'],
    ['label' => 'Layanan', 'path' => '/layanan'],
    ['label' => (string) $service['title'], 'path' => null],
];

$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'name' => (string) $service['title'],
    'description' => $description,
    'url' => Seo::absolute(url($path)),
    'provider' => ['@type' => 'Organization', 'name' => Site::name()],
];
if ($image !== null) {
    $schema['image'] = Seo::absolute($image['url']);
}

$jsonld = [$schema, Seo::breadcrumbs([
    ['label' => 'Beranda', 'path' => '/'],
    ['label' => 'Layanan', 'path' => '/layanan'],
    ['label' => (string) $service['title'], 'path' => $path],
])];

if ($d['faqs'] !== []) {
    $jsonld[] = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(static fn (array $f): array => [
            '@type' => 'Question',
            'name' => (string) $f['question'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => (string) $f['answer']],
        ], $d['faqs']),
    ];
}

$this->layout('site/layout', ['page' => [
    'title' => (string) $service['title'],
    'seo_title' => (string) $service['seo_title'],
    'description' => $description,
    'path' => $path,
    'active' => 'services',
    'image' => $image['url'] ?? '',
    'jsonld' => $jsonld,
]]);

$default = Site::cta();
$ctaLabel = trim((string) $service['cta_label']) !== '' ? (string) $service['cta_label'] : $default['label'];
$ctaUrl = trim((string) $service['cta_url']) !== '' ? Site::link((string) $service['cta_url']) : $default['url'];
$wa = Site::whatsappLink('Halo ' . Site::name() . ', saya ingin bertanya tentang layanan ' . $service['title'] . '.');
?>
<article>
    <header class="wrap pt-10 md:pt-14">
        <?php $this->partial('site/partials/breadcrumb', ['items' => $crumbs]); ?>
        <h1 class="mt-6 max-w-4xl text-display"><?= e($service['title']) ?></h1>
        <?php if ($summary !== ''): ?>
            <p class="mt-6 max-w-prose text-lg text-muted"><?= e($summary) ?></p>
        <?php endif; ?>
    </header>

    <?php if ($image !== null): ?>
        <div class="wrap mt-10">
            <div class="aspect-[16/9] overflow-hidden rounded bg-line">
                <?php $this->partial('site/partials/img', ['image' => $image, 'lazy' => false]); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="wrap mt-12 grid gap-12 lg:grid-cols-12 lg:gap-16">
        <div class="lg:col-span-8">
            <?php if ($content !== ''): ?>
                <?php /* $content sudah disanitasi HTMLPurifier saat disimpan */ ?>
                <div class="rich-prose max-w-prose"><?= $content ?></div>
            <?php elseif ($summary === ''): ?>
                <p class="text-muted">Detail layanan ini akan segera ditambahkan.</p>
            <?php endif; ?>
        </div>

        <aside class="lg:col-span-4">
            <div class="border-t-2 border-ink pt-6 lg:sticky lg:top-28">
                <p class="font-serif text-heading">Tertarik dengan layanan ini?</p>
                <p class="mt-3 text-muted">Ceritakan kebutuhan Anda, kami balas dengan langkah berikutnya.</p>
                <div class="mt-6 grid gap-3">
                    <a href="<?= e($ctaUrl) ?>" class="btn btn-primary"><?= e($ctaLabel) ?></a>
                    <?php if ($wa !== ''): ?>
                        <a href="<?= e($wa) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline">Chat WhatsApp</a>
                    <?php endif; ?>
                </div>
            </div>
        </aside>
    </div>

    <?php if ($d['benefits'] !== []): ?>
        <section class="wrap mt-section" aria-labelledby="h-benefits">
            <h2 id="h-benefits" class="max-w-2xl text-title">Manfaat</h2>
            <ul class="mt-12 grid gap-x-10 gap-y-10 md:grid-cols-3">
                <?php foreach ($d['benefits'] as $item): ?>
                    <li class="border-t-2 border-ink pt-5">
                        <h3 class="text-heading"><?= e($item['title']) ?></h3>
                        <?php if (trim((string) ($item['text'] ?? '')) !== ''): ?>
                            <p class="mt-3 text-muted"><?= e($item['text']) ?></p>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php if ($d['process'] !== []): ?>
        <section class="mt-section border-y border-line bg-surface" aria-labelledby="h-process">
            <div class="wrap py-14 md:py-20">
                <h2 id="h-process" class="max-w-2xl text-title">Proses pengerjaan</h2>
                <ol class="mt-12 grid gap-x-8 gap-y-10 md:grid-cols-2 lg:grid-cols-4">
                    <?php foreach ($d['process'] as $i => $item): ?>
                        <li class="border-t border-line pt-5">
                            <span class="font-serif text-title text-accent"><?= e($i + 1) ?></span>
                            <h3 class="mt-3 text-heading"><?= e($item['title']) ?></h3>
                            <?php if (trim((string) ($item['text'] ?? '')) !== ''): ?>
                                <p class="mt-2 text-muted"><?= e($item['text']) ?></p>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($d['faqs'] !== []): ?>
        <section class="wrap mt-section" aria-labelledby="h-faq">
            <div class="grid gap-10 lg:grid-cols-12 lg:gap-16">
                <h2 id="h-faq" class="text-title lg:col-span-4">Pertanyaan tentang layanan ini</h2>
                <div class="border-t border-line lg:col-span-8" x-data="{ open: null }">
                    <?php foreach ($d['faqs'] as $i => $item): ?>
                        <?php $n = (int) $i; ?>
                        <div class="border-b border-line">
                            <h3>
                                <button type="button"
                                        class="flex w-full items-start justify-between gap-6 py-5 text-left font-serif text-xl"
                                        @click="open = open === <?= $n ?> ? null : <?= $n ?>"
                                        :aria-expanded="open === <?= $n ?>"
                                        aria-controls="sfaq-<?= $n ?>">
                                    <span><?= e($item['question']) ?></span>
                                    <span class="shrink-0 font-sans text-2xl leading-none text-accent" aria-hidden="true"
                                          x-text="open === <?= $n ?> ? '−' : '+'">+</span>
                                </button>
                            </h3>
                            <div id="sfaq-<?= $n ?>" x-show="open === <?= $n ?>" x-cloak
                                 class="max-w-prose whitespace-pre-line pb-6 text-muted"><?= e($item['answer']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</article>

<?php if ($others !== []): ?>
    <section class="wrap mt-section" aria-labelledby="h-others">
        <h2 id="h-others" class="text-title">Layanan lainnya</h2>
        <ul class="mt-10 border-t border-line">
            <?php foreach ($others as $item): ?>
                <li class="border-b border-line">
                    <a href="<?= e(url('/layanan/' . $item['slug'])) ?>" class="group flex items-start gap-5 py-6">
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
        </ul>
    </section>
<?php endif; ?>

<?php $this->partial('site/partials/cta-band', [
    'title' => 'Siap memulai?',
    'text' => 'Sampaikan kebutuhan Anda dan kami balas dengan langkah berikutnya.',
    'label' => $ctaLabel,
    'url' => $ctaUrl,
]); ?>