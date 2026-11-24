<?php
use App\Services\Seo;
use App\Services\Site;
use App\Services\SiteContent;

$path = '/blog/' . $article['slug'];
$image = $article['image'] ?? null;
$content = trim((string) $article['content']);
$excerpt = trim((string) $article['excerpt']);

$description = trim((string) $article['seo_description']);
if ($description === '') {
    $description = $excerpt !== '' ? $excerpt : Seo::excerpt($content, 160);
}

$crumbs = [
    ['label' => 'Beranda', 'path' => '/'],
    ['label' => 'Blog', 'path' => '/blog'],
    ['label' => (string) $article['title'], 'path' => null],
];

$authorName = trim((string) ($article['author'] ?? ''));
$absolute = Seo::absolute(url($path));

$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => mb_substr((string) $article['title'], 0, 110),
    'description' => $description,
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $absolute],
    'datePublished' => date('c', (int) strtotime((string) $article['pub_date'])),
    'dateModified' => date('c', (int) strtotime((string) $article['updated_at'])),
    'author' => $authorName !== ''
        ? ['@type' => 'Person', 'name' => $authorName]
        : ['@type' => 'Organization', 'name' => Site::name()],
    'publisher' => ['@type' => 'Organization', 'name' => Site::name()],
];
if ($image !== null) {
    $schema['image'] = Seo::absolute($image['url']);
}
$logo = setting_image('logo');
if ($logo !== '') {
    $schema['publisher']['logo'] = ['@type' => 'ImageObject', 'url' => Seo::absolute($logo)];
}

$this->layout('site/layout', ['page' => [
    'title' => (string) $article['title'],
    'seo_title' => (string) $article['seo_title'],
    'description' => $description,
    'path' => $path,
    'active' => 'blog',
    'type' => 'article',
    'image' => $image['url'] ?? '',
    'jsonld' => [$schema, Seo::breadcrumbs([
        ['label' => 'Beranda', 'path' => '/'],
        ['label' => 'Blog', 'path' => '/blog'],
        ['label' => (string) $article['title'], 'path' => $path],
    ])],
]]);

$meta = array_filter([
    SiteContent::dateId((string) $article['pub_date']),
    $authorName !== '' ? 'Oleh ' . $authorName : '',
    $minutes . ' menit baca',
], static fn (string $v): bool => $v !== '');

$shareUrl = str_starts_with($absolute, 'http')
    ? 'https://wa.me/?text=' . rawurlencode($article['title'] . ' ' . $absolute)
    : '';
$cta = Site::cta();
?>
<article>
    <header class="wrap pt-10 md:pt-14">
        <?php $this->partial('site/partials/breadcrumb', ['items' => $crumbs]); ?>

        <?php if (!empty($article['category'])): ?>
            <p class="eyebrow mt-6">
                <a href="<?= e(url('/blog/kategori/' . $article['category_slug'])) ?>" class="underline-offset-4 hover:text-ink hover:underline"><?= e($article['category']) ?></a>
            </p>
        <?php endif; ?>

        <h1 class="<?= empty($article['category']) ? 'mt-6' : 'mt-3' ?> max-w-4xl text-display"><?= e($article['title']) ?></h1>
        <p class="mt-6 text-muted"><?= e(implode(' · ', $meta)) ?></p>
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
        <?php endif; ?>

        <?php if ($article['tags'] !== [] || $shareUrl !== ''): ?>
            <div class="mt-12 flex max-w-prose flex-wrap items-center justify-between gap-5 border-t border-line pt-6">
                <?php if ($article['tags'] !== []): ?>
                    <ul class="flex flex-wrap gap-2" aria-label="Tag artikel">
                        <?php foreach ($article['tags'] as $t): ?>
                            <li>
                                <a href="<?= e(url('/blog/tag/' . $t['slug'])) ?>"
                                   class="rounded border border-line px-3 py-1 text-sm text-muted hover:border-ink hover:text-ink">#<?= e($t['name']) ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if ($shareUrl !== ''): ?>
                    <a href="<?= e($shareUrl) ?>" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 border-b-2 border-accent pb-1 text-[0.95rem] font-medium hover:text-accent">
                        Bagikan lewat WhatsApp <span aria-hidden="true">→</span>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</article>

<?php if ($related !== []): ?>
    <section class="wrap mt-section" aria-labelledby="h-related">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <h2 id="h-related" class="text-title">Artikel terkait</h2>
            <a href="<?= e(url('/blog')) ?>"
               class="inline-flex items-center gap-2 border-b-2 border-accent pb-1 font-medium hover:text-accent">
                Semua artikel <span aria-hidden="true">→</span>
            </a>
        </div>
        <ul class="mt-10 grid gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($related as $item): ?>
                <li><?php $this->partial('site/partials/article-card', ['item' => $item]); ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<?php $this->partial('site/partials/cta-band', [
    'title' => 'Butuh bantuan untuk kasus Anda?',
    'text' => 'Ceritakan kebutuhan Anda dan kami balas dengan langkah berikutnya.',
    'label' => $cta['label'],
    'url' => $cta['url'],
]); ?>