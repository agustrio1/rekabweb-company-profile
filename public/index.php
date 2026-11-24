<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

// Root proyek = folder yang berisi app/, config/, routes/, storage/.
// Layout standar: public/ ada di dalam root proyek, jadi dirname(__DIR__) sudah benar.
// Layout hosting dengan folder aplikasi di luar public_html: buat file .app-path di folder ini,
// isi satu baris path absolut folder aplikasi, contoh: /home/akun/cp-app
$root = dirname(__DIR__);
$pointer = __DIR__ . '/.app-path';
if (is_file($pointer)) {
    $custom = rtrim(trim((string) file_get_contents($pointer)), '/');
    if ($custom !== '') {
        $root = $custom;
    }
}

if (!is_file($root . '/app/bootstrap.php')) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Folder aplikasi tidak ditemukan. Periksa file .app-path di folder ini.');
}

define('BASE_PATH', $root);
define('PUBLIC_PATH', __DIR__);

require BASE_PATH . '/app/bootstrap.php';
require BASE_PATH . '/routes/web.php';

$request = Request::capture();

// Paksa HTTPS bila APP_URL memakai https dan permintaan ke domain itu datang lewat HTTP.
// Di balik Cloudflare atau proxy lain, isi TRUST_PROXY=true supaya tidak terjadi redirect berulang.
$origin = parse_url((string) config('app.url', ''));
if (
    PHP_SAPI !== 'cli-server'
    && filter_var(env('FORCE_HTTPS', true), FILTER_VALIDATE_BOOLEAN)
    && is_array($origin)
    && ($origin['scheme'] ?? '') === 'https'
    && !empty($origin['host'])
    && !$request->isSecure()
) {
    $host = strtolower((string) preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));

    // Hanya domain milik APP_URL yang dialihkan, supaya header Host palsu tidak bisa dimanfaatkan
    if ($host === strtolower((string) $origin['host'])) {
        $status = in_array($request->method(), ['GET', 'HEAD'], true) ? 301 : 308;
        Response::redirect('https://' . $origin['host'] . (string) ($_SERVER['REQUEST_URI'] ?? '/'), $status)->send();
        exit;
    }
}

Router::dispatch($request)->send();