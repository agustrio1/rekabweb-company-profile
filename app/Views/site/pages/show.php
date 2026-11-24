<?php
use App\Services\Seo;
use App\Services\Site;

$path = '/' . $item['slug'];
$image = $item['image'] ?? null;
$content = trim((string) $item['content']);

$description = trim((string) $item['seo_description']);
if ($description === '') {
    $description = Seo::excerpt($content, 160);
}

$isAbout = in_array($item['slug'], ['tentang', 'about'], true);
$isLegal = in_array($item['slug'], ['kebijakan-privasi', 'syarat-ketentuan'], true);

$crumbs = [
    ['label' => 'Beranda', 'path' => '/'],
    ['label' => (string) $item['title'], 'path' => null],
];

$this->layout('site/layout', ['page' => [
    'title' => (string) $item['title'],
    'seo_title' => (string) $item['seo_title'],
    'description' => $description,
    'path' => $path,
    'active' => $isAbout ? 'about' : '',
    'image' => $image['url'] ?? '',
    'jsonld' => [Seo::breadcrumbs([
        ['label' => 'Beranda', 'path' => '/'],
        ['label' => (string) $item['title'], 'path' => $path],
    ])],
]]);

$cta = Site::cta();
?>
<article>
    <header class="wrap pt-10 md:pt-14">
        <?php $this->partial('site/partials/breadcrumb', ['items' => $crumbs]); ?>
        <h1 class="mt-6 max-w-4xl text-display"><?= e($item['title']) ?></h1>
    </header>

    <?php if ($image !== null): ?>
        <div class="wrap mt-10">
            <div class="aspect-[16/9] overflow-hidden rounded bg-line">
                <?php $this->partial('site/partials/img', ['image' => $image, 'lazy' => false]); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="wrap mt-12">
        <?php if ($content !== ''): ?>
            <?php /* $content sudah disanitasi HTMLPurifier saat disimpan */ ?>
            <div class="rich-prose max-w-prose"><?= $content ?></div>
        <?php else: ?>
            <p class="text-muted">Isi halaman ini akan segera ditambahkan.</p>
        <?php endif; ?>
    </div>
</article>

<?php if (!$isLegal): ?>
    <?php $this->partial('site/partials/cta-band', [
        'title' => 'Ingin berdiskusi lebih lanjut?',
        'text' => 'Sampaikan kebutuhan Anda dan kami balas dengan langkah berikutnya.',
        'label' => $cta['label'],
        'url' => $cta['url'],
    ]); ?>
<?php endif; ?>