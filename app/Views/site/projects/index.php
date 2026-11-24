<?php
use App\Services\Catalog;

$basePath = $category !== null ? '/proyek/kategori/' . $category['slug'] : '/proyek';
$pagePath = $basePath . ($page > 1 ? '?halaman=' . $page : '');
$heading = $category !== null ? 'Proyek: ' . $category['name'] : 'Proyek';

$crumbs = [['label' => 'Beranda', 'path' => '/'], ['label' => 'Proyek', 'path' => '/proyek']];
if ($category !== null) {
    $crumbs[] = ['label' => (string) $category['name'], 'path' => null];
} else {
    $crumbs[1]['path'] = null;
}

$this->layout('site/layout', ['page' => [
    'title' => $heading . ($page > 1 ? ' (halaman ' . $page . ')' : ''),
    'description' => 'Proyek yang sudah dikerjakan ' . \App\Services\Site::name() . '.',
    'path' => $pagePath,
    'active' => 'projects',
    'jsonld' => [\App\Services\Seo::breadcrumbs(array_map(
        static fn (array $c): array => $c['path'] === null
            ? ['label' => $c['label'], 'path' => $basePath]
            : $c,
        $crumbs
    ))],
]]);

$link = static fn (int $p): string => url($basePath . ($p > 1 ? '?halaman=' . $p : ''));
?>
<section class="wrap pt-10 md:pt-14">
    <?php $this->partial('site/partials/breadcrumb', ['items' => $crumbs]); ?>

    <h1 class="mt-6 max-w-3xl text-display"><?= e($heading) ?></h1>
    <p class="mt-5 max-w-prose text-lg text-muted">
        Pekerjaan yang sudah kami selesaikan, lengkap dengan tantangan dan solusinya.
    </p>

    <?php if ($categories !== []): ?>
        <nav class="mt-10 flex flex-wrap gap-x-6 gap-y-3 border-b border-line pb-4" aria-label="Filter kategori">
            <a href="<?= e(url('/proyek')) ?>"
               class="border-b-2 pb-1 font-medium <?= $category === null ? 'border-accent text-ink' : 'border-transparent text-muted hover:text-ink' ?>"
               <?= $category === null ? 'aria-current="page"' : '' ?>>Semua</a>
            <?php foreach ($categories as $cat): ?>
                <?php $is = $category !== null && (int) $category['id'] === (int) $cat['id']; ?>
                <a href="<?= e(url('/proyek/kategori/' . $cat['slug'])) ?>"
                   class="border-b-2 pb-1 font-medium <?= $is ? 'border-accent text-ink' : 'border-transparent text-muted hover:text-ink' ?>"
                   <?= $is ? 'aria-current="page"' : '' ?>><?= e($cat['name']) ?> <span class="text-sm text-muted"><?= e($cat['n']) ?></span></a>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>
</section>

<section class="wrap mt-10 md:mt-12" aria-label="Daftar proyek">
    <?php if ($items === []): ?>
        <p class="border-t border-line py-12 text-muted">
            <?= $category !== null ? 'Belum ada proyek di kategori ini.' : 'Proyek akan segera ditampilkan di sini.' ?>
        </p>
    <?php else: ?>
        <ul class="grid gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($items as $item): ?>
                <li><?php $this->partial('site/partials/project-card', ['item' => $item]); ?></li>
            <?php endforeach; ?>
        </ul>

        <?php if ($pages > 1): ?>
            <nav class="mt-14 flex items-center justify-between border-t border-line pt-6" aria-label="Halaman proyek">
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
$cta = \App\Services\Site::cta();
$this->partial('site/partials/cta-band', [
    'title' => 'Punya proyek serupa?',
    'text' => 'Ceritakan rencana Anda dan kami bantu menyusun langkah pertamanya.',
    'label' => $cta['label'],
    'url' => $cta['url'],
]);
?>