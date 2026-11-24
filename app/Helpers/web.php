<?php

declare(strict_types=1);

use App\Core\Csrf;
use App\Core\Response;
use App\Core\Session;
use App\Services\Auth;
use App\Services\Settings;

function csrf_token(): string
{
    return Csrf::token();
}

function csrf_field(): string
{
    return Csrf::field();
}

function auth_user(): ?array
{
    return Auth::user();
}

function can(string $permission): bool
{
    return Auth::can($permission);
}

/** Baca pesan flash (sekali tampil). */
function flash(string $key): mixed
{
    return Session::pullFlash($key);
}

function redirect_route(string $name, array $params = [], int $status = 302): Response
{
    return Response::redirect(route($name, $params), $status);
}

/** Nilai pengaturan website, contoh: setting('company_name'). */
function setting(string $key, ?string $default = null): ?string
{
    return Settings::get($key, $default);
}

/** URL gambar dari pengaturan, contoh: setting_image('logo'). Kosong kalau belum diisi. */
function setting_image(string $key): string
{
    return Settings::imageUrl($key);
}