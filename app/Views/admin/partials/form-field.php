<?php
/** @var string      $name  nama field form */
/** @var string      $id    id elemen */
/** @var array       $field definisi dari config */
/** @var string      $value nilai saat ini */
/** @var string|null $error pesan error */
$error = $error ?? null;
$type = (string) ($field['type'] ?? 'text');
$bad = $error !== null ? ' border-red-400' : '';
$desc = $id . '-desc';
$inputmode = in_array($type, ['link', 'url'], true) ? 'url' : 'text';
?>
<div class="grid content-start gap-2 <?= !empty($field['wide']) ? 'sm:col-span-2' : '' ?>">
    <label for="<?= e($id) ?>" class="label">
        <?= e($field['label']) ?>
        <?php if (!empty($field['required'])): ?><span class="text-red-700">*</span><?php endif; ?>
    </label>

    <?php if ($type === 'textarea'): ?>
        <textarea id="<?= e($id) ?>" name="<?= e($name) ?>" rows="3"
                  maxlength="<?= e($field['max'] ?? 500) ?>"
                  aria-describedby="<?= e($desc) ?>"
                  <?= $error !== null ? 'aria-invalid="true"' : '' ?>
                  class="textarea<?= $bad ?>"><?= e($value) ?></textarea>

    <?php elseif ($type === 'select'): ?>
        <select id="<?= e($id) ?>" name="<?= e($name) ?>" aria-describedby="<?= e($desc) ?>" class="input<?= $bad ?>">
            <?php foreach ((array) $field['options'] as $optValue => $optLabel): ?>
                <option value="<?= e($optValue) ?>" <?= (string) $optValue === $value ? 'selected' : '' ?>>
                    <?= e($optLabel) ?>
                </option>
            <?php endforeach; ?>
        </select>

    <?php elseif ($type === 'image'): ?>
        <?php if (can('media.view')): ?>
            <?php
            $media = ctype_digit($value) && $value !== '' ? \App\Services\Media::find((int) $value) : null;
            $this->partial('admin/partials/media-picker', [
                'name' => $name,
                'value' => $media !== null ? (string) $media['id'] : '',
                'preview' => $media !== null ? \App\Services\Media::url((string) $media['path']) : '',
                'id' => $id,
            ]);
            ?>
        <?php else: ?>
            <input type="hidden" name="<?= e($name) ?>" value="<?= e($value) ?>">
            <p class="text-xs text-muted">Kamu tidak punya izin membuka Media.</p>
        <?php endif; ?>

    <?php else: ?>
        <input id="<?= e($id) ?>" name="<?= e($name) ?>" type="text"
               inputmode="<?= e($inputmode) ?>" autocomplete="off"
               maxlength="<?= e($field['max'] ?? 255) ?>"
               placeholder="<?= e($field['placeholder'] ?? '') ?>"
               value="<?= e($value) ?>"
               aria-describedby="<?= e($desc) ?>"
               <?= $error !== null ? 'aria-invalid="true"' : '' ?>
               class="input<?= $bad ?>">
    <?php endif; ?>

    <div id="<?= e($desc) ?>">
        <?php if ($error !== null): ?>
            <p class="text-xs font-medium text-red-700"><?= e($error) ?></p>
        <?php elseif (!empty($field['hint'])): ?>
            <p class="text-xs text-muted"><?= e($field['hint']) ?></p>
        <?php endif; ?>
    </div>
</div>