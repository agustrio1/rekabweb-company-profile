<?php

declare(strict_types=1);

return [
    'host' => trim((string) env('MAIL_HOST', '')),
    'port' => (int) env('MAIL_PORT', 587),
    'username' => (string) env('MAIL_USERNAME', ''),
    'password' => (string) env('MAIL_PASSWORD', ''),
    // tls (STARTTLS, port 587), ssl (port 465), atau none
    'encryption' => strtolower(trim((string) env('MAIL_ENCRYPTION', 'tls'))),
    'from_address' => trim((string) env('MAIL_FROM_ADDRESS', '')),
    'from_name' => (string) env('MAIL_FROM_NAME', (string) env('APP_NAME', 'Company Profile')),
    // Penerima notifikasi, pisahkan dengan koma. Kosong = email dari Pengaturan website.
    'to' => (string) env('MAIL_TO', ''),
];