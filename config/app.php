<?php

declare(strict_types=1);

$env = (string) env('APP_ENV', 'production');

return [
    'name' => (string) env('APP_NAME', 'Company Profile'),
    'env' => $env,
    // Debug tidak pernah aktif di production, apa pun isi .env
    'debug' => $env !== 'production' && (bool) env('APP_DEBUG', false),
    'url' => rtrim((string) env('APP_URL', ''), '/'),
    'timezone' => (string) env('APP_TIMEZONE', 'Asia/Jakarta'),
    // Aktifkan hanya jika di belakang proxy tepercaya (mis. Cloudflare)
    'trust_proxy' => (bool) env('TRUST_PROXY', false),
];