<?php
$user = auth_user();
$active = $active ?? '';
$appName = (string) config('app.name');
$initial = mb_strtoupper(mb_substr((string) ($user['name'] ?? '?'), 0, 1));
$newInquiries = can('inquiries.view') ? \App\Services\Inquiries::newCount() : 0;

// Ikon lucide (path konstan, bukan input pengguna)
$icons = [
    'dashboard' => '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>',
    'database' => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14a9 3 0 0 0 18 0V5"/><path d="M3 12a9 3 0 0 0 18 0"/>',
    'settings' => '<line x1="21" x2="14" y1="4" y2="4"/><line x1="10" x2="3" y1="4" y2="4"/><line x1="21" x2="12" y1="12" y2="12"/><line x1="8" x2="3" y1="12" y2="12"/><line x1="21" x2="16" y1="20" y2="20"/><line x1="12" x2="3" y1="20" y2="20"/><line x1="14" x2="14" y1="2" y2="6"/><line x1="8" x2="8" y1="10" y2="14"/><line x1="16" x2="16" y1="18" y2="22"/>',
    'image' => '<rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>',
    'file' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/>',
    'home' => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
    'briefcase' => '<path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/><rect width="20" height="14" x="2" y="6" rx="2"/>',
    'folder' => '<path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/>',
    'tag' => '<path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/>',
    'newspaper' => '<path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/>',
    'quote' => '<path d="M16 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z"/><path d="M5 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z"/>',
    'help' => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>',
    'inbox' => '<polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>',
];
$icon = static fn (string $paths, string $class = 'h-4 w-4'): string =>
    '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';

$groups = [
    'Umum' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'admin.dashboard', 'perm' => 'dashboard.view', 'icon' => 'dashboard'],
        ['key' => 'inquiries', 'label' => 'Inquiry', 'route' => 'admin.inquiries', 'perm' => 'inquiries.view', 'icon' => 'inbox', 'badge' => $newInquiries],
    ],
    'Konten' => [
        ['key' => 'pages', 'label' => 'Halaman', 'route' => 'admin.pages', 'perm' => 'content.edit', 'icon' => 'file'],
        ['key' => 'homepage', 'label' => 'Beranda', 'route' => 'admin.homepage', 'perm' => 'content.edit', 'icon' => 'home'],
        ['key' => 'services', 'label' => 'Layanan', 'route' => 'admin.services', 'perm' => 'content.edit', 'icon' => 'briefcase'],
        ['key' => 'projects', 'label' => 'Portofolio', 'route' => 'admin.projects', 'perm' => 'content.edit', 'icon' => 'folder'],
        ['key' => 'project-categories', 'label' => 'Kategori proyek', 'route' => 'admin.project-categories', 'perm' => 'content.edit', 'icon' => 'tag'],
        ['key' => 'articles', 'label' => 'Artikel', 'route' => 'admin.articles', 'perm' => 'content.edit', 'icon' => 'newspaper'],
        ['key' => 'article-categories', 'label' => 'Kategori artikel', 'route' => 'admin.taxonomy', 'params' => ['type' => 'categories'], 'perm' => 'content.edit', 'icon' => 'folder'],
        ['key' => 'article-tags', 'label' => 'Tag artikel', 'route' => 'admin.taxonomy', 'params' => ['type' => 'tags'], 'perm' => 'content.edit', 'icon' => 'tag'],
        ['key' => 'testimonials', 'label' => 'Testimoni', 'route' => 'admin.testimonials', 'perm' => 'content.edit', 'icon' => 'quote'],
        ['key' => 'faqs', 'label' => 'FAQ', 'route' => 'admin.faqs', 'perm' => 'content.edit', 'icon' => 'help'],
        ['key' => 'media', 'label' => 'Media', 'route' => 'admin.media', 'perm' => 'media.view', 'icon' => 'image'],
    ],
    'Pengaturan' => [
        ['key' => 'settings', 'label' => 'Pengaturan website', 'route' => 'admin.settings', 'perm' => 'settings.manage', 'icon' => 'settings'],
    ],
    'Sistem' => [
        ['key' => 'migrations', 'label' => 'Migrasi database', 'route' => 'admin.migrations', 'perm' => 'system.migrations', 'icon' => 'database'],
    ],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($title ?? 'Admin') ?> | <?= e($appName) ?></title>
    <?= vite('resources/js/admin.js') ?>
</head>
<body class="min-h-screen"
      x-data="{ sidebar: window.matchMedia('(min-width: 1024px)').matches }"
      @keydown.escape.window="if (window.innerWidth < 1024) sidebar = false"
      hx-headers="<?= e(json_encode(['X-CSRF-Token' => csrf_token()])) ?>">

<!-- Overlay (hanya HP/tablet) -->
<div class="fixed inset-0 z-30 bg-ink/40 lg:hidden" x-show="sidebar" x-cloak @click="sidebar = false"></div>

