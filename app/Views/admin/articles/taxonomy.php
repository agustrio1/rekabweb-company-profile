<?php
$this->layout('admin/layout', ['title' => $meta['label'], 'active' => 'article-' . $type]);

$other = $type === 'categories' ? 'tags' : 'categories';
$otherMeta = \App\Services\ArticleTaxonomy::meta($other);
?>

<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight"><?= e($meta['label']) ?></h1>
        <p class="mt-1 max-w-prose text-sm text-muted">
            <?= e($meta['description']) ?> Maksimal <?= e($meta['max']) ?> <?= e($meta['noun']) ?>.
        </p>
    </div>
    <div class="flex gap-2">
        <a href="<?= e(route('admin.taxonomy', ['type' => $other])) ?>" class="btn btn-outline"><?= e($otherMeta['label']) ?></a>
        <a href="<?= e(route('admin.articles')) ?>" class="btn btn-outline">Artikel</a>
    </div>
</div>

<div class="card mb-6">
    <div class="card-header">
        <h2 class="card-title text-base"><?= e($meta['singular']) ?> baru</h2>
    </div>
    <div class="card-content">
        <form method="post" action="<?= e(route('admin.taxonomy.store', ['type' => $type])) ?>" class="flex flex-col gap-2 sm:flex-row">
            <?= csrf_field() ?>
            <input type="text" name="name" maxlength="<?= e($meta['name_max']) ?>" required autocomplete="off"
                   placeholder="Nama <?= e($meta['noun']) ?>" aria-label="Nama <?= e($meta['noun']) ?> baru" class="input sm:max-w-sm">
            <button type="submit" class="btn btn-primary">Tambah</button>
        </form>
    </div>
</div>

<?php if ($rows === []): ?>
    <div class="card">
        <div class="card-content flex flex-col items-center gap-1 py-12 text-center">
            <p class="font-medium">Belum ada <?= e($meta['noun']) ?></p>
            <p class="text-sm text-muted">Tambahkan yang pertama lewat formulir di atas.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card overflow-hidden">
        <ul>
            <?php foreach ($rows as $row): ?>
                <?php $id = (int) $row['id']; ?>
                <li class="flex flex-col gap-3 border-b border-line p-4 last:border-0 sm:flex-row sm:items-center">
                    <form method="post" action="<?= e(route('admin.taxonomy.update', ['type' => $type, 'id' => $id])) ?>"
                          class="flex flex-1 flex-col gap-2 sm:flex-row sm:items-center">
                        <?= csrf_field() ?>
                        <input type="text" name="name" maxlength="<?= e($meta['name_max']) ?>" required autocomplete="off"
                               value="<?= e($row['name']) ?>" aria-label="Nama <?= e($meta['noun']) ?>" class="input sm:max-w-sm">
                        <button type="submit" class="btn btn-outline btn-sm">Simpan</button>
                        <span class="text-xs text-muted">
                            <?= e($row['item_count']) ?> artikel · <span class="font-mono"><?= e($row['slug']) ?></span>
                        </span>
                    </form>

                    <?php if ($canDelete): ?>
                        <form method="post" action="<?= e(route('admin.taxonomy.delete', ['type' => $type, 'id' => $id])) ?>"
                              @submit="if (!confirm(<?= e(json_encode('Hapus ' . $meta['noun'] . ' ini? ' . $meta['delete_note'], JSON_UNESCAPED_UNICODE)) ?>)) $event.preventDefault()">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline btn-sm text-red-700 hover:bg-red-50">Hapus</button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>