<?php
$this->layout('admin/layout', ['title' => 'Dashboard', 'active' => 'dashboard']);

$canSettings = can('settings.manage');
$canContent = can('content.edit');
$canInquiries = can('inquiries.view');
$checklistTotal = count($checklist);
$pagesUrl = route('admin.pages');

$stats = [
    [
        'label' => 'Inquiry baru',
        'value' => $inquiry['new'],
        'url' => route('admin.inquiries') . '?status=new',
        'show' => $canInquiries,
        'alert' => $inquiry['new'] > 0,
    ],
    ['label' => 'Halaman terbit', 'value' => $summary['pages']['live'], 'url' => $pagesUrl . '?status=published', 'show' => $canContent, 'alert' => false],
    ['label' => 'Layanan terbit', 'value' => $summary['services']['live'], 'url' => route('admin.services'), 'show' => $canContent, 'alert' => false],
    ['label' => 'Gambar di Media', 'value' => $mediaCount, 'url' => route('admin.media'), 'show' => can('media.view'), 'alert' => false],
];

$statusBadge = static function (array $row): array {
    if ($row['status'] === 'published') {
        $future = $row['published_at'] !== null && strtotime((string) $row['published_at']) > time();

        return $future ? ['Terjadwal', 'border-line bg-paper text-muted'] : ['Terbit', 'badge-success'];
    }

    return ['Draft', 'border-line bg-paper text-muted'];
};
?>

<div class="mb-6">
    <h1 class="text-2xl font-semibold tracking-tight">Halo, <?= e($user['name']) ?></h1>
    <p class="mt-1 text-sm text-muted">Ringkasan konten dan kelengkapan website. Kamu masuk sebagai <?= e($user['role_label']) ?>.</p>
</div>

<?php if ($noindex): ?>
    <div role="alert" class="mb-6 flex items-start gap-3 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
        <p class="flex-1">
            <span class="font-medium">Website belum bisa ditemukan di Google.</span>
            Pengindeksan sedang diblokir.
            <?php if ($canSettings): ?>
                Kalau website sudah siap, ubah di <a href="<?= e(route('admin.settings')) ?>" class="font-medium underline">Pengaturan, tab SEO</a>.
            <?php else: ?>
                Minta Administrator mengubahnya saat website sudah siap.
            <?php endif; ?>
        </p>
    </div>
<?php endif; ?>

