<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class Session
{
    private static bool $started = false;

    public static function start(Request $request): void
    {
        if (self::$started) {
            return;
        }

        // Simpan session di luar public dan di luar /tmp bersama
        $dir = BASE_PATH . '/storage/sessions';
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }
        if (is_dir($dir) && is_writable($dir)) {
            session_save_path($dir);
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        ini_set('session.gc_maxlifetime', '7200');
        ini_set('session.gc_probability', '1');
        ini_set('session.gc_divisor', '100');

        session_name('cp_sid');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => Request::base() !== '' ? Request::base() : '/',
            'secure' => $request->isSecure(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        if (!session_start()) {
            throw new RuntimeException('Session gagal dimulai.');
        }

        self::$started = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$started ? ($_SESSION[$key] ?? $default) : $default;
    }

    public static function put(string $key, mixed $value): void
    {
        if (self::$started) {
            $_SESSION[$key] = $value;
        }
    }

    public static function forget(string $key): void
    {
        if (self::$started) {
            unset($_SESSION[$key]);
        }
    }

    public static function pull(string $key, mixed $default = null): mixed
    {
        $value = self::get($key, $default);
        self::forget($key);

        return $value;
    }

    /** Pesan sekali tampil: diset di request ini, dibaca di request berikutnya. */
    public static function flash(string $key, mixed $value): void
    {
        if (self::$started) {
            $_SESSION['_flash'][$key] = $value;
        }
    }

    public static function pullFlash(string $key): mixed
    {
        if (!self::$started) {
            return null;
        }

        $value = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);

        return $value;
    }

    /** Ganti ID session (cegah session fixation). */
    public static function regenerate(): void
    {
        if (self::$started) {
            session_regenerate_id(true);
        }
    }

    public static function destroy(): void
    {
        if (!self::$started) {
            return;
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $p['path'],
                'domain' => $p['domain'],
                'secure' => $p['secure'],
                'httponly' => $p['httponly'],
                'samesite' => $p['samesite'],
            ]);
        }

        session_destroy();
        self::$started = false;
    }
}