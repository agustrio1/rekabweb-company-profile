<?php

declare(strict_types=1);

// Hanya boleh dijalankan dari command line
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

function ask(string $label, bool $secret = false): string
{
    fwrite(STDERR, $label . ': ');

    $hide = $secret && DIRECTORY_SEPARATOR === '/';
    if ($hide) {
        @shell_exec('stty -echo');
    }

    $value = trim((string) fgets(STDIN));

    if ($hide) {
        @shell_exec('stty echo');
        fwrite(STDERR, "\n");
    }

    return $value;
}

function quote(string $value): string
{
    return "'" . str_replace(['\\', "'"], ['\\\\', "''"], $value) . "'";
}

fwrite(STDERR, "Membuat SQL untuk akun Super Admin pertama.\n\n");

$name = ask('Nama');
$email = strtolower(ask('Email'));
$pass = ask('Password (minimal 10 karakter)', true);
$again = ask('Ulangi password', true);

$errors = [];
if ($name === '' || mb_strlen($name) > 100) {
    $errors[] = 'Nama wajib diisi, maksimal 100 karakter.';
}
if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || strlen($email) > 190) {
    $errors[] = 'Format email tidak valid.';
}
if (strlen($pass) < 10) {
    $errors[] = 'Password minimal 10 karakter.';
}
if ($pass !== $again) {
    $errors[] = 'Konfirmasi password tidak sama.';
}

if ($errors !== []) {
    fwrite(STDERR, "\n" . implode("\n", $errors) . "\n");
    exit(1);
}

fwrite(STDERR, "\nSalin SQL di bawah ke tab SQL phpMyAdmin (setelah install.sql diimpor).\n\n");

echo 'INSERT INTO users (role_id, name, email, password_hash, is_active) VALUES (1, '
    . quote($name) . ', ' . quote($email) . ', ' . quote(password_hash($pass, PASSWORD_DEFAULT)) . ", 1);\n";