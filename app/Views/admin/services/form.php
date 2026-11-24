<?php
$isEdit = $service !== null;
$this->layout('admin/layout', [
    'title' => $isEdit ? 'Edit layanan' : 'Layanan baru',
    'active' => 'services',
]);

$val = static fn (string $k): string => (string) ($values[$k] ?? '');
$err = static fn (string $k): ?string => is_string($errors[$k] ?? null) ? $errors[$k] : null;
$bad = static fn (string $k): string => is_string($errors[$k] ?? null) ? ' border-red-400' : '';

$media = ctype_digit($val('featured_image')) && $val('featured_image') !== ''
    ? \App\Services\Media::find((int) $val('featured_image'))
    : null;

$action = $isEdit
    ? route('admin.services.update', ['id' => (int) $service['id']])
    : route('admin.services.store');
?>

<div class="mb-6">
    <a href="<?= e(route('admin.services')) ?>" class="text-sm text-muted hover:text-ink">← Semua layanan</a>
    <h1 class="mt-2 text-2xl font-semibold tracking-tight"><?= $isEdit ? 'Edit layanan' : 'Layanan baru' ?></h1>
</div>

<form method="post" action="<?= e($action) ?>" novalidate class="grid gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="grid content-start gap-6 lg:col-span-2">
        <div class="card">
            <div class="card-content grid gap-5 pt-6">
                <div class="grid gap-2">
                    <label for="title" class="label">Nama layanan <span class="text-red-700">*</span></label>
                    <input id="title" name="title" type="text" maxlength="150" value="<?= e($val('title')) ?>"
                           class="input<?= $bad('title') ?>" <?= $err('title') ? 'aria-invalid="true"' : '' ?>>
                    <?php if ($err('title')): ?><p class="text-xs font-medium text-red-700"><?= e($err('title')) ?></p><?php endif; ?>
                </div>

                <div class="grid gap-2">
                    <label for="slug" class="label">Slug</label>
                    <input id="slug" name="slug" type="text" maxlength="150" value="<?= e($val('slug')) ?>"
                           placeholder="dibuat-otomatis-dari-nama" autocomplete="off"
                           class="input font-mono<?= $bad('slug') ?>" <?= $err('slug') ? 'aria-invalid="true"' : '' ?>>
                    <?php if ($err('slug')): ?>
                        <p class="text-xs font-medium text-red-700"><?= e($err('slug')) ?></p>
                    <?php else: ?>
                        <p class="text-xs text-muted">Alamat: /layanan/slug. Kosongkan untuk dibuat otomatis.</p>
                    <?php endif; ?>
                </div>

                <div class="grid gap-2">
                    <label for="summary" class="label">Ringkasan</label>
                    <textarea id="summary" name="summary" rows="3" maxlength="300"
                              class="textarea<?= $bad('summary') ?>"><?= e($val('summary')) ?></textarea>
                    <?php if ($err('summary')): ?>
                        <p class="text-xs font-medium text-red-700"><?= e($err('summary')) ?></p>
                    <?php else: ?>
                        <p class="text-xs text-muted">Maksimal 300 karakter. Tampil di kartu layanan dan wajib diisi sebelum terbit.</p>
                    <?php endif; ?>
                </div>

                <div class="grid gap-2">
                    <label for="content" class="label">Deskripsi lengkap</label>
                    <?php $this->partial('admin/partials/rich-editor', [
                        'name' => 'content',
                        'value' => $val('content'),
                        'id' => 'content',
                        'label' => 'Deskripsi layanan',
                    ]); ?>
                    <?php if ($err('content')): ?><p class="text-xs font-medium text-red-700"><?= e($err('content')) ?></p><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-content grid gap-8 pt-6">
                <?php foreach ($reps as $key => $rep): ?>
                    <?php $this->partial('admin/partials/repeater', [
                        'name' => $key,
                        'rep' => $rep,
                        'items' => $values[$key] ?? [],
                        'error' => is_array($errors[$key] ?? null) ? $errors[$key] : [],
                    ]); ?>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title text-base">Tombol ajakan (CTA)</h2>
                <p class="card-description">Tombol di halaman detail layanan ini.</p>
            </div>
            <div class="card-content grid gap-5 sm:grid-cols-2">
                <?php foreach ($ctaFields as $key => $field): ?>
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
    </div>

    <div class="grid content-start gap-6">
        <div class="card">
            <div class="card-header"><h2 class="card-title text-base">Publikasi</h2></div>
            <div class="card-content grid gap-4">
                <div class="grid gap-2">
                    <label for="status" class="label">Status</label>
                    <select id="status" name="status" class="input<?= $bad('status') ?>" <?= $canPublish ? '' : 'disabled' ?>>
                        <option value="draft" <?= $val('status') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= $val('status') === 'published' ? 'selected' : '' ?>>Terbit</option>
                    </select>
                    <?php if (!$canPublish): ?>
                        <p class="text-xs text-muted">Kamu tidak punya izin menerbitkan layanan.</p>
                    <?php endif; ?>
                </div>

                <div class="grid gap-2">
                    <label for="published_at" class="label">Tanggal terbit</label>
                    <input id="published_at" name="published_at" type="datetime-local"
                           value="<?= e($val('published_at')) ?>" class="input<?= $bad('published_at') ?>"
                           <?= $canPublish ? '' : 'disabled' ?>>
                    <?php if ($err('published_at')): ?>
                        <p class="text-xs font-medium text-red-700"><?= e($err('published_at')) ?></p>
                    <?php else: ?>
                        <p class="text-xs text-muted">Kosong = sekarang. Tanggal masa depan berarti terjadwal.</p>
                    <?php endif; ?>
                </div>

                <label class="flex cursor-pointer items-start gap-2 text-sm">
                    <input type="checkbox" name="is_featured" value="1" <?= $val('is_featured') === '1' ? 'checked' : '' ?>
                           class="mt-0.5 h-4 w-4 accent-accent">
                    <span>
                        Layanan unggulan
                        <span class="block text-xs text-muted">Diprioritaskan di section Layanan pada beranda.</span>
                    </span>
                </label>

                <button type="submit" class="btn btn-primary w-full"><?= $isEdit ? 'Simpan perubahan' : 'Simpan layanan' ?></button>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2 class="card-title text-base">Gambar utama</h2></div>
            <div class="card-content grid gap-2">
                <?php if (can('media.view')): ?>
                    <?php $this->partial('admin/partials/media-picker', [
                        'name' => 'featured_image',
                        'value' => $media !== null ? (string) $media['id'] : '',
                        'preview' => $media !== null ? \App\Services\Media::url((string) $media['path']) : '',
                        'id' => 'f-featured-image',
                    ]); ?>
                <?php else: ?>
                    <input type="hidden" name="featured_image" value="<?= e($val('featured_image')) ?>">
                    <p class="text-xs text-muted">Kamu tidak punya izin membuka Media.</p>
                <?php endif; ?>
                <?php if ($err('featured_image')): ?><p class="text-xs font-medium text-red-700"><?= e($err('featured_image')) ?></p><?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2 class="card-title text-base">SEO</h2></div>
            <div class="card-content grid gap-4">
                <div class="grid gap-2">
                    <label for="seo_title" class="label">Judul SEO</label>
                    <input id="seo_title" name="seo_title" type="text" maxlength="70" value="<?= e($val('seo_title')) ?>"
                           class="input<?= $bad('seo_title') ?>">
                    <?php if ($err('seo_title')): ?>
                        <p class="text-xs font-medium text-red-700"><?= e($err('seo_title')) ?></p>
                    <?php else: ?>
                        <p class="text-xs text-muted">Maksimal 70 karakter. Kosong = memakai nama layanan.</p>
                    <?php endif; ?>
                </div>
                <div class="grid gap-2">
                    <label for="seo_description" class="label">Deskripsi SEO</label>
                    <textarea id="seo_description" name="seo_description" rows="3" maxlength="160"
                              class="textarea<?= $bad('seo_description') ?>"><?= e($val('seo_description')) ?></textarea>
                    <?php if ($err('seo_description')): ?>
                        <p class="text-xs font-medium text-red-700"><?= e($err('seo_description')) ?></p>
                    <?php else: ?>
                        <p class="text-xs text-muted">Maksimal 160 karakter. Kosong = memakai ringkasan layanan.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>