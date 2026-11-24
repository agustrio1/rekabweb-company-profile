<?php
use App\Services\Site;
use App\Services\SiteContent;

if ($category !== null) {
    $basePath = '/blog/kategori/' . $category['slug'];
    $heading = 'Kategori: ' . $category['name'];
    $intro = 'Artikel dalam kategori ' . $category['name'] . '.';
} elseif ($tag !== null) {
    $basePath = '/blog/tag/' . $tag['slug'];
    $heading = 'Tag: ' . $tag['name'];
    $intro = 'Artikel dengan tag ' . $tag['name'] . '.';
} else {
    $basePath = '/blog';
    $heading = 'Blog';
    $intro = 'Tips, panduan, dan kabar terbaru dari ' . Site::name() . '.';
}

$pagePath = $basePath . ($page > 1 ? '?halaman=' . $page : '');

$crumbs = [['label' => 'Beranda', 'path' => '/'], ['label' => 'Blog', 'path' => '/blog']];
if ($category !== null) {
    $crumbs[] = ['label' => (string) $category['name'], 'path' => null];
} elseif ($tag !== null) {
    $crumbs[] = ['label' => (string) $tag['name'], 'path' => null];
} else {
    $crumbs[1]['path'] = null;
}

$this->layout('site/layout', ['page' => [
    'title' => $heading . ($page > 1 ? ' (halaman ' . $page . ')' : ''),
    'description' => $intro,
    'path' => $pagePath,
    'active' => 'blog',
    'jsonld' => [\App\Services\Seo::breadcrumbs(array_map(
        static fn (array $c): array => $c['path'] === null
            ? ['label' => $c['label'], 'path' => $basePath]
            : $c,
        $crumbs
    ))],
]]);

$link = static fn (int $p): string => url($basePath . ($p > 1 ? '?halaman=' . $p : ''));
$isPlain = $category === null && $tag === null;
$hasFeatured = $isPlain && $page === 1 && count($items) > 1;
$featured = $hasFeatured ? $items[0] : null;
$rest = $hasFeatured ? array_slice($items, 1) : $items;
?>
<section class="wrap pt-10 md:pt-14">
    <?php $this->partial('site/partials/breadcrumb', ['items' => $crumbs]); ?>

    <h1 class="mt-6 max-w-3xl text-display"><?= e($heading) ?></h1>
    <p class="mt-5 max-w-prose text-lg text-muted"><?= e($intro) ?></p>

    <?php if ($categories !== []): ?>
        <nav class="mt-10 flex flex-wrap gap-x-6 gap-y-3 border-b border-line pb-4" aria-label="Filter kategori">
            <a href="<?= e(url('/blog')) ?>"
               class="border-b-2 pb-1 font-medium <?= $isPlain ? 'border-accent text-ink' : 'border-transparent text-muted hover:text-ink' ?>"
               <?= $isPlain ? 'aria-current="page"' : '' ?>>Semua</a>
            <?php foreach ($categories as $cat): ?>
                <?php $is = $category !== null && (int) $category['id'] === (int) $cat['id']; ?>
                <a href="<?= e(url('/blog/kategori/' . $cat['slug'])) ?>"
                   class="border-b-2 pb-1 font-medium <?= $is ? 'border-accent text-ink' : 'border-transparent text-muted hover:text-ink' ?>"
                   <?= $is ? 'aria-current="page"' : '' ?>><?= e($cat['name']) ?> <span class="text-sm text-muted"><?= e($cat['n']) ?></span></a>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>

    <?php if ($tags !== []): ?>
        <nav class="mt-5 flex flex-wrap items-center gap-2" aria-label="Tag populer">
            <span class="eyebrow mr-1">Tag</span>
            <?php foreach ($tags as $t): ?>
                <?php $is = $tag !== null && (int) $tag['id'] === (int) $t['id']; ?>
                <a href="<?= e(url('/blog/tag/' . $t['slug'])) ?>"
                   class="rounded border px-3 py-1 text-sm <?= $is ? 'border-ink bg-ink text-paper' : 'border-line text-muted hover:border-ink hover:text-ink' ?>"
                   <?= $is ? 'aria-current="page"' : '' ?>>#<?= e($t['name']) ?></a>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>
</section>

<section class="wrap mt-10 md:mt-12" aria-label="Daftar artikel">
    <?php if ($items === []): ?>
        <p class="border-t border-line py-12 text-muted">
            <?= $isPlain ? 'Artikel akan segera ditampilkan di sini.' : 'Belum ada artikel di sini.' ?>
        </p>
    <?php else: ?>
        <?php if ($featured !== null): ?>
            <?php
            $fdate = SiteContent::dateId((string) $featured['pub_date']);
            $fmeta = array_filter([$fdate, (string) ($featured['category'] ?? '')], static fn (string $v): bool => $v !== '');
            ?>
            <a href="<?= e(url('/blog/' . $featured['slug'])) ?>"
               class="group mb-14 grid items-center gap-8 border-b border-line pb-14 lg:grid-cols-12 lg:gap-12">
                <div class="aspect-[16/10] overflow-hidden rounded bg-line lg:col-span-7">
                    <?php $this->partial('site/partials/img', [
                        'image' => $featured['image'] ?? null,
                        'lazy' => false,
                        'class' => 'h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.02]',
                    ]); ?>
                </div>
                <div class="lg:col-span-5">
                    <?php if ($fmeta !== []): ?>
                        <p class="eyebrow"><?= e(implode(' · ', $fmeta)) ?></p>
                    <?php endif; ?>
                    <h2 class="mt-3 text-title transition-colors group-hover:text-accent"><?= e($featured['title']) ?></h2>
                    <?php if (trim((string) $featured['summary']) !== ''): ?>
                        <p class="mt-4 max-w-prose text-lg text-muted"><?= e($featured['summary']) ?></p>
                    <?php endif; ?>
                    <span class="mt-6 inline-flex items-center gap-2 font-medium">
                        Baca artikel <span class="transition-transform group-hover:translate-x-1" aria-hidden="true">→</span>
                    </span>
                </div>
            </a>
        <?php endif; ?>

        <?php if ($rest !== []): ?>
            <ul class="grid gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($rest as $item): ?>
                    <li><?php $this->partial('site/partials/article-card', ['item' => $item]); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if ($pages > 1): ?>
            <nav class="mt-14 flex items-center justify-between border-t border-line pt-6" aria-label="Halaman artikel">
                <?php if ($page > 1): ?>
                    <a href="<?= e($link($page - 1)) ?>" class="btn btn-outline btn-sm" rel="prev">← Sebelumnya</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
                <p class="text-sm text-muted">Halaman <?= e($page) ?> dari <?= e($pages) ?></p>
                <?php if ($page < $pages): ?>
                    <a href="<?= e($link($page + 1)) ?>" class="btn btn-outline btn-sm" rel="next">Berikutnya →</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>

<?php
$cta = Site::cta();
$this->partial('site/partials/cta-band', [
    'title' => 'Ada pertanyaan seputar topik ini?',
    'text' => 'Ceritakan kebutuhan Anda dan kami bantu menjawabnya.',
    'label' => $cta['label'],
    'url' => $cta['url'],
]);
?>