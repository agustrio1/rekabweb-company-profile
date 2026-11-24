<?php
use App\Services\FormGuard;
use App\Services\Seo;
use App\Services\Site;

$this->layout('site/layout', ['page' => [
    'title' => 'Kontak',
    'description' => 'Hubungi ' . Site::name() . '. Ceritakan kebutuhan Anda dan kami balas dengan langkah berikutnya.',
    'path' => '/kontak',
    'active' => 'contact',
    'jsonld' => [Seo::breadcrumbs([
        ['label' => 'Beranda', 'path' => '/'],
        ['label' => 'Kontak', 'path' => '/kontak'],
    ])],
]]);

$address = trim((string) setting('address', ''));
$hours = trim((string) setting('business_hours', ''));
$email = trim((string) setting('email', ''));
$phone = trim((string) setting('phone', ''));
$tel = Site::telHref();
$waDigits = Site::whatsappDigits();
$hasInfo = $address !== '' || $hours !== '' || $email !== '' || $phone !== '' || $waDigits !== '';

$general = is_string($e['_form'] ?? null) ? $e['_form'] : null;
$hasFieldErrors = array_diff_key($e, ['_form' => true]) !== [];

$input = static function (string $name, string $label, string $type, string $autocomplete, bool $required, string $placeholder = '') use ($v, $e): void {
    $id = 'f-' . $name;
    $err = is_string($e[$name] ?? null) ? $e[$name] : null;
    ?>
    <div class="grid gap-2">
        <label for="<?= e($id) ?>" class="field-label">
            <?= e($label) ?><?php if ($required): ?> <span class="text-accent" aria-hidden="true">*</span><?php endif; ?>
        </label>
        <input id="<?= e($id) ?>" name="<?= e($name) ?>" type="<?= e($type) ?>" value="<?= e($v[$name] ?? '') ?>"
               autocomplete="<?= e($autocomplete) ?>" placeholder="<?= e($placeholder) ?>"
               <?= $required ? 'required' : '' ?> maxlength="190"
               <?= $err !== null ? 'aria-invalid="true" aria-describedby="' . e($id) . '-err"' : '' ?>
               class="field<?= $err !== null ? ' field-invalid' : '' ?>">
        <?php if ($err !== null): ?>
            <p id="<?= e($id) ?>-err" class="field-error"><?= e($err) ?></p>
        <?php endif; ?>
    </div>
    <?php
};
?>
<section class="wrap pt-10 md:pt-14">
    <?php $this->partial('site/partials/breadcrumb', ['items' => [
        ['label' => 'Beranda', 'path' => '/'],
        ['label' => 'Kontak', 'path' => null],
    ]]); ?>
    <h1 class="mt-6 max-w-3xl text-display">Kontak</h1>
    <p class="mt-5 max-w-prose text-lg text-muted">
        Ceritakan kebutuhan Anda. Kami membalas dengan langkah berikutnya, biasanya dalam satu hari kerja.
    </p>
</section>

