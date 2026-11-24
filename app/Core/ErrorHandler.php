<?php

declare(strict_types=1);

namespace App\Core;

use ErrorException;
use Throwable;

final class ErrorHandler
{
    private const LOG_KEEP_DAYS = 30;

    private const TITLES = [
        400 => 'Permintaan tidak valid',
        403 => 'Akses ditolak',
        404 => 'Halaman tidak ditemukan',
        405 => 'Metode tidak diizinkan',
        419 => 'Sesi kedaluwarsa',
        429 => 'Terlalu banyak permintaan',
        500 => 'Terjadi kesalahan pada server',
    ];

    private const MESSAGES = [
        400 => 'Permintaan tidak bisa diproses. Periksa alamat atau isian lalu coba lagi.',
        403 => 'Kamu tidak punya izin untuk membuka halaman ini.',
        404 => 'Halaman yang kamu cari tidak ada atau sudah dipindahkan.',
        405 => 'Metode permintaan ini tidak didukung untuk alamat tersebut.',
        419 => 'Sesi sudah berakhir. Muat ulang halaman lalu coba lagi.',
        429 => 'Terlalu banyak percobaan. Tunggu sebentar lalu coba lagi.',
        500 => 'Maaf, ada masalah di sisi kami. Silakan coba lagi nanti.',
    ];

    public static function register(): void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        ini_set('log_errors', '0');
        // Cegah argumen fungsi (mis. password DB) ikut tercatat di stack trace
        ini_set('zend.exception_ignore_args', '1');

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            // Deprecation dari library cukup dicatat, jangan menghentikan request
            if ($severity & (E_DEPRECATED | E_USER_DEPRECATED)) {
                self::log(new ErrorException($message, 0, $severity, $file, $line));

                return true;
            }

            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler([self::class, 'handle']);

        register_shutdown_function(static function (): void {
            $error = error_get_last();
            $fatal = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];

            if ($error !== null && in_array($error['type'], $fatal, true)) {
                self::handle(new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
            }
        });
    }

    public static function handle(Throwable $e): void
    {
        $status = $e instanceof HttpException ? $e->statusCode : 500;

        if ($status >= 500) {
            self::log($e);
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        try {
            $debug = (bool) Config::get('app.debug', false);
        } catch (Throwable) {
            $debug = false;
        }

        $title = self::TITLES[$status] ?? 'Terjadi kesalahan';
        $message = self::MESSAGES[$status] ?? self::MESSAGES[500];
        $path = self::requestPath();
        $admin = $path === '/admin' || str_starts_with($path, '/admin/');

        $headers = $e instanceof HttpException ? $e->headers : [];
        $headers += ['X-Robots-Tag' => 'noindex'];
        if ($status >= 500 || $admin) {
            $headers += ['Cache-Control' => 'no-store'];
        }

        // Permintaan fetch/HTMX yang meminta JSON dijawab dengan JSON
        if (str_contains(strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? '')), 'application/json')) {
            try {
                $json = Response::json(['error' => $title, 'status' => $status], $status);
                foreach ($headers as $name => $value) {
                    $json->header((string) $name, (string) $value);
                }
                $json->send();

                return;
            } catch (Throwable) {
                // lanjut ke halaman HTML
            }
        }

        $headers += ['Content-Type' => 'text/html; charset=UTF-8'];

        try {
            $html = self::render($status, $title, $message, $debug ? self::describe($e) : '', $path, $admin);
        } catch (Throwable) {
            $html = 'Terjadi kesalahan.';
            $headers['Content-Type'] = 'text/plain; charset=UTF-8';
        }

        (new Response($html, $status, $headers))->send();
    }

    public static function log(Throwable $e): void
    {
        $entry = sprintf(
            "[%s] %s %s\n%s: %s in %s:%d\n%s\n",
            date('Y-m-d H:i:s'),
            (string) ($_SERVER['REQUEST_METHOD'] ?? 'CLI'),
            // Query string sengaja tidak dicatat: bisa memuat data sensitif
            self::requestPath(),
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        $dir = BASE_PATH . '/storage/logs';
        $written = is_dir($dir) && is_writable($dir)
            && @file_put_contents($dir . '/app-' . date('Y-m-d') . '.log', $entry . PHP_EOL, FILE_APPEND | LOCK_EX) !== false;

        if (!$written) {
            error_log(get_class($e) . ': ' . $e->getMessage());

            return;
        }

        self::prune($dir);
    }

    /** Halaman 4xx publik memakai layout situs, sisanya halaman mandiri tanpa database. */
    private static function render(int $status, string $title, string $message, string $debug, string $path, bool $admin): string
    {
        $data = [
            'status' => $status,
            'title' => $title,
            'message' => $message,
            'debug' => $debug,
            'path' => $path,
        ];

        if ($status < 500 && !$admin) {
            try {
                return View::render('errors/public', $data);
            } catch (Throwable $e) {
                // Mis. database mati saat layout membaca pengaturan: pakai halaman mandiri
                self::log($e);
            }
        }

        $data['homeUrl'] = url($admin ? '/admin' : '/');
        $data['homeLabel'] = $admin ? 'Kembali ke dashboard' : 'Kembali ke beranda';

        return View::render('errors/error', $data);
    }

    /** Hapus log lama, dicek sesekali saja. */
    private static function prune(string $dir): void
    {
        try {
            if (random_int(1, 50) !== 1) {
                return;
            }

            $limit = time() - self::LOG_KEEP_DAYS * 86400;
            foreach (glob($dir . '/app-*.log') ?: [] as $file) {
                if (is_file($file) && (int) filemtime($file) < $limit) {
                    @unlink($file);
                }
            }
        } catch (Throwable) {
            // pembersihan log tidak boleh menimbulkan error baru
        }
    }

    /** Path permintaan tanpa query string dan tanpa subfolder aplikasi. */
    private static function requestPath(): string
    {
        $path = rawurldecode((string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH));
        $base = Request::base();

        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        return '/' . ltrim($path, '/');
    }

    private static function describe(Throwable $e): string
    {
        return sprintf(
            "%s: %s\n%s:%d\n\n%s",
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
    }
}