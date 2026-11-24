<?php
$this->layout('admin/layout', ['title' => 'Testimoni', 'active' => 'testimonials']);

$arrow = static fn (string $path): string =>
    '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
?>

<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">Testimoni</h1>
        <p class="mt-1 max-w-prose text-sm text-muted">
            Kutipan dari klien. Urutan di sini adalah urutan tampil di website. Pasang hanya testimoni asli yang sudah diizinkan klien.
        </p>
    </div>
    <?php if (can('content.create')): ?>
        <a href="<?= e(route('admin.testimonials.create')) ?>" class="btn btn-primary">Testimoni baru</a>
    <?php endif; ?>
</div>

<?php if ($rows === []): ?>
    <div class="card">
        <div class="card-content flex flex-col items-center gap-1 py-12 text-center">
            <p class="font-medium">Belum ada testimoni</p>
            <p class="text-sm text-muted">Tambahkan testimoni pertama lewat tombol di atas.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                <tr class="border-b border-line text-muted">
                    <th class="h-10 w-20 px-4 font-medium">Urutan</th>
                    <th class="h-10 px-4 font-medium">Klien</th>
                    <th class="h-10 px-4 font-medium">Status</th>
                    <th class="h-10 px-4"><span class="sr-only">Aksi</span></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <?php
                    $sub = trim($row['job_title'] . ($row['job_title'] !== '' && $row['company'] !== '' ? ', ' : '') . $row['company']);
                    ?>
                    <tr class="border-b border-line transition-colors last:border-0 hover:bg-paper/60">
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                <form method="post" action="<?= e($row['move_url']) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="dir" value="up">
                                    <button type="submit" class="btn btn-outline btn-icon" <?= $row['is_first'] ? 'disabled' : '' ?>
                                            aria-label="Naikkan <?= e($row['name']) ?>"><?= $arrow('<path d="m18 15-6-6-6 6"/>') ?></button>
                                </form>
                                <form method="post" action="<?= e($row['move_url']) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="dir" value="down">
                                    <button type="submit" class="btn btn-outline btn-icon" <?= $row['is_last'] ? 'disabled' : '' ?>
                                            aria-label="Turunkan <?= e($row['name']) ?>"><?= $arrow('<path d="m6 9 6 6 6-6"/>') ?></button>
                                </form>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <?php if ($row['photo_path'] !== null): ?>
                                    <img src="<?= e(\App\Services\Media::url((string) $row['photo_path'])) ?>" alt=""
                                         loading="lazy" class="h-10 w-10 shrink-0 rounded-full border border-line object-cover">
                                <?php else: ?>
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-line bg-paper text-sm font-medium">
                                        <?= e(mb_strtoupper(mb_substr((string) $row['name'], 0, 1))) ?>
                                    </span>
                                <?php endif; ?>
                                <div class="min-w-0">
                                    <a href="<?= e($row['edit_url']) ?>" class="font-medium hover:underline"><?= e($row['name']) ?></a>
                                    <?php if ($sub !== ''): ?><p class="truncate text-xs text-muted"><?= e($sub) ?></p><?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <?php if ($row['status'] === 'published'): ?>
                                <span class="badge badge-success">Terbit</span>
                            <?php else: ?>
                                <span class="badge border-line bg-paper text-muted">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="<?= e($row['edit_url']) ?>" class="btn btn-outline btn-sm">Edit</a>
                                <?php if ($row['can_delete']): ?>
                                    <form method="post" action="<?= e($row['delete_url']) ?>"
                                          @submit="if (!confirm('Hapus testimoni ini secara permanen?')) $event.preventDefault()">
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

    <p class="mt-4 text-sm text-muted"><?= e(count($rows)) ?> testimoni</p>
<?php endif; ?>