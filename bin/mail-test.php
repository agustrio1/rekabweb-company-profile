<?php

declare(strict_types=1);

use App\Services\Mailer;

// Hanya boleh dijalankan dari command line
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');

require BASE_PATH . '/app/bootstrap.php';

if (!Mailer::enabled()) {
    fwrite(STDERR, "Email belum aktif. Isi MAIL_HOST dan MAIL_FROM_ADDRESS di .env, dan pastikan PHPMailer terpasang.\n");
    exit(1);
}

$to = isset($argv[1]) && filter_var($argv[1], FILTER_VALIDATE_EMAIL) ? [strtolower($argv[1])] : Mailer::recipients();

if ($to === []) {
    fwrite(STDERR, "Penerima kosong. Pakai: php bin/mail-test.php alamat@email.com\n");
    exit(1);
}

echo 'Mengirim email uji ke ' . implode(', ', $to) . "...\n";

[$ok, $error] = Mailer::send($to, 'Email uji dari website', "Jika email ini sampai, notifikasi inquiry sudah siap.\n");

if ($ok) {
    echo "Berhasil terkirim.\n";
    exit(0);
}

fwrite(STDERR, 'Gagal: ' . $error . "\n");
exit(1);