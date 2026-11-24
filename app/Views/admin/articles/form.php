<?php
$isEdit = $article !== null;
$this->layout('admin/layout', [
    'title' => $isEdit ? 'Edit artikel' : 'Artikel baru',
    'active' => 'articles',
]);

$val = static fn (string $k): string => (string) ($values[$k] ?? '');
$err = static fn (string $k): ?string => is_string($errors[$k] ?? null) ? $errors[$k] : null;
$bad = static fn (string $k): string => is_string($errors[$k] ?? null) ? ' border-red-400' : '';

$media = ctype_digit($val('featured_image')) && $val('featured_image') !== ''
    ? \App\Services\Media::find((int) $val('featured_image'))
    : null;

$action = $isEdit
    ? route('admin.articles.update', ['id' => (int) $article['id']])
    : route('admin.articles.store');

$tagConfig = json_encode($val('tags'), JSON_UNESCAPED_UNICODE);
?>

<div class="mb-6">
    <a href="<?= e(route('admin.articles')) ?>" class="text-sm text-muted hover:text-ink">← Semua artikel</a>
    <h1 class="mt-2 text-2xl font-semibold tracking-tight"><?= $isEdit ? 'Edit artikel' : 'Artikel baru' ?></h1>
</div>

<form method="post" action="<?= e($action) ?>" novalidate class="grid gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="grid content-start gap-6 lg:col-span-2">
        <div class="card">
            <div class="card-content grid gap-5 pt-6">
                <div class="grid gap-2">
                    <label for="title" class="label">Judul <span class="text-red-700">*</span></label>
                    <input id="title" name="title" type="text" maxlength="200" value="<?= e($val('title')) ?>"
                           class="input<?= $bad('title') ?>" <?= $err('title') ? 'aria-invalid="true"' : '' ?>>
                    <?php if ($err('title')): ?><p class="text-xs font-medium text-red-700"><?= e($err('title')) ?></p><?php endif; ?>
                </div>

                <div class="grid gap-2">
                    <label for="slug" class="label">Slug</label>
                    <input id="slug" name="slug" type="text" maxlength="150" value="<?= e($val('slug')) ?>"
                           placeholder="dibuat-otomatis-dari-judul" autocomplete="off"
                           class="input font-mono<?= $bad('slug') ?>" <?= $err('slug') ? 'aria-invalid="true"' : '' ?>>
                    <?php if ($err('slug')): ?>
                        <p class="text-xs font-medium text-red-700"><?= e($err('slug')) ?></p>
                    <?php else: ?>
                        <p class="text-xs text-muted">Alamat: /blog/slug. Kosongkan untuk dibuat otomatis dari judul.</p>
                    <?php endif; ?>
                </div>

                <div class="grid gap-2">
                    <label for="excerpt" class="label">Ringkasan</label>
                    <textarea id="excerpt" name="excerpt" rows="3" maxlength="300"
                              class="textarea<?= $bad('excerpt') ?>"><?= e($val('excerpt')) ?></textarea>
                    <?php if ($err('excerpt')): ?>
                        <p class="text-xs font-medium text-red-700"><?= e($err('excerpt')) ?></p>
                    <?php else: ?>
                        <p class="text-xs text-muted">Maksimal 300 karakter. Tampil di daftar artikel. Kosong = dipotong otomatis dari isi artikel.</p>
                    <?php endif; ?>
                </div>

                <div class="grid gap-2">
                    <label for="content" class="label">Isi artikel</label>
                    <?php $this->partial('admin/partials/rich-editor', [
                        'name' => 'content',
                        'value' => $val('content'),
                        'id' => 'content',
                        'label' => 'Isi artikel',
                    ]); ?>
                    <?php if ($err('content')): ?><p class="text-xs font-medium text-red-700"><?= e($err('content')) ?></p><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2 class="card-title text-base">Tag</h2></div>
            <div class="card-content grid gap-2"
                 x-data="{
                     value: <?= e($tagConfig) ?>,
                     add(t) {
                         const list = this.value.split(',').map((s) => s.trim()).filter(Boolean);
                         if (!list.some((s) => s.toLowerCase() === t.toLowerCase())) list.push(t);
                         this.value = list.join(', ');
                     }
                 }">
                <label for="tags" class="label">Tag artikel</label>
                <input id="tags" name="tags" type="text" x-model="value" autocomplete="off"
                       placeholder="contoh: renovasi, tips, perawatan"
                       class="input<?= $bad('tags') ?>" <?= $err('tags') ? 'aria-invalid="true"' : '' ?>>
                <?php if ($err('tags')): ?>
                    <p class="text-xs font-medium text-red-700"><?= e($err('tags')) ?></p>
                <?php else: ?>
                    <p class="text-xs text-muted">Pisahkan dengan koma. Maksimal <?= e($maxTags) ?> tag. Tag baru dibuat otomatis.</p>
                <?php endif; ?>

                <?php if ($popularTags !== []): ?>
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <?php foreach ($popularTags as $tag): ?>
                            <button type="button" class="badge border-line bg-paper text-ink hover:bg-line/40"
                                    @click="add(<?= e(json_encode($tag, JSON_UNESCAPED_UNICODE)) ?>)">+ <?= e($tag) ?></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
                        <p class="text-xs text-muted">Kamu tidak punya izin menerbitkan artikel.</p>
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

                <?php if ($canAuthor): ?>
                    <div class="grid gap-2">
                        <label for="author_id" class="label">Penulis</label>
                        <select id="author_id" name="author_id" class="input<?= $bad('author_id') ?>">
                            <?php foreach ($authors as $authorId => $authorName): ?>
                                <option value="<?= e($authorId) ?>" <?= $val('author_id') === (string) $authorId ? 'selected' : '' ?>>
                                    <?= e($authorName) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($err('author_id')): ?>
                            <p class="text-xs font-medium text-red-700"><?= e($err('author_id')) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary w-full"><?= $isEdit ? 'Simpan perubahan' : 'Simpan artikel' ?></button>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2 class="card-title text-base">Kategori</h2></div>
            <div class="card-content grid gap-2">
                <label for="category_id" class="sr-only">Kategori</label>
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
                        Kelola di <a href="<?= e(route('admin.taxonomy', ['type' => 'categories'])) ?>" class="underline">Kategori artikel</a>.
                    </p>
                <?php endif; ?>
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
                        <p class="text-xs text-muted">Maksimal 70 karakter. Kosong = memakai judul artikel.</p>
                    <?php endif; ?>
                </div>
                <div class="grid gap-2">
                    <label for="seo_description" class="label">Deskripsi SEO</label>
                    <textarea id="seo_description" name="seo_description" rows="3" maxlength="160"
                              class="textarea<?= $bad('seo_description') ?>"><?= e($val('seo_description')) ?></textarea>
                    <?php if ($err('seo_description')): ?>
                        <p class="text-xs font-medium text-red-700"><?= e($err('seo_description')) ?></p>
                    <?php else: ?>
                        <p class="text-xs text-muted">Maksimal 160 karakter. Kosong = memakai ringkasan artikel.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>