<?php
$this->layout('admin/layout', ['title' => 'Detail inquiry', 'active' => 'inquiries']);

$val = static fn (string $k): string => (string) ($values[$k] ?? '');
$err = static fn (string $k): ?string => is_string($errors[$k] ?? null) ? $errors[$k] : null;

$canUpdate = can('inquiries.update');
$canDelete = can('inquiries.delete');

$digits = (string) preg_replace('/\D+/', '', (string) $inquiry['whatsapp']);
$sent = date('d M Y H:i', (int) strtotime((string) $inquiry['created_at']));
$updated = date('d M Y H:i', (int) strtotime((string) $inquiry['updated_at']));
?>

<div class="mb-6">
    <a href="<?= e(route('admin.inquiries')) ?>" class="text-sm text-muted hover:text-ink">← Semua inquiry</a>
    <div class="mt-2 flex flex-wrap items-center gap-3">
        <h1 class="text-2xl font-semibold tracking-tight"><?= e($inquiry['name']) ?></h1>
        <?php $this->partial('admin/partials/inquiry-status', ['status' => $inquiry['status']]); ?>
    </div>
    <p class="mt-1 text-sm text-muted">Dikirim <?= e($sent) ?></p>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="grid content-start gap-6 lg:col-span-2">
        <div class="card">
            <div class="card-header"><h2 class="card-title text-base">Pesan</h2></div>
            <div class="card-content">
                <p class="whitespace-pre-wrap break-words text-sm leading-relaxed"><?= e($inquiry['message']) ?></p>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2 class="card-title text-base">Data pengirim</h2></div>
            <div class="card-content">
                <dl class="grid gap-x-4 gap-y-3 text-sm sm:grid-cols-[10rem_1fr]">
                    <dt class="text-muted">Nama</dt>
                    <dd class="break-words"><?= e($inquiry['name']) ?></dd>

                    <dt class="text-muted">Email</dt>
                    <dd class="break-all">
                        <a href="mailto:<?= e($inquiry['email']) ?>" class="underline"><?= e($inquiry['email']) ?></a>
                    </dd>

                    <dt class="text-muted">WhatsApp</dt>
                    <dd>
                        <?php if ($digits !== ''): ?>
                            <a href="https://wa.me/<?= e($digits) ?>" target="_blank" rel="noopener noreferrer"
                               class="underline">+<?= e($digits) ?></a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </dd>

                    <dt class="text-muted">Perusahaan</dt>
                    <dd class="break-words"><?= e($inquiry['company'] !== '' ? $inquiry['company'] : '-') ?></dd>

                    <dt class="text-muted">Jenis kebutuhan</dt>
                    <dd class="break-words"><?= e($inquiry['need_type'] !== '' ? $inquiry['need_type'] : '-') ?></dd>
                </dl>

                <div class="mt-5 flex flex-wrap gap-2">
                    <?php if ($digits !== ''): ?>
                        <a href="https://wa.me/<?= e($digits) ?>" target="_blank" rel="noopener noreferrer"
                           class="btn btn-outline btn-sm">Balas lewat WhatsApp</a>
                    <?php endif; ?>
                    <a href="mailto:<?= e($inquiry['email']) ?>" class="btn btn-outline btn-sm">Balas lewat email</a>
                </div>
            </div>
        </div>
    </div>

    <div class="grid content-start gap-6">
        <form method="post" action="<?= e(route('admin.inquiries.update', ['id' => (int) $inquiry['id']])) ?>"
              novalidate class="card">
            <?= csrf_field() ?>
            <div class="card-header"><h2 class="card-title text-base">Tindak lanjut</h2></div>
            <div class="card-content grid gap-4">
                <div class="grid gap-2">
                    <label for="status" class="label">Status</label>
                    <select id="status" name="status" class="input" <?= $canUpdate ? '' : 'disabled' ?>>
                        <?php foreach ($statuses as $code => $label): ?>
                            <option value="<?= e($code) ?>" <?= $val('status') === $code ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($err('status')): ?><p class="text-xs font-medium text-red-700"><?= e($err('status')) ?></p><?php endif; ?>
                </div>

                <div class="grid gap-2">
                    <label for="admin_note" class="label">Catatan internal</label>
                    <textarea id="admin_note" name="admin_note" rows="6" maxlength="2000"
                              class="textarea<?= $err('admin_note') ? ' border-red-400' : '' ?>"
                              <?= $canUpdate ? '' : 'disabled' ?>><?= e($val('admin_note')) ?></textarea>
                    <?php if ($err('admin_note')): ?>
                        <p class="text-xs font-medium text-red-700"><?= e($err('admin_note')) ?></p>
                    <?php else: ?>
                        <p class="text-xs text-muted">Hanya terlihat oleh admin. Tidak tampil di website.</p>
                    <?php endif; ?>
                </div>

                <?php if ($canUpdate): ?>
                    <button type="submit" class="btn btn-primary w-full">Simpan</button>
                <?php else: ?>
                    <p class="text-xs text-muted">Kamu tidak punya izin mengubah inquiry.</p>
                <?php endif; ?>

                <?php if ($inquiry['handler'] !== null): ?>
                    <p class="text-xs text-muted">Terakhir diubah oleh <?= e($inquiry['handler']) ?> pada <?= e($updated) ?>.</p>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($canDelete): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title text-base">Hapus inquiry</h2>
                    <p class="card-description">Menghapus bersifat permanen. Untuk pesan spam, cukup ubah statusnya.</p>
                </div>
                <div class="card-content">
                    <form method="post" action="<?= e(route('admin.inquiries.delete', ['id' => (int) $inquiry['id']])) ?>"
                          @submit="if (!confirm('Hapus inquiry ini secara permanen?')) $event.preventDefault()">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline w-full text-red-700 hover:bg-red-50">Hapus inquiry</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>