<?php
$this->layout('admin/layout', ['title' => 'Kategori proyek', 'active' => 'project-categories']);
?>

<div class="mb-6">
    <h1 class="text-2xl font-semibold tracking-tight">Kategori proyek</h1>
    <p class="mt-1 max-w-prose text-sm text-muted">
        Kelompokkan proyek portofolio, misalnya "Renovasi", "Pembangunan", atau "Interior". Maksimal <?= e($max) ?> kategori.
    </p>
</div>

<div class="card mb-6">
    <div class="card-header">
        <h2 class="card-title text-base">Kategori baru</h2>
    </div>
    <div class="card-content">
        <form method="post" action="<?= e(route('admin.project-categories.store')) ?>" class="flex flex-col gap-2 sm:flex-row">
            <?= csrf_field() ?>
            <input type="text" name="name" maxlength="80" required autocomplete="off"
                   placeholder="Nama kategori" aria-label="Nama kategori baru" class="input sm:max-w-sm">
            <button type="submit" class="btn btn-primary">Tambah</button>
        </form>
    </div>
</div>

<?php if ($rows === []): ?>
    <div class="card">
        <div class="card-content flex flex-col items-center gap-1 py-12 text-center">
            <p class="font-medium">Belum ada kategori</p>
            <p class="text-sm text-muted">Tambahkan kategori pertama lewat formulir di atas.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card overflow-hidden">
        <ul>
            <?php foreach ($rows as $row): ?>
                <?php $id = (int) $row['id']; ?>
                <li class="flex flex-col gap-3 border-b border-line p-4 last:border-0 sm:flex-row sm:items-center">
                    <form method="post" action="<?= e(route('admin.project-categories.update', ['id' => $id])) ?>"
                          class="flex flex-1 flex-col gap-2 sm:flex-row sm:items-center">
                        <?= csrf_field() ?>
                        <input type="text" name="name" maxlength="80" required autocomplete="off"
                               value="<?= e($row['name']) ?>" aria-label="Nama kategori" class="input sm:max-w-sm">
                        <button type="submit" class="btn btn-outline btn-sm">Simpan</button>
                        <span class="text-xs text-muted">
                            <?= e($row['project_count']) ?> proyek · <span class="font-mono"><?= e($row['slug']) ?></span>
                        </span>
                    </form>

                    <?php if ($canDelete): ?>
                        <form method="post" action="<?= e(route('admin.project-categories.delete', ['id' => $id])) ?>"
                              @submit="if (!confirm('Hapus kategori ini? Proyek di dalamnya tidak ikut terhapus, tapi menjadi tanpa kategori.')) $event.preventDefault()">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline btn-sm text-red-700 hover:bg-red-50">Hapus</button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>