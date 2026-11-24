<?php

declare(strict_types=1);

use App\Core\Database;

// Hanya boleh dijalankan dari command line
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');

require BASE_PATH . '/app/bootstrap.php';

function ask(string $label, bool $hidden = false): string
{
    echo $label . ': ';

    $hide = $hidden && function_exists('shell_exec') && stripos(PHP_OS, 'WIN') === false;
    if ($hide) {
        shell_exec('stty -echo 2>/dev/null');
    }

    $line = fgets(STDIN);

    if ($hide) {
        shell_exec('stty echo 2>/dev/null');
        echo "\n";
    }

    return trim((string) $line);
}

function fail(string $message): never
{
    fwrite(STDERR, "Gagal: $message\n");
    exit(1);
}

function sql_quote(string $value): string
{
    return "'" . str_replace(['\\', "'"], ['\\\\', "''"], $value) . "'";
}

$printSql = in_array('--sql', $argv, true);

$name = ask('Nama lengkap');
$email = strtolower(ask('Email'));
$password = ask('Password (min. 10 karakter)', true);
$confirm = ask('Ulangi password', true);

if ($name === '' || strlen($name) > 100) {
    fail('Nama wajib diisi (maksimal 100 karakter).');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
    fail('Format email tidak valid.');
}
if (strlen($password) < 10) {
    fail('Password minimal 10 karakter.');
}
if ($password !== $confirm) {
    fail('Konfirmasi password tidak sama.');
}
if (strtolower($password) === $email) {
    fail('Password tidak boleh sama dengan email.');
}

$hash = password_hash($password, PASSWORD_DEFAULT);

// Mode --sql: cetak perintah INSERT untuk ditempel di phpMyAdmin (tanpa koneksi DB)
if ($printSql) {
    echo "\nTempel SQL berikut di tab SQL phpMyAdmin (database sudah berisi tabel hasil migration):\n\n";
    echo 'INSERT INTO users (role_id, name, email, password_hash, is_active) VALUES ('
        . "(SELECT id FROM roles WHERE name = 'super_admin'), "
        . sql_quote($name) . ', ' . sql_quote($email) . ', ' . sql_quote($hash) . ", 1);\n";
    exit(0);
}

try {
    $pdo = Database::connection();

    $roleId = $pdo->query("SELECT id FROM roles WHERE name = 'super_admin'")->fetchColumn();
    if ($roleId === false) {
        fail("Role belum ada. Jalankan 'php bin/migrate.php run' dulu.");
    }

    $check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
    $check->execute([$email]);
    if ((int) $check->fetchColumn() > 0) {
        fail('Email sudah terdaftar.');
    }

    $insert = $pdo->prepare(
        'INSERT INTO users (role_id, name, email, password_hash, is_active) VALUES (?, ?, ?, ?, 1)'
    );
    $insert->execute([(int) $roleId, $name, $email, $hash]);

    echo "Super Admin dibuat (id {$pdo->lastInsertId()}): $email\n";
} catch (Throwable $e) {
    fail($e->getMessage());
}