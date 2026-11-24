<?php
$this->layout('admin/layout', ['title' => 'Media', 'active' => 'media']);
$canUpload = can('media.upload');
$canDelete = can('media.delete');
$pageUrl = static fn (int $p): string => route('admin.media') . ($p > 1 ? '?page=' . $p : '');
?>

<div class="mb-6">
    <h1 class="text-2xl font-semibold tracking-tight">Media</h1>
    <p class="mt-1 max-w-prose text-sm text-muted">
        Gambar yang diunggah di sini bisa dipakai di halaman, layanan, proyek, dan artikel.
    </p>
</div>

<?php if ($canUpload): ?>
    <div class="card mb-6">
        <div class="card-header">
            <h2 class="card-title text-lg">Unggah gambar</h2>
            <p class="card-description">
                JPG, PNG, WebP, atau GIF. Maksimal <?= e($maxLabel) ?> per file, sampai 10 file sekaligus.
            </p>
        </div>
        <div class="card-content">
            <form method="post" action="<?= e(route('admin.media.upload')) ?>" enctype="multipart/form-data"
                  class="flex flex-col gap-3 sm:flex-row">
                <?= csrf_field() ?>
                <input type="file" name="files[]" multiple required
                       accept="image/jpeg,image/png,image/webp,image/gif"
                       aria-label="Pilih gambar"
                       class="input cursor-pointer pt-1.5 file:mr-3 file:border-0 file:bg-transparent file:text-sm file:font-medium">
                <button type="submit" class="btn btn-primary shrink-0">Unggah</button>
            </form>
        </div>
    </div>
<?php endif; ?>

<div x-data="{ items: <?= e(json_encode($items, JSON_UNESCAPED_UNICODE)) ?>, sel: null }">
    <?php if ($items === []): ?>
        <div class="card">
            <div class="card-content flex flex-col items-center gap-1 py-12 text-center">
                <p class="font-medium">Belum ada gambar</p>
                <p class="text-sm text-muted">Unggah gambar pertama lewat formulir di atas.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6">
            <template x-for="it in items" :key="it.id">
                <button type="button" @click="sel = it"
                        class="group overflow-hidden rounded-lg border border-line bg-surface text-left shadow-sm transition-colors hover:border-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent/40">
                    <img :src="it.url" :alt="it.alt || it.name" loading="lazy"
                         class="aspect-square w-full bg-paper object-cover">
                    <span class="block truncate px-2 py-1.5 text-xs text-muted" x-text="it.name"></span>
                </button>
            </template>
        </div>

        <div class="mt-6 flex items-center justify-between text-sm">
            <p class="text-muted"><?= e($total) ?> gambar · halaman <?= e($page) ?> dari <?= e($pages) ?></p>
            <div class="flex gap-2">
                <?php if ($page > 1): ?>
                    <a href="<?= e($pageUrl($page - 1)) ?>" class="btn btn-outline btn-sm">Sebelumnya</a>
                <?php endif; ?>
                <?php if ($page < $pages): ?>
                    <a href="<?= e($pageUrl($page + 1)) ?>" class="btn btn-outline btn-sm">Berikutnya</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Detail gambar -->
    <div x-show="sel" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
         role="dialog" aria-modal="true" aria-label="Detail gambar"
         @keydown.escape.window="sel = null">
        <div class="absolute inset-0 bg-ink/50" @click="sel = null"></div>

        <div class="card relative max-h-full w-full max-w-3xl overflow-y-auto">
            <template x-if="sel">
                <div class="grid gap-0 md:grid-cols-2">
                    <div class="flex items-center justify-center rounded-t-xl bg-paper p-3 md:rounded-l-xl md:rounded-tr-none">
                        <img :src="sel.url" :alt="sel.alt || sel.name" class="max-h-80 w-full object-contain">
                    </div>

                    <div class="grid content-start gap-4 p-5">
                        <div class="flex items-start justify-between gap-3">
                            <p class="min-w-0 break-words text-sm font-semibold" x-text="sel.name"></p>
                            <button type="button" class="btn btn-ghost btn-icon -mr-2 -mt-1 shrink-0" @click="sel = null" aria-label="Tutup">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                            </button>
                        </div>

                        <dl class="grid grid-cols-2 gap-x-3 gap-y-1 text-xs">
                            <dt class="text-muted">Ukuran file</dt><dd x-text="sel.size"></dd>
                            <dt class="text-muted">Dimensi</dt><dd x-text="sel.width + ' × ' + sel.height + ' px'"></dd>
                            <dt class="text-muted">Tipe</dt><dd x-text="sel.mime"></dd>
                            <dt class="text-muted">Diunggah</dt><dd x-text="sel.date"></dd>
                        </dl>

                        <div class="grid gap-2">
                            <label class="label" for="media-url">URL</label>
                            <input id="media-url" type="text" readonly class="input text-xs" :value="sel.url" @focus="$event.target.select()">
                        </div>

                        <?php if ($canUpload): ?>
                            <form method="post" :action="sel.update_url" class="grid gap-2">
                                <?= csrf_field() ?>
                                <input type="hidden" name="page" value="<?= e($page) ?>">
                                <label class="label" for="media-alt">Alt text</label>
                                <input id="media-alt" name="alt" type="text" maxlength="255" class="input"
                                       :value="sel.alt" placeholder="Deskripsikan isi gambar">
                                <p class="text-xs text-muted">Dibaca screen reader dan membantu SEO gambar.</p>
                                <button type="submit" class="btn btn-primary btn-sm justify-self-start">Simpan alt text</button>
                            </form>
                        <?php endif; ?>

                        <?php if ($canDelete): ?>
                            <form method="post" :action="sel.delete_url" class="border-t border-line pt-4"
                                  @submit="if (!confirm('Hapus gambar ini secara permanen?')) $event.preventDefault()">
                                <?= csrf_field() ?>
                                <input type="hidden" name="page" value="<?= e($page) ?>">
                                <button type="submit" class="btn btn-outline btn-sm text-red-700 hover:bg-red-50">Hapus gambar</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>