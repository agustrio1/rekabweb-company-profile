<?php
$this->layout('admin/layout', ['title' => 'Portofolio', 'active' => 'projects']);

$arrow = static fn (string $path): string =>
    '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
?>

<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">Portofolio</h1>
        <p class="mt-1 max-w-prose text-sm text-muted">
            Proyek yang sudah dikerjakan. Urutan di sini adalah urutan tampil di website.
        </p>
    </div>
    <div class="flex gap-2">
        <a href="<?= e(route('admin.project-categories')) ?>" class="btn btn-outline">Kategori</a>
        <?php if (can('content.create')): ?>
            <a href="<?= e(route('admin.projects.create')) ?>" class="btn btn-primary">Proyek baru</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($rows === []): ?>
    <div class="card">
        <div class="card-content flex flex-col items-center gap-1 py-12 text-center">
            <p class="font-medium">Belum ada proyek</p>
            <p class="text-sm text-muted">Buat proyek pertama lewat tombol di atas.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                <tr class="border-b border-line text-muted">
                    <th class="h-10 w-20 px-4 font-medium">Urutan</th>
                    <th class="h-10 px-4 font-medium">Proyek</th>
                    <th class="hidden h-10 px-4 font-medium md:table-cell">Kategori</th>
                    <th class="h-10 px-4 font-medium">Status</th>
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
                            <div class="flex gap-1">
                                <form method="post" action="<?= e($row['move_url']) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="dir" value="up">
                                    <button type="submit" class="btn btn-outline btn-icon" <?= $row['is_first'] ? 'disabled' : '' ?>
                                            aria-label="Naikkan <?= e($row['title']) ?>"><?= $arrow('<path d="m18 15-6-6-6 6"/>') ?></button>
                                </form>
                                <form method="post" action="<?= e($row['move_url']) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="dir" value="down">
                                    <button type="submit" class="btn btn-outline btn-icon" <?= $row['is_last'] ? 'disabled' : '' ?>
                                            aria-label="Turunkan <?= e($row['title']) ?>"><?= $arrow('<path d="m6 9 6 6 6-6"/>') ?></button>
                                </form>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <a href="<?= e($row['edit_url']) ?>" class="font-medium hover:underline"><?= e($row['title']) ?></a>
                            <p class="font-mono text-xs text-muted">/proyek/<?= e($row['slug']) ?></p>
                        </td>
                        <td class="hidden px-4 py-3 text-muted md:table-cell"><?= e($row['category'] ?? 'Tanpa kategori') ?></td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                <?php if ($scheduled): ?>
                                    <span class="badge border-line bg-paper text-muted">Terjadwal</span>
                                <?php elseif ($row['status'] === 'published'): ?>
                                    <span class="badge badge-success">Terbit</span>
                                <?php else: ?>
                                    <span class="badge border-line bg-paper text-muted">Draft</span>
                                <?php endif; ?>
                                <?php if ((int) $row['is_featured'] === 1): ?>
                                    <span class="badge border-line bg-paper text-ink">Unggulan</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="<?= e($row['edit_url']) ?>" class="btn btn-outline btn-sm">Edit</a>
                                <?php if ($row['can_delete']): ?>
                                    <form method="post" action="<?= e($row['delete_url']) ?>"
                                          @submit="if (!confirm('Hapus proyek ini secara permanen?')) $event.preventDefault()">
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

    <p class="mt-4 text-sm text-muted"><?= e(count($rows)) ?> proyek</p>
<?php endif; ?>