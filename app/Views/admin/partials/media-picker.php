  <?php
/** @var string $name  nama field (menyimpan ID media) */
/** @var string $value ID media terpilih atau '' */
/** @var string $preview URL gambar terpilih atau '' */
/** @var string $id id elemen untuk tombol pilih */
$config = [
    'id' => $value,
    'url' => $preview,
    'listUrl' => route('admin.media.browse'),
    'uploadUrl' => route('admin.media.upload'),
    'canUpload' => can('media.upload'),
];
?>
<div x-data="{
        ...<?= e(json_encode($config, JSON_UNESCAPED_UNICODE)) ?>,
        open: false, items: [], page: 1, pages: 1, loading: false, error: '',
        async load(p = 1) {
            this.loading = true; this.error = '';
            try {
                const r = await fetch(this.listUrl + '?page=' + p, { headers: { 'Accept': 'application/json' } });
                if (!r.ok) throw new Error();
                const d = await r.json();
                this.items = d.items; this.page = d.page; this.pages = d.pages;
            } catch (e) { this.error = 'Gagal memuat gambar.'; }
            this.loading = false;
        },
        show() { this.open = true; this.load(1); },
        choose(it) { this.id = String(it.id); this.url = it.url; this.open = false; },
        clear() { this.id = ''; this.url = ''; },
        async upload(ev) {
            const files = ev.target.files;
            if (!files.length) return;
            const fd = new FormData();
            for (const f of files) fd.append('files[]', f);
            this.loading = true; this.error = '';
            try {
                const r = await fetch(this.uploadUrl, {
                    method: 'POST', body: fd,
                    headers: {
                        'X-CSRF-Token': document.querySelector('meta[name=csrf-token]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const d = await r.json();
                if (d.errors && d.errors.length) this.error = d.errors.join(' ');
                await this.load(1);
            } catch (e) { this.error = 'Unggah gagal. Ukuran file mungkin melebihi batas server.'; }
            ev.target.value = '';
            this.loading = false;
        }
    }">
    <input type="hidden" name="<?= e($name) ?>" :value="id">

    <div class="flex items-center gap-3">
        <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-md border border-line bg-paper text-xs text-muted">
            <img x-show="url" :src="url" alt="" class="h-full w-full object-contain">
            <span x-show="!url">Kosong</span>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" id="<?= e($id) ?>" class="btn btn-outline btn-sm" @click="show()">
                <span x-text="url ? 'Ganti gambar' : 'Pilih gambar'"></span>
            </button>
            <button type="button" class="btn btn-ghost btn-sm" x-show="url" x-cloak @click="clear()">Hapus</button>
        </div>
    </div>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
         role="dialog" aria-modal="true" aria-label="Pilih gambar"
         @keydown.escape.window="open = false">
        <div class="absolute inset-0 bg-ink/50" @click="open = false"></div>

        <div class="card relative flex max-h-full w-full max-w-3xl flex-col">
            <div class="flex items-center justify-between gap-3 border-b border-line p-4">
                <p class="text-sm font-semibold">Pilih gambar</p>
                <div class="flex items-center gap-2">
                    <label x-show="canUpload" class="btn btn-outline btn-sm cursor-pointer">
                        Unggah baru
                        <input type="file" multiple class="sr-only" accept="image/jpeg,image/png,image/webp,image/gif" @change="upload($event)">
                    </label>
                    <button type="button" class="btn btn-ghost btn-icon" @click="open = false" aria-label="Tutup">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
            </div>

            <div class="overflow-y-auto p-4">
                <p x-show="error" x-text="error" class="mb-3 text-sm font-medium text-red-700"></p>
                <p x-show="loading" class="text-sm text-muted">Memuat…</p>
                <p x-show="!loading && !items.length && !error" class="py-8 text-center text-sm text-muted">Belum ada gambar.</p>

                <div class="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-5">
                    <template x-for="it in items" :key="it.id">
                        <button type="button" @click="choose(it)"
                                class="overflow-hidden rounded-md border border-line bg-surface transition-colors hover:border-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent/40"
                                :class="String(it.id) === id ? 'ring-2 ring-accent' : ''">
                            <img :src="it.url" :alt="it.alt || it.name" loading="lazy" class="aspect-square w-full bg-paper object-cover">
                        </button>
                    </template>
                </div>
            </div>

            <div class="flex items-center justify-between border-t border-line p-3 text-sm" x-show="pages > 1">
                <button type="button" class="btn btn-outline btn-sm" :disabled="page <= 1" @click="load(page - 1)">Sebelumnya</button>
                <span class="text-muted" x-text="page + ' / ' + pages"></span>
                <button type="button" class="btn btn-outline btn-sm" :disabled="page >= pages" @click="load(page + 1)">Berikutnya</button>
            </div>
        </div>
    </div>
</div>