<!-- Sidebar -->
<aside class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-line bg-surface transition-transform duration-200"
       :class="sidebar ? '!translate-x-0' : ''">

    <div class="flex h-14 shrink-0 items-center gap-3 border-b border-line px-4">
        <span class="flex h-8 w-8 items-center justify-center rounded-md bg-accent text-sm font-semibold text-white">
            <?= e(mb_strtoupper(mb_substr($appName, 0, 1))) ?>
        </span>
        <div class="min-w-0 leading-tight">
            <p class="truncate text-sm font-semibold"><?= e($appName) ?></p>
            <p class="text-xs text-muted">Admin</p>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto p-3" aria-label="Menu utama">
        <?php foreach ($groups as $label => $items): ?>
            <?php $visible = array_filter($items, static fn (array $i): bool => can($i['perm'])); ?>
            <?php if ($visible === []) { continue; } ?>
            <div class="mb-4">
                <p class="px-2 pb-1 text-xs font-medium text-muted"><?= e($label) ?></p>
                <div class="grid gap-1">
                    <?php foreach ($visible as $item): ?>
                        <?php $isActive = $active === $item['key']; ?>
                        <a href="<?= e(route($item['route'], $item['params'] ?? [])) ?>"
                           class="flex h-9 items-center gap-2 rounded-md px-2 text-sm font-medium transition-colors <?= $isActive ? 'bg-paper text-ink' : 'text-muted hover:bg-paper hover:text-ink' ?>"
                           <?= $isActive ? 'aria-current="page"' : '' ?>>
                            <?= $icon($icons[$item['icon']]) ?>
                            <span class="min-w-0 flex-1 truncate"><?= e($item['label']) ?></span>
                            <?php if (!empty($item['badge'])): ?>
                                <span class="rounded-full bg-accent px-1.5 text-xs font-medium text-white"
                                      aria-label="<?= e($item['badge']) ?> baru"><?= e($item['badge'] > 99 ? '99+' : $item['badge']) ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </nav>

    <!-- User card -->
    <div class="relative shrink-0 border-t border-line p-3" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
        <div x-show="open" x-cloak role="menu"
             class="absolute inset-x-3 bottom-full mb-2 rounded-md border border-line bg-surface p-1 shadow-md">
            <form method="post" action="<?= e(route('admin.logout')) ?>">
                <?= csrf_field() ?>
                <button type="submit" role="menuitem"
                        class="flex h-9 w-full items-center gap-2 rounded-sm px-2 text-sm transition-colors hover:bg-paper">
                    <?= $icon('<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>') ?>
                    Keluar
                </button>
            </form>
        </div>

        <button type="button"
                class="flex w-full items-center gap-3 rounded-md p-2 text-left transition-colors hover:bg-paper focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent/40"
                @click="open = !open" :aria-expanded="open" aria-haspopup="menu">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-line bg-paper text-sm font-medium">
                <?= e($initial) ?>
            </span>
            <span class="min-w-0 flex-1 leading-tight">
                <span class="block truncate text-sm font-medium"><?= e($user['name'] ?? '') ?></span>
                <span class="block truncate text-xs text-muted"><?= e($user['email'] ?? '') ?></span>
            </span>
            <?= $icon('<path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/>', 'h-4 w-4 shrink-0 text-muted') ?>
        </button>
    </div>
</aside>

<!-- Konten -->
<div class="transition-[padding] duration-200" :class="sidebar ? 'lg:pl-64' : ''">
    <header class="sticky top-0 z-20 flex h-14 items-center gap-3 border-b border-line bg-surface px-4 lg:px-6">
        <button type="button" class="btn btn-ghost btn-icon -ml-1" @click="sidebar = !sidebar" aria-label="Buka atau tutup sidebar">
            <?= $icon('<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/>') ?>
        </button>
        <span class="separator-v" aria-hidden="true"></span>

        <nav aria-label="Breadcrumb" class="flex min-w-0 items-center gap-1.5 text-sm">
            <span class="text-muted">Admin</span>
            <?= $icon('<path d="m9 18 6-6-6-6"/>', 'h-3.5 w-3.5 shrink-0 text-muted') ?>
            <span class="truncate font-medium" aria-current="page"><?= e($title ?? 'Admin') ?></span>
        </nav>

        <a href="<?= e(route('home')) ?>" target="_blank" rel="noopener"
           class="btn btn-outline btn-sm ml-auto">
            <span class="hidden sm:inline">Lihat situs</span>
            <?= $icon('<path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>') ?>
        </a>
    </header>

    <main class="mx-auto w-full max-w-6xl p-4 lg:p-6">
        <?php /* $content adalah HTML hasil render view, bukan input pengguna */ ?>
        <?= $content ?>
    </main>
</div>

<?php if (can('media.view')): ?>
    <?php $this->partial('admin/partials/media-modal'); ?>
<?php endif; ?>

<?php $this->partial('admin/partials/toaster'); ?>
</body>
</html>