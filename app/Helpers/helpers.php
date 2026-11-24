<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Env;
use App\Core\ErrorHandler;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Route;
use App\Core\View;

function base_path(string $path = ''): string
{
    return BASE_PATH . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

function env(string $key, mixed $default = null): mixed
{
    return Env::get($key, $default);
}

function config(string $key, mixed $default = null): mixed
{
    return Config::get($key, $default);
}

/** Escape output HTML. Wajib dipakai untuk semua data dinamis di view. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** URL relatif dari root domain, sudah memperhitungkan subfolder. */
function url(string $path = ''): string
{
    return Request::base() . '/' . ltrim($path, '/');
}

/** URL absolut berdasarkan APP_URL (untuk canonical, sitemap, OG). */
function full_url(string $path = ''): string
{
    return (string) config('app.url', '') . '/' . ltrim($path, '/');
}

/** URL dari nama route, contoh: route('services.show', ['slug' => 'konsultasi']) */
function route(string $name, array $params = []): string
{
    return url(Route::url($name, $params));
}

function redirect(string $to, int $status = 302): Response
{
    if (!preg_match('#^https?://#i', $to)) {
        $to = url($to);
    }

    return Response::redirect($to, $status);
}

function abort(int $status, string $message = ''): never
{
    throw new HttpException($status, $message);
}

function view(string $name, array $data = [], int $status = 200): Response
{
    return Response::html(View::render($name, $data), $status);
}

/** URL file statis di public/ dengan versi dari waktu modifikasi (cache busting). */
function asset(string $path): string
{
    $file = PUBLIC_PATH . '/' . ltrim($path, '/');
    $version = is_file($file) ? '?v=' . filemtime($file) : '';

    return url($path) . $version;
}

/**
 * Tag <link> dan <script> untuk entry hasil build Vite, dibaca dari manifest.
 * Contoh: <?= vite('resources/js/public.js') ?>
 * Hasilnya HTML mentah yang aman (semua nilai di-escape di sini), jangan di-escape lagi.
 */
function vite(string $entry): string
{
    static $manifest = null;

    if ($manifest === null) {
        $file = PUBLIC_PATH . '/assets/manifest.json';
        $manifest = is_file($file)
            ? (json_decode((string) file_get_contents($file), true) ?: [])
            : [];
    }

    if (!isset($manifest[$entry])) {
        $error = new RuntimeException("Asset [$entry] tidak ada di manifest. Jalankan 'npm run build'.");

        if (config('app.debug')) {
            throw $error;
        }

        ErrorHandler::log($error);

        return '';
    }

    $css = [];
    $seen = [];

    $collect = function (string $key) use (&$collect, &$css, &$seen, $manifest): void {
        if (isset($seen[$key]) || !isset($manifest[$key])) {
            return;
        }
        $seen[$key] = true;

        foreach ($manifest[$key]['imports'] ?? [] as $import) {
            $collect($import);
        }
        foreach ($manifest[$key]['css'] ?? [] as $file) {
            $css[] = $file;
        }
    };

    $collect($entry);

    $tags = '';
    foreach (array_unique($css) as $file) {
        $tags .= '<link rel="stylesheet" href="' . e(url('assets/' . $file)) . '">' . "\n";
    }
    $tags .= '<script type="module" src="' . e(url('assets/' . $manifest[$entry]['file'])) . '"></script>' . "\n";

    return $tags;
}