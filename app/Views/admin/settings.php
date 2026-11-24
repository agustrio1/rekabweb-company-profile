<?php $this->layout('admin/layout', ['title' => 'Pengaturan website', 'active' => 'settings']); ?>

<div class="mb-6">
    <h1 class="text-2xl font-semibold tracking-tight">Pengaturan website</h1>
    <p class="mt-1 max-w-prose text-sm text-muted">
        Identitas, kontak, social media, dan SEO default. Perubahan langsung dipakai di website publik.
    </p>
</div>

<form method="post" action="<?= e(route('admin.settings.update')) ?>" novalidate
      x-data="{ tab: '<?= e($initial) ?>' }">
    <?= csrf_field() ?>

    <div class="mb-4 overflow-x-auto">
        <div role="tablist" class="inline-flex items-center gap-1 rounded-lg bg-paper p-1 text-muted">
            <?php foreach ($groups as $groupKey => $group): ?>
                <?php $hasError = array_intersect_key($errors, $group['fields']) !== []; ?>
                <button type="button" role="tab"
                        class="inline-flex h-8 items-center gap-1.5 whitespace-nowrap rounded-md px-3 text-sm font-medium transition-colors"
                        :class="tab === '<?= e($groupKey) ?>' ? 'bg-surface text-ink shadow-sm' : 'hover:text-ink'"
                        :aria-selected="tab === '<?= e($groupKey) ?>'"
                        @click="tab = '<?= e($groupKey) ?>'">
                    <?= e($group['label']) ?>
                    <?php if ($hasError): ?>
                        <span class="h-1.5 w-1.5 rounded-full bg-red-600" aria-label="Ada kesalahan"></span>
                    <?php endif; ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <?php foreach ($groups as $groupKey => $group): ?>
        <div class="card" role="tabpanel" x-show="tab === '<?= e($groupKey) ?>'" <?= $groupKey === $initial ? '' : 'x-cloak' ?>>
            <div class="card-header">
                <h2 class="card-title text-lg"><?= e($group['label']) ?></h2>
            </div>

            <div class="card-content grid gap-5 sm:grid-cols-2">
                <?php foreach ($group['fields'] as $key => $field): ?>
                    <?php
                    $type = (string) ($field['type'] ?? 'text');
                    $value = (string) ($values[$key] ?? '');
                    $error = $errors[$key] ?? null;
                    $id = 'f-' . $key;
                    $describedBy = $id . '-desc';
                    $errorClass = $error !== null ? ' border-red-400' : '';
                    $inputmode = match ($type) {
                        'email' => 'email',
                        'phone', 'whatsapp' => 'tel',
                        'url', 'link' => 'url',
                        default => 'text',
                    };
                    ?>
                    <div class="grid content-start gap-2 <?= !empty($field['wide']) ? 'sm:col-span-2' : '' ?>">
                        <label for="<?= e($id) ?>" class="label">
                            <?= e($field['label']) ?>
                            <?php if (!empty($field['required'])): ?><span class="text-red-700">*</span><?php endif; ?>
                        </label>

                        <?php if ($type === 'textarea'): ?>
                            <textarea id="<?= e($id) ?>" name="<?= e($key) ?>" rows="3"
                                      maxlength="<?= e($field['max'] ?? 500) ?>"
                                      placeholder="<?= e($field['placeholder'] ?? '') ?>"
                                      aria-describedby="<?= e($describedBy) ?>"
                                      <?= $error !== null ? 'aria-invalid="true"' : '' ?>
                                      class="textarea<?= $errorClass ?>"><?= e($value) ?></textarea>

                        <?php elseif ($type === 'select'): ?>
                            <select id="<?= e($id) ?>" name="<?= e($key) ?>"
                                    aria-describedby="<?= e($describedBy) ?>"
                                    class="input<?= $errorClass ?>">
                                <?php foreach ((array) $field['options'] as $optValue => $optLabel): ?>
                                    <option value="<?= e($optValue) ?>" <?= (string) $optValue === $value ? 'selected' : '' ?>>
                                        <?= e($optLabel) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                                              <?php elseif ($type === 'image'): ?>
                            <?php
                            $media = ctype_digit($value) && $value !== '' ? \App\Services\Media::find((int) $value) : null;
                            $this->partial('admin/partials/media-picker', [
                                'name' => $key,
                                'value' => $media !== null ? (string) $media['id'] : '',
                                'preview' => $media !== null ? \App\Services\Media::url((string) $media['path']) : '',
                                'id' => $id,
                            ]);
                            ?>

                        <?php else: ?>
                            <input id="<?= e($id) ?>" name="<?= e($key) ?>" type="text"
                                   inputmode="<?= e($inputmode) ?>" autocomplete="off"
                                   maxlength="<?= e($field['max'] ?? 255) ?>"
                                   placeholder="<?= e($field['placeholder'] ?? '') ?>"
                                   value="<?= e($value) ?>"
                                   aria-describedby="<?= e($describedBy) ?>"
                                   <?= $error !== null ? 'aria-invalid="true"' : '' ?>
                                   class="input<?= $errorClass ?>">
                        <?php endif; ?>

                        <div id="<?= e($describedBy) ?>">
                            <?php if ($error !== null): ?>
                                <p class="text-xs font-medium text-red-700"><?= e($error) ?></p>
                            <?php elseif (!empty($field['hint'])): ?>
                                <p class="text-xs text-muted"><?= e($field['hint']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="mt-6 flex justify-end">
        <button type="submit" class="btn btn-primary">Simpan perubahan</button>
    </div>
</form>