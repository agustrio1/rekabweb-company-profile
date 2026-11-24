<?php

declare(strict_types=1);

namespace App\Core;

final class Env
{
    /** @var array<string, mixed> */
    private static array $vars = [];

    public static function load(string $file): void
    {
        if (!is_file($file) || !is_readable($file)) {
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || $line[0] === '#') {
                continue;
            }

            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }

            $key = trim(substr($line, 0, $pos));
            if ($key === '') {
                continue;
            }

            self::$vars[$key] = self::parse(trim(substr($line, $pos + 1)));
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, self::$vars) ? self::$vars[$key] : $default;
    }

    private static function parse(string $value): mixed
    {
        // Nilai dalam tanda kutip dipakai apa adanya
        if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
            $end = strrpos($value, $value[0]);
            if ($end !== false && $end > 0) {
                return substr($value, 1, $end - 1);
            }
        }

        // Buang komentar di akhir baris
        $hash = strpos($value, ' #');
        if ($hash !== false) {
            $value = rtrim(substr($value, 0, $hash));
        }

        return match (strtolower($value)) {
            'true' => true,
            'false' => false,
            'null' => null,
            default => $value,
        };
    }
}