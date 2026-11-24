<?php
/** @var string $active key menu yang sedang aktif */
$active = (string) ($active ?? '');
$name = \App\Services\Site::name();
$logo = setting_image('logo');
$cta = \App\Services\Site::cta();
$nav = \App\Services\Site::nav();
?>
<header class="sticky top-0 z-40 border-b border-line bg-paper"
        x-data="{ open: false }" @keydown.escape.window="open = false">
    <div class="wrap flex h-16 items-center justify-between gap-6 md:h-20">
        <a href="<?= e(url('/')) ?>" class="flex min-w-0 items-center" aria-label="<?= e($name) ?>, ke beranda">
            <?php if ($logo !== ''): ?>
                <img src="<?= e($logo) ?>" alt="<?= e($name) ?>" class="h-9 w-auto max-w-[11rem] object-contain md:h-10">
            <?php else: ?>
                <span class="truncate font-serif text-xl font-semibold tracking-tight md:text-2xl"><?= e($name) ?></span>
            <?php endif; ?>
        </a>

        <nav class="hidden items-center gap-8 lg:flex" aria-label="Menu utama">
            <?php foreach ($nav as $item): ?>
                <?php $is = $active === $item['key']; ?>
                <a href="<?= e(url($item['path'])) ?>"
                   class="border-b-2 py-1 text-[0.95rem] font-medium transition-colors <?= $is ? 'border-accent text-ink' : 'border-transparent text-muted hover:text-ink' ?>"
                   <?= $is ? 'aria-current="page"' : '' ?>><?= e($item['label']) ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="flex items-center gap-3">
            <a href="<?= e($cta['url']) ?>" class="btn btn-primary btn-sm hidden lg:inline-flex"><?= e($cta['label']) ?></a>

            <button type="button"
                    class="-mr-2 flex h-11 w-11 items-center justify-center rounded text-ink lg:hidden"
                    @click="open = !open" :aria-expanded="open" aria-controls="menu-mobile" aria-label="Menu">
                <svg x-show="!open" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                <svg x-show="open" x-cloak class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </div>
    </div>

    <div id="menu-mobile" x-show="open" x-cloak class="border-t border-line bg-paper lg:hidden">
        <nav class="wrap grid py-3" aria-label="Menu seluler">
            <?php foreach ($nav as $item): ?>
                <?php $is = $active === $item['key']; ?>
                <a href="<?= e(url($item['path'])) ?>"
                   class="border-b border-line py-3.5 text-lg font-medium <?= $is ? 'text-accent' : 'text-ink' ?>"
                   <?= $is ? 'aria-current="page"' : '' ?>><?= e($item['label']) ?></a>
            <?php endforeach; ?>
            <a href="<?= e($cta['url']) ?>" class="btn btn-primary mt-4"><?= e($cta['label']) ?></a>
        </nav>
    </div>
</header>