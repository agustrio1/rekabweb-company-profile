<?php
/** @var string $status kode status inquiry */
$labels = \App\Services\Inquiries::statuses();
$classes = [
    'new' => 'border-amber-300 bg-amber-50 text-amber-900',
    'contacted' => 'badge-success',
    'closed' => 'border-line bg-paper text-muted',
    'spam' => 'badge-danger',
];
$s = (string) ($status ?? '');
?>
<span class="badge <?= e($classes[$s] ?? 'border-line bg-paper text-muted') ?>"><?= e($labels[$s] ?? $s) ?></span>