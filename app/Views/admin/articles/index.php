<?php
$this->layout('admin/layout', ['title' => 'Artikel', 'active' => 'articles']);

$link = static function (int $p) use ($q, $status): string {
    $qs = http_build_query(array_filter(
        ['q' => $q, 'status' => $status, 'page' => $p > 1 ? $p : null],
        static fn ($v): bool => $v !== null && $v !== ''
    ));

    return route('admin.articles') . ($qs !== '' ? '?' . $qs : '');
};
?>

<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">Artikel</h1>
        <p class="mt-1 max-w-prose text-sm text-muted">
            Tulisan blog perusahaan. Artikel tampil di website berdasarkan tanggal terbit terbaru.
        </p>
    </div>
    <div class="flex gap-2">
        <a href="<?= e(route('admin.taxonomy', ['type' => 'categories'])) ?>" class="btn btn-outline">Kategori</a>
        <a href="<?= e(route('admin.taxonomy', ['type' => 'tags'])) ?>" class="btn btn-outline">Tag</a>
        <?php if (can('content.create')): ?>
            <a href="<?= e(route('admin.articles.create')) ?>" class="btn btn-primary">Artikel baru</a>
        <?php endif; ?>
    </div>
</div>

<form method="get" action="<?= e(route('admin.articles')) ?>" class="mb-4 flex flex-col gap-2 sm:flex-row">
    <input type="search" name="q" value="<?= e($q) ?>" maxlength="100" placeholder="Cari judul atau slug"
           aria-label="Cari artikel" class="input sm:max-w-xs">
    <select name="status" aria-label="Filter status" class="input sm:w-40">
        <option value="">Semua status</option>
        <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Terbit</option>
        <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
    </select>
    <button type="submit" class="btn btn-outline">Cari</button>
</form>

<?php if ($rows === []): ?>
    <div class="card">
        <div class="card-content flex flex-col items-center gap-1 py-12 text-center">
            <p class="font-medium"><?= $q !== '' || $status !== '' ? 'Tidak ada artikel yang cocok' : 'Belum ada artikel' ?></p>
            <p class="text-sm text-muted">
                <?= $q !== '' || $status !== '' ? 'Ubah kata kunci atau filter.' : 'Tulis artikel pertama lewat tombol di atas.' ?>
            </p>
        </div>
    </div>
<?php else: ?>
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                <tr class="border-b border-line text-muted">
                    <th class="h-10 px-4 font-medium">Judul</th>
                    <th class="hidden h-10 px-4 font-medium md:table-cell">Kategori</th>
                    <th class="hidden h-10 px-4 font-medium lg:table-cell">Penulis</th>
                    <th class="h-10 px-4 font-medium">Status</th>
                    <th class="hidden h-10 px-4 font-medium sm:table-cell">Diubah</th>
                    <th class="h-10 px-4"><span class="sr-only">Aksi</span></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <?php
                    $scheduled = $row['status'] === 'published'
                        && $row['published_at'] !== null
                        && strtotime((string) $row['published_at']) > time();
                    ?>
                    <tr class="border-b border-line transition-colors last:border-0 hover:bg-paper/60">
                        <td class="px-4 py-3">
                            <a href="<?= e($row['edit_url']) ?>" class="font-medium hover:underline"><?= e($row['title']) ?></a>
                            <p class="font-mono text-xs text-muted">/blog/<?= e($row['slug']) ?></p>
                        </td>
                        <td class="hidden px-4 py-3 text-muted md:table-cell"><?= e($row['category'] ?? 'Tanpa kategori') ?></td>
                        <td class="hidden px-4 py-3 text-muted lg:table-cell"><?= e($row['author'] ?? '-') ?></td>
                        <td class="px-4 py-3">
                            <?php if ($scheduled): ?>
                                <span class="badge border-line bg-paper text-muted">Terjadwal</span>
                            <?php elseif ($row['status'] === 'published'): ?>
                                <span class="badge badge-success">Terbit</span>
                            <?php else: ?>
                                <span class="badge border-line bg-paper text-muted">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td class="hidden whitespace-nowrap px-4 py-3 text-muted sm:table-cell">
                            <?= e(date('d M Y H:i', (int) strtotime((string) $row['updated_at']))) ?>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="<?= e($row['edit_url']) ?>" class="btn btn-outline btn-sm">Edit</a>
                                <?php if ($row['can_delete']): ?>
                                    <form method="post" action="<?= e($row['delete_url']) ?>"
                                          @submit="if (!confirm('Hapus artikel ini secara permanen?')) $event.preventDefault()">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline btn-sm text-red-700 hover:bg-red-50">Hapus</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 flex items-center justify-between text-sm">
        <p class="text-muted"><?= e($total) ?> artikel · halaman <?= e($page) ?> dari <?= e($pages) ?></p>
        <div class="flex gap-2">
            <?php if ($page > 1): ?>
                <a href="<?= e($link($page - 1)) ?>" class="btn btn-outline btn-sm">Sebelumnya</a>
            <?php endif; ?>
            <?php if ($page < $pages): ?>
                <a href="<?= e($link($page + 1)) ?>" class="btn btn-outline btn-sm">Berikutnya</a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>