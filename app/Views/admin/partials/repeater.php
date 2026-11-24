<?php
/** @var string $name  nama field form (mis. benefits) */
/** @var array  $rep   definisi: label, item_label, max, hint, fields */
/** @var array  $items list baris: tiap baris field => string */
/** @var array  $error ['g' => [pesan], 'i' => [indeks => [field => pesan]]] */
$error = $error ?? [];

$list = [];
foreach (array_values($items) as $n => $item) {
    $list[] = $item + ['_k' => $n];
}

$config = [
    'items' => $list,
    'n' => count($list),
    'max' => (int) $rep['max'],
    'blank' => array_fill_keys(array_keys($rep['fields']), ''),
    'ierr' => $error['i'] ?? [],
];
?>
<div class="grid gap-2"
     x-data="{
         ...<?= e(json_encode($config, JSON_UNESCAPED_UNICODE)) ?>,
         add() { if (this.items.length < this.max) this.items.push({ ...this.blank, _k: this.n++ }); },
         remove(i) { this.items.splice(i, 1); this.ierr = {}; }
     }">
    <div class="flex items-center justify-between">
        <p class="label"><?= e($rep['label']) ?></p>
        <span class="text-xs text-muted" x-text="items.length + ' / ' + max"></span>
    </div>

    <?php if (!empty($rep['hint'])): ?>
        <p class="text-xs text-muted"><?= e($rep['hint']) ?></p>
    <?php endif; ?>

    <?php foreach (($error['g'] ?? []) as $message): ?>
        <p class="text-sm font-medium text-red-700"><?= e($message) ?></p>
    <?php endforeach; ?>

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
                                      :name="'<?= e($name) ?>[' + i + '][<?= e($f) ?>]'"
                                      x-model="it.<?= e($f) ?>"></textarea>
                        <?php else: ?>
                            <input type="text" maxlength="<?= e($field['max'] ?? 255) ?>" class="input" autocomplete="off"
                                   :name="'<?= e($name) ?>[' + i + '][<?= e($f) ?>]'"
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

    <button type="button" class="btn btn-outline btn-sm justify-self-start"
            x-show="items.length < max" @click="add()">
        + Tambah <?= e(mb_strtolower($rep['item_label'])) ?>
    </button>
</div>