<section class="wrap mt-12 grid gap-12 md:mt-16 lg:grid-cols-12 lg:gap-16" aria-label="Formulir dan info kontak">
    <?php if ($hasInfo): ?>
        <div class="lg:col-span-5">
            <dl class="grid gap-6 border-t-2 border-ink pt-6">
                <?php if ($address !== ''): ?>
                    <div>
                        <dt class="eyebrow">Alamat</dt>
                        <dd class="mt-1 whitespace-pre-line"><?= e($address) ?></dd>
                    </div>
                <?php endif; ?>
                <?php if ($phone !== ''): ?>
                    <div>
                        <dt class="eyebrow">Telepon</dt>
                        <dd class="mt-1">
                            <?php if ($tel !== ''): ?>
                                <a href="<?= e($tel) ?>" class="underline underline-offset-4 hover:text-accent"><?= e($phone) ?></a>
                            <?php else: ?>
                                <?= e($phone) ?>
                            <?php endif; ?>
                        </dd>
                    </div>
                <?php endif; ?>
                <?php if ($waDigits !== ''): ?>
                    <div>
                        <dt class="eyebrow">WhatsApp</dt>
                        <dd class="mt-1">
                            <a href="<?= e(Site::whatsappLink('Halo ' . Site::name() . ', saya ingin berkonsultasi.')) ?>"
                               target="_blank" rel="noopener noreferrer"
                               class="underline underline-offset-4 hover:text-accent">+<?= e($waDigits) ?></a>
                        </dd>
                    </div>
                <?php endif; ?>
                <?php if ($email !== ''): ?>
                    <div>
                        <dt class="eyebrow">Email</dt>
                        <dd class="mt-1 break-all">
                            <a href="mailto:<?= e($email) ?>" class="underline underline-offset-4 hover:text-accent"><?= e($email) ?></a>
                        </dd>
                    </div>
                <?php endif; ?>
                <?php if ($hours !== ''): ?>
                    <div>
                        <dt class="eyebrow">Jam operasional</dt>
                        <dd class="mt-1 whitespace-pre-line"><?= e($hours) ?></dd>
                    </div>
                <?php endif; ?>
            </dl>
        </div>
    <?php endif; ?>

    <div class="<?= $hasInfo ? 'lg:col-span-7' : 'lg:col-span-8' ?>">
        <?php if ($general !== null || $hasFieldErrors): ?>
            <div role="alert" class="mb-8 border-l-4 border-accent bg-surface px-5 py-4">
                <p class="font-medium"><?= e($general ?? 'Periksa kembali isian yang ditandai.') ?></p>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= e(url('/kontak')) ?>" class="grid gap-6" novalidate
              x-data="{ busy: false }" @submit="busy = true" @pageshow.window="busy = false">
            <input type="hidden" name="<?= e(FormGuard::FIELD) ?>" value="<?= e($token) ?>">

            <?php /* Honeypot: disembunyikan dari pengunjung, bot biasanya mengisinya */ ?>
            <div class="absolute -left-[9999px] h-0 w-0 overflow-hidden" aria-hidden="true">
                <label for="f-<?= e(FormGuard::HONEYPOT) ?>">Jangan diisi</label>
                <input id="f-<?= e(FormGuard::HONEYPOT) ?>" name="<?= e(FormGuard::HONEYPOT) ?>" type="text"
                       tabindex="-1" autocomplete="off" value="">
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <?php $input('name', 'Nama', 'text', 'name', true); ?>
                <?php $input('email', 'Email', 'email', 'email', true, 'nama@perusahaan.com'); ?>
                <?php $input('whatsapp', 'Nomor WhatsApp', 'tel', 'tel', true, '0812 3456 7890'); ?>
                <?php $input('company', 'Perusahaan (opsional)', 'text', 'organization', false); ?>
            </div>

            <?php $needErr = is_string($e['need_type'] ?? null) ? $e['need_type'] : null; ?>
            <div class="grid gap-2">
                <label for="f-need_type" class="field-label">Jenis kebutuhan</label>
                <select id="f-need_type" name="need_type"
                        <?= $needErr !== null ? 'aria-invalid="true" aria-describedby="f-need_type-err"' : '' ?>
                        class="field<?= $needErr !== null ? ' field-invalid' : '' ?>">
                    <option value="">Pilih jenis kebutuhan</option>
                    <?php foreach ($needTypes as $type): ?>
                        <option value="<?= e($type) ?>" <?= $v['need_type'] === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($needErr !== null): ?><p id="f-need_type-err" class="field-error"><?= e($needErr) ?></p><?php endif; ?>
            </div>

            <?php $msgErr = is_string($e['message'] ?? null) ? $e['message'] : null; ?>
            <div class="grid gap-2">
                <label for="f-message" class="field-label">Pesan <span class="text-accent" aria-hidden="true">*</span></label>
                <textarea id="f-message" name="message" rows="6" required maxlength="2000"
                          placeholder="Ceritakan kebutuhan, lokasi, dan perkiraan jadwal Anda"
                          <?= $msgErr !== null ? 'aria-invalid="true" aria-describedby="f-message-err"' : '' ?>
                          class="field<?= $msgErr !== null ? ' field-invalid' : '' ?>"><?= e($v['message']) ?></textarea>
                <?php if ($msgErr !== null): ?><p id="f-message-err" class="field-error"><?= e($msgErr) ?></p><?php endif; ?>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <button type="submit" class="btn btn-primary disabled:opacity-60" :disabled="busy">
                    <span x-text="busy ? 'Mengirim…' : 'Kirim pesan'">Kirim pesan</span>
                </button>
                <p class="text-sm text-muted">Data Anda hanya dipakai untuk membalas pesan ini.</p>
            </div>
        </form>
    </div>
</section>