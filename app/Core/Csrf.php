<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf');

        if (!is_string($token) || strlen($token) !== 64) {
            $token = bin2hex(random_bytes(32));
            Session::put('_csrf', $token);
        }

        return $token;
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(self::token()) . '">';
    }

    /** Token dari field form (_token) atau header X-CSRF-Token (HTMX). */
    public static function verify(Request $request): bool
    {
        $sent = $request->input('_token');
        if (!is_string($sent) || $sent === '') {
            $sent = (string) $request->header('X-CSRF-Token');
        }

        return $sent !== '' && hash_equals(self::token(), $sent);
    }
}