<?php
$this->layout('admin/layout', ['title' => 'Beranda', 'active' => 'homepage']);

$chevron = static fn (string $path): string =>
    '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
?>

<div class="mb-6">
    <h1 class="text-2xl font-semibold tracking-tight">Beranda</h1>
    <p class="mt-1 max-w-prose text-sm text-muted">
        Atur section yang tampil di halaman depan, urutannya, dan isinya. Ketuk judul section untuk membuka isinya.
    </p>
</div>

<form method="post" action="<?= e(route('admin.homepage.update')) ?>" novalidate
      x-data="{
          order: <?= e(json_encode(array_keys($sections))) ?>,
          pos(k) { return this.order.indexOf(k); },
          move(k, d) {
              const i = this.order.indexOf(k), j = i + d;
              if (j < 0 || j >= this.order.length) return;
              [this.order[i], this.order[j]] = [this.order[j], this.order[i]];
          }
      }">
    <?= csrf_field() ?>

    <template x-for="k in order" :key="k">
        <input type="hidden" name="order[]" :value="k">
    </template>

    <div class="flex flex-col gap-3">
        <?php foreach ($sections as $key => $sec): ?>
            <?php
            $def = $defs[$key];
            $se = $errors[$key] ?? [];
            $hasError = $se !== [];
            $rep = is_array($def['repeater'] ?? null) ? $def['repeater'] : null;
            ?>
            <div class="card" :style="'order: ' + pos('<?= e($key) ?>')"
                 x-data="{ open: <?= $hasError ? 'true' : 'false' ?> }">

                <div class="flex items-center gap-2 p-3 sm:p-4">
                    <div class="flex shrink-0 flex-col">
                        <button type="button" class="btn btn-ghost h-7 w-8 p-0"
                                :disabled="pos('<?= e($key) ?>') === 0"
                                @click="move('<?= e($key) ?>', -1)"
                                aria-label="Naikkan <?= e($def['label']) ?>"><?= $chevron('<path d="m18 15-6-6-6 6"/>') ?></button>
                        <button type="button" class="btn btn-ghost h-7 w-8 p-0"
                                :disabled="pos('<?= e($key) ?>') === order.length - 1"
                                @click="move('<?= e($key) ?>', 1)"
                                aria-label="Turunkan <?= e($def['label']) ?>"><?= $chevron('<path d="m6 9 6 6 6-6"/>') ?></button>
                    </div>

                    <button type="button" class="min-w-0 flex-1 rounded-md px-1 py-1 text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent/40"
                            @click="open = !open" :aria-expanded="open">
                        <span class="flex items-center gap-2">
                            <span class="truncate text-sm font-semibold"><?= e($def['label']) ?></span>
                            <?php if ($hasError): ?>
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-red-600" aria-label="Ada kesalahan"></span>
                            <?php endif; ?>
                        </span>
                        <span class="block truncate text-xs text-muted"><?= e($def['description']) ?></span>
                    </button>

                    <label class="flex shrink-0 cursor-pointer items-center gap-2 text-sm">
                        <input type="checkbox" name="active[<?= e($key) ?>]" value="1"
                               <?= $sec['active'] ? 'checked' : '' ?>
                               aria-label="Tampilkan <?= e($def['label']) ?>"
                               class="h-4 w-4 accent-accent">
                        <span class="hidden sm:inline">Tampil</span>
                    </label>
                </div>

                <div x-show="open" x-cloak class="border-t border-line p-4 sm:p-5">
                    <?php if (!empty($def['source'])): ?>
                        <p class="mb-4 rounded-md bg-paper px-3 py-2 text-xs text-muted">
                            Daftarnya diambil otomatis dari modul <?= e($def['source']) ?> (dibuat di PHASE 7).
                            Di sini kamu mengatur judul dan jumlah yang tampil.
                        </p>
                    <?php endif; ?>

                    <?php foreach (($se['g'] ?? []) as $message): ?>
                        <p class="mb-3 text-sm font-medium text-red-700"><?= e($message) ?></p>
                    <?php endforeach; ?>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <?php foreach ($def['fields'] as $f => $field): ?>
                            <?php $this->partial('admin/partials/form-field', [
                                'name' => 's[' . $key . '][' . $f . ']',
                                'id' => 'f-' . $key . '-' . $f,
                                'field' => $field,
                                'value' => (string) ($sec['data'][$f] ?? ''),
                                'error' => $se['f'][$f] ?? null,
                            ]); ?>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($rep !== null): ?>
                        <?php
                        $items = [];
                        foreach (($sec['data']['items'] ?? []) as $n => $item) {
                            $items[] = $item + ['_k' => $n];
                        }
                        $repConfig = [
                            'items' => $items,
                            'n' => count($items),
                            'max' => (int) $rep['max'],
                            'blank' => array_fill_keys(array_keys($rep['fields']), ''),
                            'ierr' => $se['i'] ?? [],
                        ];
                        ?>
                        <div class="mt-6"
                             x-data="{
                                 ...<?= e(json_encode($repConfig, JSON_UNESCAPED_UNICODE)) ?>,
                                 add() { if (this.items.length < this.max) this.items.push({ ...this.blank, _k: this.n++ }); },
                                 remove(i) { this.items.splice(i, 1); this.ierr = {}; }
                             }">
                            <div class="mb-1 flex items-center justify-between">
                                <p class="label"><?= e($rep['label']) ?></p>
                                <span class="text-xs text-muted" x-text="items.length + ' / ' + max"></span>
                            </div>
                            <?php if (!empty($rep['hint'])): ?>
                                <p class="mb-3 text-xs text-muted"><?= e($rep['hint']) ?></p>
                            <?php endif; ?>

                            <p x-show="!items.length" class="rounded-md border border-dashed border-line p-4 text-center text-sm text-muted">
                                Belum ada item.
                            </p>

                            <div class="grid gap-3">
                                <template x-for="(it, i) in items" :key="it._k">
                                    <div class="grid gap-3 rounded-lg border border-line bg-paper/40 p-4">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-medium" x-text="'<?= e($rep['item_label']) ?> ' + (i + 1)"></p>
                                            <button type="button" class="btn btn-ghost btn-sm text-red-700" @click="remove(i)">Hapus</button>
                                        </div>

                                        <?php foreach ($rep['fields'] as $f => $field): ?>
                                            <label class="grid gap-1.5">
                                                <span class="label"><?= e($field['label']) ?><?= !empty($field['required']) ? ' *' : '' ?></span>
                                                <?php if (($field['type'] ?? 'text') === 'textarea'): ?>
                                                    <textarea rows="2" maxlength="<?= e($field['max'] ?? 300) ?>" class="textarea"
                                                              :name="'s[<?= e($key) ?>][items][' + i + '][<?= e($f) ?>]'"
                                                              x-model="it.<?= e($f) ?>"></textarea>
                                                <?php else: ?>
                                                    <input type="text" maxlength="<?= e($field['max'] ?? 255) ?>" class="input" autocomplete="off"
                                                           :name="'s[<?= e($key) ?>][items][' + i + '][<?= e($f) ?>]'"
                                                           x-model="it.<?= e($f) ?>">
                                                <?php endif; ?>
                                                <span class="text-xs font-medium text-red-700"
                                                      x-show="ierr[i] && ierr[i]['<?= e($f) ?>']"
                                                      x-text="ierr[i] && ierr[i]['<?= e($f) ?>']"></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </template>
                            </div>

                            <button type="button" class="btn btn-outline btn-sm mt-3"
                                    x-show="items.length < max" @click="add()">
                                + Tambah <?= e(mb_strtolower($rep['item_label'])) ?>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-6 flex justify-end">
        <button type="submit" class="btn btn-primary">Simpan perubahan</button>
    </div>
</form>