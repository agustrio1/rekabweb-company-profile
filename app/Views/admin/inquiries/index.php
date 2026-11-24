<?php
$this->layout('admin/layout', ['title' => 'Inquiry', 'active' => 'inquiries']);

$link = static function (int $p, string $st) use ($q): string {
    $qs = http_build_query(array_filter(
        ['q' => $q, 'status' => $st, 'page' => $p > 1 ? $p : null],
        static fn ($v): bool => $v !== null && $v !== ''
    ));

    return route('admin.inquiries') . ($qs !== '' ? '?' . $qs : '');
};

$canDelete = can('inquiries.delete');
$allCount = array_sum($counts);
$filtered = $q !== '' || $status !== '';
?>

<div class="mb-6">
    <h1 class="text-2xl font-semibold tracking-tight">Inquiry</h1>
    <p class="mt-1 max-w-prose text-sm text-muted">
        Pesan dari calon klien lewat formulir kontak. Inquiry terbaru ada di paling atas.
    </p>
</div>

<div class="mb-4 overflow-x-auto">
    <div class="inline-flex items-center gap-1 rounded-lg bg-paper p-1 text-muted" role="tablist">
        <a href="<?= e($link(1, '')) ?>"
           class="inline-flex h-8 items-center gap-1.5 whitespace-nowrap rounded-md px-3 text-sm font-medium transition-colors <?= $status === '' ? 'bg-surface text-ink shadow-sm' : 'hover:text-ink' ?>">
            Semua <span class="text-xs"><?= e($allCount) ?></span>
        </a>
        <?php foreach ($statuses as $code => $label): ?>
            <a href="<?= e($link(1, $code)) ?>"
               class="inline-flex h-8 items-center gap-1.5 whitespace-nowrap rounded-md px-3 text-sm font-medium transition-colors <?= $status === $code ? 'bg-surface text-ink shadow-sm' : 'hover:text-ink' ?>">
                <?= e($label) ?> <span class="text-xs"><?= e($counts[$code] ?? 0) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<form method="get" action="<?= e(route('admin.inquiries')) ?>" class="mb-4 flex flex-col gap-2 sm:flex-row">
    <?php if ($status !== ''): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
    <input type="search" name="q" value="<?= e($q) ?>" maxlength="100"
           placeholder="Cari nama, email, perusahaan, atau WhatsApp"
           aria-label="Cari inquiry" class="input sm:max-w-sm">
    <button type="submit" class="btn btn-outline">Cari</button>
</form>

<?php if ($rows === []): ?>
    <div class="card">
        <div class="card-content flex flex-col items-center gap-1 py-12 text-center">
            <p class="font-medium"><?= $filtered ? 'Tidak ada inquiry yang cocok' : 'Belum ada inquiry' ?></p>
            <p class="text-sm text-muted">
                <?= $filtered ? 'Ubah kata kunci atau filter.' : 'Pesan dari formulir kontak website akan muncul di sini.' ?>
            </p>
        </div>
    </div>
<?php else: ?>
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                <tr class="border-b border-line text-muted">
                    <th class="h-10 px-4 font-medium">Pengirim</th>
                    <th class="hidden h-10 px-4 font-medium md:table-cell">Kebutuhan</th>
                    <th class="hidden h-10 px-4 font-medium sm:table-cell">Dikirim</th>
                    <th class="h-10 px-4 font-medium">Status</th>
                    <th class="h-10 px-4"><span class="sr-only">Aksi</span></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr class="border-b border-line transition-colors last:border-0 hover:bg-paper/60">
                        <td class="px-4 py-3">
                            <a href="<?= e($row['show_url']) ?>"
                               class="<?= $row['status'] === 'new' ? 'font-semibold' : 'font-medium' ?> hover:underline"><?= e($row['name']) ?></a>
                            <p class="text-xs text-muted">
                                <?= e($row['email']) ?><?= $row['company'] !== '' ? ' · ' . e($row['company']) : '' ?>
                            </p>
                        </td>
                        <td class="hidden px-4 py-3 text-muted md:table-cell"><?= e($row['need_type'] !== '' ? $row['need_type'] : '-') ?></td>
                        <td class="hidden whitespace-nowrap px-4 py-3 text-muted sm:table-cell">
                            <?= e(date('d M Y H:i', (int) strtotime((string) $row['created_at']))) ?>
                        </td>
                        <td class="px-4 py-3"><?php $this->partial('admin/partials/inquiry-status', ['status' => $row['status']]); ?></td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="<?= e($row['show_url']) ?>" class="btn btn-outline btn-sm">Lihat</a>
                                <?php if ($canDelete): ?>
                                    <form method="post" action="<?= e($row['delete_url']) ?>"
                                          @submit="if (!confirm('Hapus inquiry ini secara permanen?')) $event.preventDefault()">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline btn-sm text-red-700 hover:bg-red-50">Hapus</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 flex items-center justify-between text-sm">
        <p class="text-muted"><?= e($total) ?> inquiry · halaman <?= e($page) ?> dari <?= e($pages) ?></p>
        <div class="flex gap-2">
            <?php if ($page > 1): ?>
                <a href="<?= e($link($page - 1, $status)) ?>" class="btn btn-outline btn-sm">Sebelumnya</a>
            <?php endif; ?>
            <?php if ($page < $pages): ?>
                <a href="<?= e($link($page + 1, $status)) ?>" class="btn btn-outline btn-sm">Berikutnya</a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>