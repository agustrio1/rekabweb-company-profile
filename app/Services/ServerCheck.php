<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ErrorHandler;
use Throwable;

/** Pemeriksaan lingkungan server untuk halaman admin (pengganti terminal di shared hosting). */
final class ServerCheck
{
    /**
     * @return array<int, array{group: string, status: string, label: string, detail: string}> status: ok, warn, fail
     */
    public static function run(bool $secure): array
    {
        $rows = [];
        $group = '';
        $add = static function (string $status, string $label, string $detail = '') use (&$rows, &$group): void {
            $rows[] = ['group' => $group, 'status' => $status, 'label' => $label, 'detail' => $detail];
        };

        // ---- PHP ----
        $group = 'PHP';
        $add(PHP_VERSION_ID >= 80200 ? 'ok' : 'fail', 'Versi PHP', PHP_VERSION . ' (minimal 8.2)');

        $extensions = [
            ['pdo_mysql', 'fail', 'koneksi database'],
            ['mbstring', 'fail', 'teks UTF-8'],
            ['fileinfo', 'fail', 'validasi upload'],
            ['ctype', 'fail', 'validasi input'],
            ['json', 'fail', 'data JSON'],
            ['gd', 'warn', 'kompres dan konversi gambar'],
            ['openssl', 'warn', 'email SMTP terenkripsi'],
            ['exif', 'warn', 'orientasi foto dari HP'],
        ];
        foreach ($extensions as [$ext, $level, $why]) {
            $has = extension_loaded($ext);
            $add($has ? 'ok' : $level, 'Ekstensi ' . $ext, $has ? 'Aktif' : 'Tidak aktif. Dipakai untuk ' . $why . '.');
        }

        $webp = function_exists('imagewebp');
        $add($webp ? 'ok' : 'warn', 'Dukungan WebP', $webp ? 'Aktif' : 'Tidak aktif: gambar disimpan apa adanya.');

        $max = Media::maxBytes();
        $add(
            $max >= 5242880 ? 'ok' : 'warn',
            'Batas upload per file',
            Media::humanSize($max) . ' (upload_max_filesize ' . ini_get('upload_max_filesize')
            . ', post_max_size ' . ini_get('post_max_size') . '). Disarankan 8M dan 32M.'
        );

        $memory = self::bytes((string) ini_get('memory_limit'));
        $add(
            $memory >= 134217728 ? 'ok' : 'warn',
            'Batas memori',
            (string) ini_get('memory_limit') . ($memory >= 134217728 ? '' : '. Gambar besar tidak dikonversi kalau memori kurang.')
        );

        // ---- Folder dan file ----
        $group = 'Folder dan file';

        $doc = (string) ($_SERVER['DOCUMENT_ROOT'] ?? '') !== '' ? realpath((string) $_SERVER['DOCUMENT_ROOT']) : false;
        $base = realpath(BASE_PATH);
        if ($doc !== false && $base !== false) {
            $inside = $base === $doc || str_starts_with($base . DIRECTORY_SEPARATOR, $doc . DIRECTORY_SEPARATOR);
            $add(
                $inside ? 'fail' : 'ok',
                'Lokasi folder aplikasi',
                $inside
                    ? 'Folder aplikasi berada di dalam document root, jadi .env dan source code bisa diakses publik. Pindahkan sesuai panduan.'
                    : 'Di luar document root'
            );
        }

        $add(is_file(BASE_PATH . '/.env') ? 'ok' : 'fail', 'File .env', is_file(BASE_PATH . '/.env') ? 'Ada' : 'Tidak ada');

        $dirs = [
            [BASE_PATH . '/storage/logs', 'storage/logs', 'log error'],
            [BASE_PATH . '/storage/sessions', 'storage/sessions', 'session login admin'],
            [BASE_PATH . '/storage/cache', 'storage/cache', 'cache dan konversi gambar'],
            [PUBLIC_PATH . '/uploads', 'public/uploads', 'upload gambar'],
        ];
        foreach ($dirs as [$path, $name, $why]) {
            if (!is_dir($path)) {
                $add('fail', 'Folder ' . $name, 'Tidak ada. Dipakai untuk ' . $why . '.');
            } elseif (!is_writable($path)) {
                $add('fail', 'Folder ' . $name, 'Tidak bisa ditulis. Dipakai untuk ' . $why . '. Ubah permission jadi 755 atau 775.');
            } else {
                $add('ok', 'Folder ' . $name, 'Bisa ditulis');
            }
        }

        $add(
            is_file(PUBLIC_PATH . '/uploads/.htaccess') ? 'ok' : 'warn',
            'Perlindungan folder upload',
            is_file(PUBLIC_PATH . '/uploads/.htaccess')
                ? '.htaccess ada (script tidak dieksekusi di folder upload)'
                : 'public/uploads/.htaccess tidak ada. Unggah ulang dari public.zip.'
        );

        $add(
            is_file(PUBLIC_PATH . '/assets/manifest.json') ? 'ok' : 'fail',
            'Aset frontend',
            is_file(PUBLIC_PATH . '/assets/manifest.json') ? 'manifest.json ada' : 'public/assets/manifest.json tidak ada. Unggah ulang public.zip.'
        );

        $add(class_exists('HTMLPurifier') ? 'ok' : 'fail', 'Library HTMLPurifier', class_exists('HTMLPurifier') ? 'Terpasang' : 'Tidak ditemukan. Pastikan folder vendor/ ikut diunggah.');
        $add(class_exists('PHPMailer\\PHPMailer\\PHPMailer') ? 'ok' : 'warn', 'Library PHPMailer', class_exists('PHPMailer\\PHPMailer\\PHPMailer') ? 'Terpasang' : 'Tidak ditemukan (hanya perlu untuk email).');

        // ---- Konfigurasi ----
        $group = 'Konfigurasi';
        $production = config('app.env') === 'production';
        $add($production ? 'ok' : 'warn', 'APP_ENV', (string) config('app.env') . ($production ? '' : '. Isi production di server live.'));
        $add(config('app.debug') ? 'fail' : 'ok', 'Mode debug', config('app.debug') ? 'AKTIF. Matikan di server live (APP_ENV=production, APP_DEBUG=false).' : 'Mati');

        $url = (string) config('app.url', '');
        if ($url === '') {
            $add('warn', 'APP_URL', 'Kosong: canonical, sitemap, dan JSON-LD tidak dibuat.');
        } elseif ($production && !str_starts_with($url, 'https://')) {
            $add('warn', 'APP_URL', $url . ' (belum https://)');
        } else {
            $add('ok', 'APP_URL', $url);
        }

        $add($secure ? 'ok' : 'warn', 'HTTPS pada permintaan ini', $secure ? 'Aman (HTTPS)' : 'Halaman ini dibuka lewat HTTP. Pasang SSL. Di balik Cloudflare, isi TRUST_PROXY=true.');

        $key = trim((string) env('APP_KEY', ''));
        if ($key !== '') {
            $add(strlen($key) >= 32 ? 'ok' : 'warn', 'APP_KEY', strlen($key) >= 32 ? 'Terisi' : 'Terlalu pendek, pakai minimal 32 karakter.');
        } elseif (is_file(BASE_PATH . '/storage/app.key')) {
            $add('ok', 'APP_KEY', 'Kosong, memakai kunci otomatis di storage/app.key');
        } else {
            $add('warn', 'APP_KEY', 'Kosong. Kunci dibuat otomatis saat formulir kontak pertama dibuka, tapi lebih baik diisi.');
        }

        $csp = strtolower(trim((string) env('CSP', 'on')));
        $add($csp === 'on' ? 'ok' : 'warn', 'Content Security Policy', $csp);

        $add(Mailer::enabled() ? 'ok' : 'warn', 'Notifikasi email', Mailer::enabled() ? 'Aktif' : 'Nonaktif (opsional). Isi MAIL_* di .env untuk mengaktifkan.');

        $noindex = setting('robots', 'index') === 'noindex';
        $add($noindex ? 'warn' : 'ok', 'Pengindeksan Google', $noindex ? 'Diblokir. Buka blokir di Pengaturan, tab SEO, saat website siap tayang.' : 'Diizinkan');

        // ---- Database ----
        $group = 'Database';
        try {
            $pdo = Database::connection();
            $add('ok', 'Koneksi dan versi', (string) $pdo->query('SELECT VERSION()')->fetchColumn());

            $admins = (int) $pdo->query(
                "SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id
                 WHERE r.name = 'super_admin' AND u.is_active = 1"
            )->fetchColumn();
            $add($admins >= 1 ? 'ok' : 'fail', 'Akun Super Admin aktif', (string) $admins);

            $add('ok', 'Zona waktu', 'Database ' . (string) $pdo->query('SELECT @@session.time_zone')->fetchColumn() . ', PHP ' . date('P'));
        } catch (Throwable $e) {
            ErrorHandler::log($e);
            $add('fail', 'Database', 'Pemeriksaan gagal. Lihat storage/logs.');
        }

        return $rows;
    }

    private static function bytes(string $value): int
    {
        $value = trim($value);
        if ($value === '-1') {
            return PHP_INT_MAX;
        }

        return (int) $value * match (strtolower(substr($value, -1))) {
            'g' => 1073741824,
            'm' => 1048576,
            'k' => 1024,
            default => 1,
        };
    }
}