<?php
use App\Services\Seo;
use App\Services\Site;

$path = '/proyek/' . $project['slug'];
$summary = trim((string) $project['summary']);
$image = $project['image'] ?? null;
$content = trim((string) $project['content']);

$description = trim((string) $project['seo_description']);
if ($description === '') {
    $description = $summary !== '' ? $summary : Seo::excerpt($content, 160);
}

$crumbs = [
    ['label' => 'Beranda', 'path' => '/'],
    ['label' => 'Proyek', 'path' => '/proyek'],
    ['label' => (string) $project['title'], 'path' => null],
];

$this->layout('site/layout', ['page' => [
    'title' => (string) $project['title'],
    'seo_title' => (string) $project['seo_title'],
    'description' => $description,
    'path' => $path,
    'active' => 'projects',
    'image' => $image['url'] ?? '',
    'jsonld' => [Seo::breadcrumbs([
        ['label' => 'Beranda', 'path' => '/'],
        ['label' => 'Proyek', 'path' => '/proyek'],
        ['label' => (string) $project['title'], 'path' => $path],
    ])],
]]);

$client = (int) $project['show_client'] === 1 ? trim((string) $project['client_name']) : '';
$facts = array_filter([
    'Klien' => $client,
    'Lokasi' => trim((string) $project['location']),
    'Tahun' => $project['completed_year'] !== null ? (string) $project['completed_year'] : '',
], static fn (string $v): bool => $v !== '');

$story = array_filter([
    'Tantangan' => trim((string) $project['challenge']),
    'Solusi' => trim((string) $project['solution']),
    'Hasil' => trim((string) $project['results']),
], static fn (string $v): bool => $v !== '');

$cta = Site::cta();
?>
<article>
    <header class="wrap pt-10 md:pt-14">
        <?php $this->partial('site/partials/breadcrumb', ['items' => $crumbs]); ?>

        <?php if (!empty($project['category'])): ?>
            <p class="eyebrow mt-6">
                <a href="<?= e(url('/proyek/kategori/' . $project['category_slug'])) ?>" class="underline-offset-4 hover:text-ink hover:underline"><?= e($project['category']) ?></a>
            </p>
        <?php endif; ?>

        <h1 class="<?= empty($project['category']) ? 'mt-6' : 'mt-3' ?> max-w-4xl text-display"><?= e($project['title']) ?></h1>
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

    <?php if ($facts !== [] || $content !== ''): ?>
        <div class="wrap mt-12 grid gap-10 lg:grid-cols-12 lg:gap-16">
            <?php if ($facts !== []): ?>
                <dl class="grid content-start gap-5 border-t-2 border-ink pt-5 lg:col-span-4">
                    <?php foreach ($facts as $label => $value): ?>
                        <div>
                            <dt class="eyebrow"><?= e($label) ?></dt>
                            <dd class="mt-1 font-medium"><?= e($value) ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            <?php endif; ?>

            <?php if ($content !== ''): ?>
                <?php /* $content sudah disanitasi HTMLPurifier saat disimpan */ ?>
                <div class="rich-prose max-w-prose <?= $facts !== [] ? 'lg:col-span-8' : 'lg:col-span-12' ?>"><?= $content ?></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($story !== []): ?>
        <section class="mt-section border-y border-line bg-surface" aria-label="Cerita proyek">
            <div class="wrap py-6 md:py-10">
                <?php foreach ($story as $label => $text): ?>
                    <div class="grid gap-3 border-b border-line py-8 last:border-0 lg:grid-cols-12 lg:gap-16">
                        <h2 class="text-heading text-accent lg:col-span-4"><?= e($label) ?></h2>
                        <p class="max-w-prose whitespace-pre-line text-lg lg:col-span-8"><?= e($text) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($project['gallery'] !== []): ?>
        <section class="wrap mt-section" aria-labelledby="h-gallery">
            <h2 id="h-gallery" class="text-title">Galeri</h2>
            <ul class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($project['gallery'] as $g): ?>
                    <li class="aspect-[4/3] overflow-hidden rounded bg-line">
                        <?php $this->partial('site/partials/img', ['image' => $g]); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>
</article>

<?php if ($others !== []): ?>
    <section class="wrap mt-section" aria-labelledby="h-others">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <h2 id="h-others" class="text-title">Proyek lainnya</h2>
            <a href="<?= e(url('/proyek')) ?>"
               class="inline-flex items-center gap-2 border-b-2 border-accent pb-1 font-medium hover:text-accent">
                Semua proyek <span aria-hidden="true">→</span>
            </a>
        </div>
        <ul class="mt-10 grid gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($others as $item): ?>
                <li><?php $this->partial('site/partials/project-card', ['item' => $item]); ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<?php $this->partial('site/partials/cta-band', [
    'title' => 'Punya proyek serupa?',
    'text' => 'Ceritakan rencana Anda dan kami bantu menyusun langkah pertamanya.',
    'label' => $cta['label'],
    'url' => $cta['url'],
]); ?>