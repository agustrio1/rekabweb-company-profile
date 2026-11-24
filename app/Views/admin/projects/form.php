<?php
$isEdit = $project !== null;
$this->layout('admin/layout', [
    'title' => $isEdit ? 'Edit proyek' : 'Proyek baru',
    'active' => 'projects',
]);

$val = static fn (string $k): string => (string) ($values[$k] ?? '');
$err = static fn (string $k): ?string => is_string($errors[$k] ?? null) ? $errors[$k] : null;
$bad = static fn (string $k): string => is_string($errors[$k] ?? null) ? ' border-red-400' : '';

$media = ctype_digit($val('featured_image')) && $val('featured_image') !== ''
    ? \App\Services\Media::find((int) $val('featured_image'))
    : null;

$action = $isEdit
    ? route('admin.projects.update', ['id' => (int) $project['id']])
    : route('admin.projects.store');
?>

<div class="mb-6">
    <a href="<?= e(route('admin.projects')) ?>" class="text-sm text-muted hover:text-ink">← Semua proyek</a>
    <h1 class="mt-2 text-2xl font-semibold tracking-tight"><?= $isEdit ? 'Edit proyek' : 'Proyek baru' ?></h1>
</div>

<form method="post" action="<?= e($action) ?>" novalidate class="grid gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="grid content-start gap-6 lg:col-span-2">
        <div class="card">
            <div class="card-content grid gap-5 pt-6">
                <div class="grid gap-2">
                    <label for="title" class="label">Nama proyek <span class="text-red-700">*</span></label>
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
                        <p class="text-xs text-muted">Alamat: /proyek/slug. Kosongkan untuk dibuat otomatis.</p>
                    <?php endif; ?>
                </div>

                <div class="grid gap-2">
                    <label for="summary" class="label">Ringkasan</label>
                    <textarea id="summary" name="summary" rows="3" maxlength="300"
                              class="textarea<?= $bad('summary') ?>"><?= e($val('summary')) ?></textarea>
                    <?php if ($err('summary')): ?>
                        <p class="text-xs font-medium text-red-700"><?= e($err('summary')) ?></p>
                    <?php else: ?>
                        <p class="text-xs text-muted">Maksimal 300 karakter. Tampil di kartu proyek dan wajib diisi sebelum terbit.</p>
                    <?php endif; ?>
                </div>

                <div class="grid gap-2">
                    <label for="content" class="label">Deskripsi proyek</label>
                    <?php $this->partial('admin/partials/rich-editor', [
                        'name' => 'content',
                        'value' => $val('content'),
                        'id' => 'content',
                        'label' => 'Deskripsi proyek',
                    ]); ?>
                    <?php if ($err('content')): ?><p class="text-xs font-medium text-red-700"><?= e($err('content')) ?></p><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2 class="card-title text-base">Detail proyek</h2></div>
            <div class="card-content grid gap-5 sm:grid-cols-2">
                <div class="grid content-start gap-2">
                    <label for="category_id" class="label">Kategori</label>
                    <select id="category_id" name="category_id" class="input<?= $bad('category_id') ?>">
                        <option value="">Tanpa kategori</option>
                        <?php foreach ($categories as $catId => $catName): ?>
                            <option value="<?= e($catId) ?>" <?= $val('category_id') === (string) $catId ? 'selected' : '' ?>>
                                <?= e($catName) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($err('category_id')): ?>
                        <p class="text-xs font-medium text-red-700"><?= e($err('category_id')) ?></p>
                    <?php else: ?>
                        <p class="text-xs text-muted">
                            Kelola di <a href="<?= e(route('admin.project-categories')) ?>" class="underline">Kategori proyek</a>.
                        </p>
                    <?php endif; ?>
                </div>

                <div class="grid content-start gap-2">
                    <label for="completed_year" class="label">Tahun selesai</label>
                    <input id="completed_year" name="completed_year" type="text" inputmode="numeric" maxlength="4"
                           value="<?= e($val('completed_year')) ?>" placeholder="<?= e(date('Y')) ?>" autocomplete="off"
                           class="input<?= $bad('completed_year') ?>">
                    <?php if ($err('completed_year')): ?>
                        <p class="text-xs font-medium text-red-700"><?= e($err('completed_year')) ?></p>
                    <?php endif; ?>
                </div>

                <?php foreach (['client_name', 'location'] as $key): ?>
                    <?php $this->partial('admin/partials/form-field', [
                        'name' => $key,
                        'id' => 'f-' . $key,
                        'field' => $textFields[$key],
                        'value' => $val($key),
                        'error' => $err($key),
                    ]); ?>
                <?php endforeach; ?>

                <label class="flex cursor-pointer items-start gap-2 text-sm sm:col-span-2">
                    <input type="checkbox" name="show_client" value="1" <?= $val('show_client') === '1' ? 'checked' : '' ?>
                           class="mt-0.5 h-4 w-4 accent-accent">
                    <span>
                        Tampilkan nama klien di website
                        <span class="block text-xs text-muted">Centang hanya kalau klien mengizinkan namanya dipublikasikan.</span>
                    </span>
                </label>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2 class="card-title text-base">Cerita proyek</h2></div>
            <div class="card-content grid gap-5">
                <?php foreach (['challenge', 'solution', 'results'] as $key): ?>
                    <?php $this->partial('admin/partials/form-field', [
                        'name' => $key,
                        'id' => 'f-' . $key,
                        'field' => $textFields[$key],
                        'value' => $val($key),
                        'error' => $err($key),
                    ]); ?>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title text-base">Galeri</h2>
                <p class="card-description">Foto hasil pekerjaan. Maksimal <?= e($maxGallery) ?> gambar, urutan bisa digeser.</p>
            </div>
            <div class="card-content grid gap-2">
                <?php if (can('media.view')): ?>
                    <?php $this->partial('admin/partials/gallery-picker', [
                        'name' => 'gallery',
                        'items' => $values['gallery_items'] ?? [],
                        'max' => $maxGallery,
                    ]); ?>
                <?php else: ?>
                    <?php foreach (($values['gallery_items'] ?? []) as $item): ?>
                        <input type="hidden" name="gallery[]" value="<?= e($item['id']) ?>">
                    <?php endforeach; ?>
                    <p class="text-xs text-muted">Kamu tidak punya izin membuka Media. Galeri tidak berubah.</p>
                <?php endif; ?>
                <?php if ($err('gallery')): ?><p class="text-xs font-medium text-red-700"><?= e($err('gallery')) ?></p><?php endif; ?>
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
                        <p class="text-xs text-muted">Kamu tidak punya izin menerbitkan proyek.</p>
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
                        Proyek unggulan
                        <span class="block text-xs text-muted">Diprioritaskan di section Portofolio pada beranda.</span>
                    </span>
                </label>

                <button type="submit" class="btn btn-primary w-full"><?= $isEdit ? 'Simpan perubahan' : 'Simpan proyek' ?></button>
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
                        <p class="text-xs text-muted">Maksimal 70 karakter. Kosong = memakai nama proyek.</p>
                    <?php endif; ?>
                </div>
                <div class="grid gap-2">
                    <label for="seo_description" class="label">Deskripsi SEO</label>
                    <textarea id="seo_description" name="seo_description" rows="3" maxlength="160"
                              class="textarea<?= $bad('seo_description') ?>"><?= e($val('seo_description')) ?></textarea>
                    <?php if ($err('seo_description')): ?>
                        <p class="text-xs font-medium text-red-700"><?= e($err('seo_description')) ?></p>
                    <?php else: ?>
                        <p class="text-xs text-muted">Maksimal 160 karakter. Kosong = memakai ringkasan proyek.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>