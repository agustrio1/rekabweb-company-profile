<?php
/** @var string $name  nama field (dikirim sebagai name[]) */
/** @var array  $items list: tiap item punya id (int) dan url */
/** @var int    $max   jumlah gambar maksimal */
$config = [
    'items' => array_values($items),
    'max' => (int) $max,
];
?>
<div x-data="{
        ...<?= e(json_encode($config, JSON_UNESCAPED_UNICODE)) ?>,
        add() {
            if (this.items.length >= this.max) return;
            window.dispatchEvent(new CustomEvent('pick-media', { detail: { callback: (it) => {
                if (this.items.length >= this.max || this.items.some((x) => x.id === it.id)) return;
                this.items.push({ id: it.id, url: it.url });
            } } }));
        },
        remove(i) { this.items.splice(i, 1); },
        move(i, d) {
            const j = i + d;
            if (j < 0 || j >= this.items.length) return;
            [this.items[i], this.items[j]] = [this.items[j], this.items[i]];
        }
     }">
    <template x-for="it in items" :key="it.id">
        <input type="hidden" name="<?= e($name) ?>[]" :value="it.id">
    </template>

    <div class="mb-2 flex items-center justify-between">
        <span class="text-xs text-muted" x-text="items.length + ' / ' + max + ' gambar'"></span>
    </div>

    <p x-show="!items.length" class="rounded-md border border-dashed border-line p-4 text-center text-sm text-muted">
        Belum ada gambar galeri.
    </p>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
        <template x-for="(it, i) in items" :key="it.id">
            <div class="overflow-hidden rounded-lg border border-line bg-surface">
                <img :src="it.url" alt="" loading="lazy" class="aspect-square w-full bg-paper object-cover">
                <div class="flex items-center justify-between gap-1 p-1">
                    <button type="button" class="btn btn-ghost btn-icon" :disabled="i === 0" @click="move(i, -1)" aria-label="Geser ke kiri">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                    <button type="button" class="btn btn-ghost btn-sm text-red-700" @click="remove(i)">Hapus</button>
                    <button type="button" class="btn btn-ghost btn-icon" :disabled="i === items.length - 1" @click="move(i, 1)" aria-label="Geser ke kanan">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>
            </div>
        </template>
    </div>

    <button type="button" class="btn btn-outline btn-sm mt-3" x-show="items.length < max" @click="add()">
        + Tambah gambar
    </button>
</div>