<?php
$isEdit = $item !== null;
$this->layout('admin/layout', [
    'title' => $isEdit ? 'Edit testimoni' : 'Testimoni baru',
    'active' => 'testimonials',
]);

$val = static fn (string $k): string => (string) ($values[$k] ?? '');
$err = static fn (string $k): ?string => is_string($errors[$k] ?? null) ? $errors[$k] : null;

$media = ctype_digit($val('photo')) && $val('photo') !== ''
    ? \App\Services\Media::find((int) $val('photo'))
    : null;

$action = $isEdit
    ? route('admin.testimonials.update', ['id' => (int) $item['id']])
    : route('admin.testimonials.store');
?>

<div class="mb-6">
    <a href="<?= e(route('admin.testimonials')) ?>" class="text-sm text-muted hover:text-ink">← Semua testimoni</a>
    <h1 class="mt-2 text-2xl font-semibold tracking-tight"><?= $isEdit ? 'Edit testimoni' : 'Testimoni baru' ?></h1>
</div>

<form method="post" action="<?= e($action) ?>" novalidate class="grid gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="card lg:col-span-2">
        <div class="card-content grid gap-5 pt-6 sm:grid-cols-2">
            <?php foreach ($fields as $key => $field): ?>
                <?php $this->partial('admin/partials/form-field', [
                    'name' => $key,
                    'id' => 'f-' . $key,
                    'field' => $field,
                    'value' => $val($key),
                    'error' => $err($key),
                ]); ?>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="grid content-start gap-6">
        <div class="card">
            <div class="card-header"><h2 class="card-title text-base">Publikasi</h2></div>
            <div class="card-content grid gap-4">
                <div class="grid gap-2">
                    <label for="status" class="label">Status</label>
                    <select id="status" name="status" class="input" <?= $canPublish ? '' : 'disabled' ?>>
                        <option value="draft" <?= $val('status') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= $val('status') === 'published' ? 'selected' : '' ?>>Terbit</option>
                    </select>
                    <?php if ($err('status')): ?>
                        <p class="text-xs font-medium text-red-700"><?= e($err('status')) ?></p>
                    <?php elseif (!$canPublish): ?>
                        <p class="text-xs text-muted">Kamu tidak punya izin menerbitkan testimoni.</p>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary w-full"><?= $isEdit ? 'Simpan perubahan' : 'Simpan testimoni' ?></button>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title text-base">Foto</h2>
                <p class="card-description">Opsional. Tanpa foto, website menampilkan inisial nama.</p>
            </div>
            <div class="card-content grid gap-2">
                <?php if (can('media.view')): ?>
                    <?php $this->partial('admin/partials/media-picker', [
                        'name' => 'photo',
                        'value' => $media !== null ? (string) $media['id'] : '',
                        'preview' => $media !== null ? \App\Services\Media::url((string) $media['path']) : '',
                        'id' => 'f-photo',
                    ]); ?>
                <?php else: ?>
                    <input type="hidden" name="photo" value="<?= e($val('photo')) ?>">
                    <p class="text-xs text-muted">Kamu tidak punya izin membuka Media.</p>
                <?php endif; ?>
                <?php if ($err('photo')): ?><p class="text-xs font-medium text-red-700"><?= e($err('photo')) ?></p><?php endif; ?>
            </div>
        </div>
    </div>
</form>