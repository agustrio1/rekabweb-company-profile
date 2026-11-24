<?php

declare(strict_types=1);

namespace App\Core;

final class Config
{
    /** @var array<string, array<string, mixed>> */
    private static array $items = [];

    /** Contoh: Config::get('app.debug'), Config::get('database') */
    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $file = (string) array_shift($segments);

        if (!preg_match('/^[a-z_]+$/', $file)) {
            return $default;
        }

        if (!isset(self::$items[$file])) {
            $path = BASE_PATH . '/config/' . $file . '.php';
            self::$items[$file] = is_file($path) ? (array) require $path : [];
        }

        $value = self::$items[$file];

        foreach ($segments as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }

        return $value;
    }
}