<div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
    <?php foreach ($stats as $stat): ?>
        <?php if (!$stat['show']) { continue; } ?>
        <a href="<?= e($stat['url']) ?>"
           class="card p-4 transition-colors hover:border-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent/40 <?= $stat['alert'] ? 'border-amber-300' : '' ?>">
            <p class="text-xs font-medium text-muted"><?= e($stat['label']) ?></p>
            <p class="mt-1 text-3xl font-semibold tracking-tight"><?= e($stat['value']) ?></p>
        </a>
    <?php endforeach; ?>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <div class="grid content-start gap-6 lg:col-span-2">
        <?php if ($canInquiries): ?>
            <div class="card overflow-hidden">
                <div class="card-header flex-row items-center justify-between">
                    <div class="grid gap-1.5">
                        <h2 class="card-title text-base">Inquiry terbaru</h2>
                        <p class="card-description"><?= e($inquiry['total']) ?> inquiry masuk, <?= e($inquiry['new']) ?> belum ditangani.</p>
                    </div>
                    <a href="<?= e(route('admin.inquiries')) ?>" class="btn btn-outline btn-sm">Semua inquiry</a>
                </div>

                <?php if ($recentInquiries === []): ?>
                    <p class="border-t border-line px-6 py-10 text-center text-sm text-muted">Belum ada inquiry.</p>
                <?php else: ?>
                    <ul class="border-t border-line">
                        <?php foreach ($recentInquiries as $item): ?>
                            <li class="flex items-center justify-between gap-3 border-b border-line px-6 py-3 last:border-0">
                                <div class="min-w-0">
                                    <a href="<?= e(route('admin.inquiries.show', ['id' => (int) $item['id']])) ?>"
                                       class="block truncate text-sm font-medium hover:underline"><?= e($item['name']) ?></a>
                                    <p class="truncate text-xs text-muted">
                                        <?= e($item['need_type'] !== '' ? $item['need_type'] : 'Tanpa jenis kebutuhan') ?> ·
                                        <?= e(date('d M Y H:i', (int) strtotime((string) $item['created_at']))) ?>
                                    </p>
                                </div>
                                <?php $this->partial('admin/partials/inquiry-status', ['status' => $item['status']]); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($canContent): ?>
            <div class="card overflow-hidden">
                <div class="card-header">
                    <h2 class="card-title text-base">Ringkasan konten</h2>
                    <p class="card-description">Jumlah konten per modul. Terjadwal berarti sudah berstatus Terbit tapi tanggalnya belum tiba.</p>
                </div>
                <div class="overflow-x-auto border-t border-line">
                    <table class="w-full text-left text-sm">
                        <thead>
                        <tr class="border-b border-line text-muted">
                            <th class="h-10 px-4 font-medium">Modul</th>
                            <th class="h-10 px-4 text-right font-medium">Terbit</th>
                            <th class="h-10 px-4 text-right font-medium">Draft</th>
                            <th class="h-10 px-4 text-right font-medium">Terjadwal</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($summary as $row): ?>
                            <tr class="border-b border-line transition-colors last:border-0 hover:bg-paper/60">
                                <td class="px-4 py-3">
                                    <a href="<?= e(route($row['route'])) ?>" class="font-medium hover:underline"><?= e($row['label']) ?></a>
                                </td>
                                <td class="px-4 py-3 text-right"><?= e($row['live']) ?></td>
                                <td class="px-4 py-3 text-right text-muted"><?= e($row['draft']) ?></td>
                                <td class="px-4 py-3 text-right text-muted"><?= e($row['scheduled']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card overflow-hidden">
                <div class="card-header flex-row items-center justify-between">
                    <div class="grid gap-1.5">
                        <h2 class="card-title text-base">Aktivitas terbaru</h2>
                        <p class="card-description">Halaman yang terakhir diubah.</p>
                    </div>
                    <a href="<?= e($pagesUrl) ?>" class="btn btn-outline btn-sm">Semua halaman</a>
                </div>

                <?php if ($recent === []): ?>
                    <p class="border-t border-line px-6 py-10 text-center text-sm text-muted">Belum ada halaman.</p>
                <?php else: ?>
                    <ul class="border-t border-line">
                        <?php foreach ($recent as $row): ?>
                            <?php [$badgeLabel, $badgeClass] = $statusBadge($row); ?>
                            <li class="flex items-center justify-between gap-3 border-b border-line px-6 py-3 last:border-0">
                                <div class="min-w-0">
                                    <a href="<?= e(route('admin.pages.edit', ['id' => (int) $row['id']])) ?>"
                                       class="block truncate text-sm font-medium hover:underline"><?= e($row['title']) ?></a>
                                    <p class="truncate text-xs text-muted">
                                        <?= e($row['editor'] ?? 'Pengguna dihapus') ?> ·
                                        <?= e(date('d M Y H:i', (int) strtotime((string) $row['updated_at']))) ?>
                                    </p>
                                </div>
                                <span class="badge shrink-0 <?= e($badgeClass) ?>"><?= e($badgeLabel) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="grid content-start gap-6">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title text-base">Kelengkapan website</h2>
                <p class="card-description"><?= e($checklistDone) ?> dari <?= e($checklistTotal) ?> selesai</p>
            </div>
            <ul class="card-content grid gap-3">
                <?php foreach ($checklist as $item): ?>
                    <li class="flex items-start gap-3 text-sm">
                        <?php if ($item['done']): ?>
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-green-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-label="Selesai"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                        <?php else: ?>
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-label="Belum"><circle cx="12" cy="12" r="10"/></svg>
                        <?php endif; ?>
                        <div class="min-w-0">
                            <p class="<?= $item['done'] ? 'text-muted line-through' : 'font-medium' ?>"><?= e($item['label']) ?></p>
                            <?php if (!$item['done']): ?>
                                <p class="text-xs text-muted"><?= e($item['hint']) ?></p>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php if ($canSettings && $checklistDone < $checklistTotal): ?>
                <div class="card-content pt-0">
                    <a href="<?= e(route('admin.settings')) ?>" class="btn btn-outline btn-sm w-full">Buka Pengaturan</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title text-base">Aksi cepat</h2>
            </div>
            <div class="card-content grid gap-2">
                <?php if (can('content.create')): ?>
                    <a href="<?= e(route('admin.articles.create')) ?>" class="btn btn-outline justify-start">Tulis artikel</a>
                    <a href="<?= e(route('admin.pages.create')) ?>" class="btn btn-outline justify-start">Buat halaman baru</a>
                    <a href="<?= e(route('admin.services.create')) ?>" class="btn btn-outline justify-start">Tambah layanan</a>
                    <a href="<?= e(route('admin.projects.create')) ?>" class="btn btn-outline justify-start">Tambah proyek</a>
                <?php endif; ?>
                <?php if ($canContent): ?>
                    <a href="<?= e(route('admin.homepage')) ?>" class="btn btn-outline justify-start">
                        Atur beranda (<?= e($homepage['active']) ?> dari <?= e($homepage['total']) ?> section tampil)
                    </a>
                <?php endif; ?>
                <?php if (can('media.upload')): ?>
                    <a href="<?= e(route('admin.media')) ?>" class="btn btn-outline justify-start">Unggah gambar</a>
                <?php endif; ?>
                <?php if ($canSettings): ?>
                    <a href="<?= e(route('admin.settings')) ?>" class="btn btn-outline justify-start">Pengaturan website</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>