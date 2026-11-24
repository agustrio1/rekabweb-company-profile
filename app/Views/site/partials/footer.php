<?php
$name = \App\Services\Site::name();
$tagline = trim((string) setting('tagline', ''));
$description = trim((string) setting('description', ''));
$footerText = trim((string) setting('footer_text', ''));
$address = trim((string) setting('address', ''));
$hours = trim((string) setting('business_hours', ''));
$email = trim((string) setting('email', ''));
$phone = trim((string) setting('phone', ''));
$tel = \App\Services\Site::telHref();
$waDigits = \App\Services\Site::whatsappDigits();
$socials = \App\Services\Site::socials();
$nav = \App\Services\Site::nav();
$copyright = trim((string) setting('copyright', ''));

$legal = [
    ['label' => 'FAQ', 'path' => '/faq'],
    ['label' => 'Kebijakan privasi', 'path' => '/kebijakan-privasi'],
    ['label' => 'Syarat dan ketentuan', 'path' => '/syarat-ketentuan'],
];
$hasContact = $address !== '' || $phone !== '' || $waDigits !== '' || $email !== '' || $hours !== '';
?>
<footer class="mt-section bg-deep text-paper">
    <div class="wrap grid gap-12 py-14 md:grid-cols-12 md:py-16">
        <div class="md:col-span-5">
            <p class="font-serif text-2xl font-semibold"><?= e($name) ?></p>
            <?php if ($tagline !== ''): ?>
                <p class="mt-1 text-paper/70"><?= e($tagline) ?></p>
            <?php endif; ?>
            <?php $about = $footerText !== '' ? $footerText : $description; ?>
            <?php if ($about !== ''): ?>
                <p class="mt-5 max-w-sm text-[0.95rem] leading-relaxed text-paper/70"><?= e($about) ?></p>
            <?php endif; ?>

            <?php if ($socials !== []): ?>
                <ul class="mt-6 flex flex-wrap gap-x-5 gap-y-2 text-sm">
                    <?php foreach ($socials as $social): ?>
                        <li>
                            <a href="<?= e($social['url']) ?>" target="_blank" rel="noopener noreferrer"
                               class="underline decoration-paper/30 underline-offset-4 hover:decoration-paper"><?= e($social['label']) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <nav class="md:col-span-3" aria-label="Navigasi footer">
            <p class="text-sm font-medium uppercase tracking-widest text-paper/60">Jelajahi</p>
            <ul class="mt-4 grid gap-2.5 text-[0.95rem]">
                <?php foreach ($nav as $item): ?>
                    <li><a href="<?= e(url($item['path'])) ?>" class="text-paper/80 hover:text-white"><?= e($item['label']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <?php if ($hasContact): ?>
            <div class="md:col-span-4">
                <p class="text-sm font-medium uppercase tracking-widest text-paper/60">Kontak</p>
                <ul class="mt-4 grid gap-3 text-[0.95rem] text-paper/80">
                    <?php if ($address !== ''): ?>
                        <li class="whitespace-pre-line"><?= e($address) ?></li>
                    <?php endif; ?>
                    <?php if ($phone !== ''): ?>
                        <li>
                            <?php if ($tel !== ''): ?>
                                <a href="<?= e($tel) ?>" class="hover:text-white"><?= e($phone) ?></a>
                            <?php else: ?>
                                <?= e($phone) ?>
                            <?php endif; ?>
                        </li>
                    <?php endif; ?>
                    <?php if ($waDigits !== ''): ?>
                        <li>
                            WhatsApp:
                            <a href="https://wa.me/<?= e($waDigits) ?>" target="_blank" rel="noopener noreferrer"
                               class="hover:text-white">+<?= e($waDigits) ?></a>
                        </li>
                    <?php endif; ?>
                    <?php if ($email !== ''): ?>
                        <li><a href="mailto:<?= e($email) ?>" class="break-all hover:text-white"><?= e($email) ?></a></li>
                    <?php endif; ?>
                    <?php if ($hours !== ''): ?>
                        <li class="whitespace-pre-line border-t border-paper/15 pt-3"><?= e($hours) ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <div class="border-t border-paper/15">
        <div class="wrap flex flex-col gap-3 py-6 text-sm text-paper/60 md:flex-row md:items-center md:justify-between">
            <p>© <?= e(date('Y')) ?> <?= e($name) ?>.<?= $copyright !== '' ? ' ' . e($copyright) : '' ?></p>
            <ul class="flex flex-wrap gap-x-5 gap-y-1">
                <?php foreach ($legal as $item): ?>
                    <li><a href="<?= e(url($item['path'])) ?>" class="hover:text-white"><?= e($item['label']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</footer>