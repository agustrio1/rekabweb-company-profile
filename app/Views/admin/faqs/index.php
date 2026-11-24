<?php
$this->layout('admin/layout', ['title' => 'FAQ', 'active' => 'faqs']);

$arrow = static fn (string $path): string =>
    '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
?>

<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">FAQ</h1>
        <p class="mt-1 max-w-prose text-sm text-muted">
            Pertanyaan yang sering diajukan calon klien. Urutan di sini adalah urutan tampil di website.
        </p>
    </div>
    <?php if (can('content.create')): ?>
        <a href="<?= e(route('admin.faqs.create')) ?>" class="btn btn-primary">FAQ baru</a>
    <?php endif; ?>
</div>

<?php if ($rows === []): ?>
    <div class="card">
        <div class="card-content flex flex-col items-center gap-1 py-12 text-center">
            <p class="font-medium">Belum ada FAQ</p>
            <p class="text-sm text-muted">Tambahkan pertanyaan pertama lewat tombol di atas.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                <tr class="border-b border-line text-muted">
                    <th class="h-10 w-20 px-4 font-medium">Urutan</th>
                    <th class="h-10 px-4 font-medium">Pertanyaan</th>
                    <th class="hidden h-10 px-4 font-medium md:table-cell">Kategori</th>
                    <th class="h-10 px-4 font-medium">Status</th>
                    <th class="h-10 px-4"><span class="sr-only">Aksi</span></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr class="border-b border-line transition-colors last:border-0 hover:bg-paper/60">
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                <form method="post" action="<?= e($row['move_url']) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="dir" value="up">
                                    <button type="submit" class="btn btn-outline btn-icon" <?= $row['is_first'] ? 'disabled' : '' ?>
                                            aria-label="Naikkan pertanyaan"><?= $arrow('<path d="m18 15-6-6-6 6"/>') ?></button>
                                </form>
                                <form method="post" action="<?= e($row['move_url']) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="dir" value="down">
                                    <button type="submit" class="btn btn-outline btn-icon" <?= $row['is_last'] ? 'disabled' : '' ?>
                                            aria-label="Turunkan pertanyaan"><?= $arrow('<path d="m6 9 6 6 6-6"/>') ?></button>
                                </form>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <a href="<?= e($row['edit_url']) ?>" class="font-medium hover:underline"><?= e($row['question']) ?></a>
                        </td>
                        <td class="hidden px-4 py-3 text-muted md:table-cell"><?= e($row['category'] !== '' ? $row['category'] : '-') ?></td>
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
                                          @submit="if (!confirm('Hapus FAQ ini secara permanen?')) $event.preventDefault()">
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

    <p class="mt-4 text-sm text-muted"><?= e(count($rows)) ?> FAQ</p>
<?php endif; ?>