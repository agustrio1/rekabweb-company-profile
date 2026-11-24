<?php
$this->layout('admin/layout', ['title' => 'Migrasi dan server', 'active' => 'migrations']);

$count = ['ok' => 0, 'warn' => 0, 'fail' => 0];
$groups = [];
foreach ($checks as $check) {
    $count[$check['status']]++;
    $groups[$check['group']][] = $check;
}

$badge = [
    'ok' => ['OK', 'badge-success'],
    'warn' => ['Perhatian', 'border-amber-300 bg-amber-50 text-amber-900'],
    'fail' => ['Gagal', 'badge-danger'],
];
?>

<div class="mb-6">
    <h1 class="text-2xl font-semibold tracking-tight">Migrasi dan server</h1>
    <p class="mt-1 max-w-prose text-sm text-muted">
        Jalankan migration yang belum diterapkan setelah update versi aplikasi (backup database dulu),
        dan periksa kesiapan server.
    </p>
</div>

<div class="card overflow-hidden">
    <div class="card-header"><h2 class="card-title text-base">Migrasi database</h2></div>
    <div class="overflow-x-auto border-t border-line">
        <table class="w-full text-left text-sm">
            <thead>
            <tr class="border-b border-line text-muted">
                <th class="h-10 px-4 font-medium">Migration</th>
                <th class="h-10 px-4 font-medium">Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr class="border-b border-line transition-colors last:border-0 hover:bg-paper/60">
                    <td class="px-4 py-3 font-mono text-[13px]"><?= e($row['name']) ?></td>
                    <td class="px-4 py-3">
                        <?php if ($row['batch'] === null): ?>
                            <span class="badge badge-danger">Belum dijalankan</span>
                        <?php else: ?>
                            <span class="badge badge-success">Batch <?= e($row['batch']) ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<form method="post" action="<?= e(route('admin.migrations.run')) ?>" class="mt-6"
      @submit="if (!confirm('Jalankan migration yang belum diterapkan?')) $event.preventDefault()">
    <?= csrf_field() ?>
    <button type="submit" class="btn btn-primary" <?= $pending === 0 ? 'disabled' : '' ?>>
        Jalankan migration (<?= e($pending) ?> menunggu)
    </button>
</form>

<div class="mb-4 mt-12">
    <h2 class="text-xl font-semibold tracking-tight">Pemeriksaan server</h2>
    <p class="mt-1 text-sm text-muted">
        <?= e($count['ok']) ?> OK, <?= e($count['warn']) ?> perhatian, <?= e($count['fail']) ?> gagal.
        Semua yang berstatus Gagal harus diperbaiki sebelum website dibuka untuk umum.
    </p>
</div>

<div class="grid gap-6">
    <?php foreach ($groups as $name => $items): ?>
        <div class="card overflow-hidden">
            <div class="card-header"><h3 class="card-title text-base"><?= e($name) ?></h3></div>
            <div class="overflow-x-auto border-t border-line">
                <table class="w-full text-left text-sm">
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <?php [$badgeLabel, $badgeClass] = $badge[$item['status']]; ?>
                        <tr class="border-b border-line last:border-0">
                            <td class="px-4 py-3 font-medium"><?= e($item['label']) ?></td>
                            <td class="px-4 py-3"><span class="badge <?= e($badgeClass) ?>"><?= e($badgeLabel) ?></span></td>
                            <td class="min-w-[14rem] px-4 py-3 text-muted"><?= e($item['detail']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
</div>