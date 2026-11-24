<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    private static string $base = '';

    private function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly bool $trailingSlash,
        private readonly array $query,
        private readonly array $body,
        private readonly array $server,
    ) {
    }

    public static function capture(): self
    {
        $server = $_SERVER;
        $method = strtoupper((string) ($server['REQUEST_METHOD'] ?? 'GET'));

        // Method spoofing untuk form HTML (PUT/PATCH/DELETE lewat field _method)
        if ($method === 'POST') {
            $spoof = strtoupper((string) ($_POST['_method'] ?? ''));
            if (in_array($spoof, ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = $spoof;
            }
        }

        $path = rawurldecode((string) parse_url((string) ($server['REQUEST_URI'] ?? '/'), PHP_URL_PATH));

        // Dukungan aplikasi di subfolder
        $script = str_replace('\\', '/', (string) ($server['SCRIPT_NAME'] ?? ''));
        $base = rtrim(dirname($script), '/');
        if ($base === '.' || $base === '\\') {
            $base = '';
        }
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        $path = '/' . ltrim((string) preg_replace('#/+#', '/', $path), '/');
        $trailing = strlen($path) > 1 && str_ends_with($path, '/');
        if ($trailing) {
            $path = rtrim($path, '/');
        }

        self::$base = $base;

        return new self($method, $path, $trailing, $_GET, $_POST, $server);
    }

    public static function base(): string
    {
        return self::$base;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function hasTrailingSlash(): bool
    {
        return $this->trailingSlash;
    }

    public function queryString(): string
    {
        return (string) ($this->server['QUERY_STRING'] ?? '');
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->query : ($this->query[$key] ?? $default);
    }

    /** Ambil dari body (POST) dulu, lalu query string. */
    public function input(?string $key = null, mixed $default = null): mixed
    {
        $all = array_merge($this->query, $this->body);

        return $key === null ? $all : ($all[$key] ?? $default);
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

        return isset($this->server[$key]) ? (string) $this->server[$key] : null;
    }

    /** Sengaja hanya REMOTE_ADDR: X-Forwarded-For bisa dipalsukan klien. */
    public function ip(): string
    {
        return (string) ($this->server['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public function isSecure(): bool
    {
        $https = (string) ($this->server['HTTPS'] ?? '');
        if ($https !== '' && strtolower($https) !== 'off') {
            return true;
        }

        if ((int) ($this->server['SERVER_PORT'] ?? 0) === 443) {
            return true;
        }

        return (bool) Config::get('app.trust_proxy', false)
            && strtolower((string) $this->header('X-Forwarded-Proto')) === 'https';
    }

    public function isHtmx(): bool
    {
        return $this->header('HX-Request') === 'true';
    }
}