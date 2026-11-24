<?php

declare(strict_types=1);

// Hanya boleh dijalankan dari command line
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__);
$files = glob($root . '/database/migrations/*.sql') ?: [];
sort($files, SORT_STRING);

if ($files === []) {
    fwrite(STDERR, "Tidak ada file migration di database/migrations.\n");
    exit(1);
}

$out = "-- Instalasi database lengkap, dibuat otomatis oleh bin/build-install.php\n"
    . '-- Dibuat: ' . date('Y-m-d H:i:s') . "\n"
    . "-- Impor lewat phpMyAdmin ke database yang KOSONG.\n\n"
    . "SET NAMES utf8mb4;\n\n";

foreach ($files as $file) {
    $out .= '-- ----- ' . basename($file) . " -----\n" . trim((string) file_get_contents($file)) . "\n\n";
}

if (file_put_contents($root . '/database/install.sql', $out) === false) {
    fwrite(STDERR, "Gagal menulis database/install.sql.\n");
    exit(1);
}

echo 'database/install.sql dibuat dari ' . count($files) . " file migration.\n";