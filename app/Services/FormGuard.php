<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Request;
use RuntimeException;

/**
 * Pengaman formulir publik tanpa session: token bertanda tangan, pemeriksaan waktu isi,
 * pemeriksaan asal permintaan (Origin/Referer), dan honeypot.
 */
final class FormGuard
{
    public const HONEYPOT = 'website';
    public const FIELD = '_gtoken';

    private const MIN_AGE = 3;      // detik; lebih cepat dari ini dianggap bot
    private const MAX_AGE = 7200;   // 2 jam

    public static function token(): string
    {
        $payload = time() . '.' . bin2hex(random_bytes(8));

        return $payload . '.' . self::sign($payload);
    }

    /** @return string ok | fast | expired | invalid */
    public static function check(mixed $token): string
    {
        if (!is_string($token) || strlen($token) > 100) {
            return 'invalid';
        }

        $parts = explode('.', $token);
        if (count($parts) !== 3 || !ctype_digit($parts[0]) || preg_match('/^[a-f0-9]{16}$/', $parts[1]) !== 1) {
            return 'invalid';
        }

        if (!hash_equals(self::sign($parts[0] . '.' . $parts[1]), $parts[2])) {
            return 'invalid';
        }

        $age = time() - (int) $parts[0];
        if ($age < 0) {
            return 'invalid';
        }
        if ($age < self::MIN_AGE) {
            return 'fast';
        }

        return $age > self::MAX_AGE ? 'expired' : 'ok';
    }

    /** Tolak kiriman dari situs lain. Tanpa header Origin dan Referer, dianggap sah. */
    public static function sameOrigin(Request $request): bool
    {
        $source = $request->header('Origin');
        if ($source === null || $source === '') {
            $source = $request->header('Referer');
        }
        if ($source === null || $source === '') {
            return true;
        }

        $parts = parse_url($source);
        if (!is_array($parts) || empty($parts['host'])) {
            return false;
        }

        $authority = strtolower($parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : ''));

        return $authority === strtolower((string) $request->header('Host'));
    }

    private static function sign(string $payload): string
    {
        return hash_hmac('sha256', $payload, self::secret());
    }

    private static function secret(): string
    {
        static $secret = null;
        if ($secret !== null) {
            return $secret;
        }

        $key = trim((string) env('APP_KEY', ''));
        if ($key !== '') {
            return $secret = $key;
        }

        $file = BASE_PATH . '/storage/app.key';
        if (is_file($file)) {
            $stored = trim((string) file_get_contents($file));
            if (strlen($stored) >= 32) {
                return $secret = $stored;
            }
        }

        $stored = bin2hex(random_bytes(32));
        if (@file_put_contents($file, $stored, LOCK_EX) === false) {
            throw new RuntimeException('Kunci formulir tidak bisa dibuat. Periksa permission folder storage/.');
        }
        @chmod($file, 0600);

        return $secret = $stored;
    }
}