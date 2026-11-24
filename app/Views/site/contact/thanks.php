<?php
use App\Services\Site;

$this->layout('site/layout', ['page' => [
    'title' => 'Pesan terkirim',
    'path' => '/kontak/terkirim',
    'active' => 'contact',
    'noindex' => true,
]]);

$wa = Site::whatsappLink('Halo ' . Site::name() . ', saya baru mengirim pesan lewat website.');
?>
<section class="wrap py-section">
    <p class="eyebrow">Terima kasih</p>
    <h1 class="mt-4 max-w-3xl text-display">Pesan Anda sudah kami terima.</h1>
    <p class="mt-6 max-w-prose text-lg text-muted">
        Kami akan menghubungi Anda lewat email atau WhatsApp, biasanya dalam satu hari kerja.
    </p>

    <div class="mt-10 flex flex-wrap items-center gap-4">
        <a href="<?= e(url('/')) ?>" class="btn btn-primary">Kembali ke beranda</a>
        <?php if ($wa !== ''): ?>
            <a href="<?= e($wa) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline">Chat WhatsApp</a>
        <?php endif; ?>
    </div>
</section>