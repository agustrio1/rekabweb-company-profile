<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Database;

// Hanya boleh dijalankan dari command line
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');

require BASE_PATH . '/app/bootstrap.php';

/**
 * Jalankan perintah tanpa shell.
 *
 * @param string[]             $cmd
 * @param array<string,string> $env
 * @return array{0: int, 1: string} [kode keluar, stderr]
 */
function run(array $cmd, ?string $cwd, array $env, ?string $stdout): array
{
    $spec = [
        0 => ['file', '/dev/null', 'r'],
        1 => $stdout !== null ? ['file', $stdout, 'w'] : ['file', '/dev/null', 'w'],
        2 => ['pipe', 'w'],
    ];

    $proc = @proc_open($cmd, $spec, $pipes, $cwd, array_merge(getenv(), $env));
    if (!is_resource($proc)) {
        return [127, 'Perintah ' . $cmd[0] . ' tidak bisa dijalankan.'];
    }

    $err = (string) stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    return [proc_close($proc), trim($err)];
}

$withInquiries = in_array('--inquiries', array_slice($argv, 1), true);
$out = BASE_PATH . '/deploy';

try {
    if (!is_dir($out) && !@mkdir($out, 0755, true) && !is_dir($out)) {
        throw new RuntimeException('Folder deploy tidak bisa dibuat.');
    }

    $pdo = Database::connection();

    if ((int) $pdo->query('SELECT COUNT(*) FROM media')->fetchColumn() < 1
        || (int) $pdo->query('SELECT COUNT(*) FROM pages')->fetchColumn() < 1) {
        fwrite(STDERR, "Database lokal belum berisi konten. Jalankan dulu: php bin/seed.php --fresh\n");
        exit(1);
    }

    $robots = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'robots'")->fetchColumn();
    if ($robots !== 'noindex') {
        fwrite(STDERR, "PERHATIAN: pengindeksan Google belum diblokir di database lokal.\n"
            . "Demo di hosting publik sebaiknya noindex (Pengaturan, tab SEO) sebelum dipaketkan.\n\n");
    }

    $c = (array) Config::get('database');

    $tables = [
        'media', 'settings', 'homepage_sections', 'pages', 'services',
        'project_categories', 'projects', 'project_images',
        'article_categories', 'tags', 'articles', 'article_tag',
        'testimonials', 'faqs',
    ];
    if ($withInquiries) {
        $tables[] = 'inquiries';
    }

    // ---- demo.sql ----
    $dump = $out . '/demo-body.tmp';
    [$code, $err] = run(
        array_merge([
            'mysqldump',
            '--host=' . (string) ($c['host'] ?? '127.0.0.1'),
            '--port=' . (int) ($c['port'] ?? 3306),
            '--user=' . (string) ($c['user'] ?? ''),
            '--no-create-info', '--complete-insert', '--skip-add-locks', '--skip-comments',
            '--skip-triggers', '--default-character-set=utf8mb4',
            (string) ($c['name'] ?? ''),
        ], $tables),
        null,
        ['MYSQL_PWD' => (string) ($c['pass'] ?? '')],
        $dump
    );

    if ($code !== 0) {
        @unlink($dump);
        fwrite(STDERR, "mysqldump gagal: " . ($err !== '' ? $err : 'kode ' . $code) . "\n"
            . "Pastikan paket mariadb terpasang (pkg install mariadb) dan kredensial .env benar.\n");
        exit(1);
    }

    $head = "-- Data demo, dibuat otomatis oleh bin/package-demo.php\n"
        . '-- Dibuat: ' . date('Y-m-d H:i:s') . "\n"
        . "-- Impor SETELAH install.sql dan admin.sql, ke database yang belum berisi konten.\n\n"
        . "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n";

    // Arahkan kolom pembuat/penulis ke Super Admin yang ada di hosting
    $tail = "\nSET FOREIGN_KEY_CHECKS = 1;\n"
        . "SET @admin = (SELECT MIN(id) FROM users WHERE role_id = 1);\n"
        . "UPDATE media SET uploaded_by = @admin WHERE uploaded_by IS NOT NULL;\n";
    foreach (['pages', 'services', 'projects', 'testimonials', 'faqs'] as $t) {
        $tail .= "UPDATE $t SET created_by = @admin, updated_by = @admin, updated_at = updated_at;\n";
    }
    $tail .= "UPDATE articles SET author_id = @admin, created_by = @admin, updated_by = @admin, updated_at = updated_at;\n";
    if ($withInquiries) {
        $tail .= "UPDATE inquiries SET handled_by = @admin WHERE handled_by IS NOT NULL;\n";
    }

    $body = (string) file_get_contents($dump);
    @unlink($dump);

    if (file_put_contents($out . '/demo.sql', $head . $body . $tail) === false) {
        throw new RuntimeException('Gagal menulis deploy/demo.sql.');
    }
    echo 'deploy/demo.sql dibuat (' . count($tables) . " tabel)\n";

    // ---- uploads.zip ----
    $uploads = PUBLIC_PATH . '/uploads';
    @unlink($out . '/uploads.zip');

    [$code, $err] = run(
        ['zip', '-rq', $out . '/uploads.zip', '.', '-x', '.htaccess', '-x', '*/.htaccess', '-x', '.gitkeep', '-x', '*/.gitkeep'],
        $uploads,
        []
    );

    if ($code !== 0 && $code !== 12) { // 12 = tidak ada file untuk dimasukkan
        fwrite(STDERR, 'zip gagal: ' . ($err !== '' ? $err : 'kode ' . $code) . "\nJalankan: pkg install zip\n");
        exit(1);
    }

    echo is_file($out . '/uploads.zip')
        ? "deploy/uploads.zip dibuat\n"
        : "Tidak ada gambar di public/uploads, uploads.zip tidak dibuat.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Gagal: ' . $e->getMessage() . "\n");
    exit(1);
}