<?php

declare(strict_types=1);

namespace App\Core;

use Closure;
use InvalidArgumentException;

final class Route
{
    /** @var Route[] */
    private static array $routes = [];

    /** @var array<string, Route> */
    private static array $named = [];

    /** @var array<int, array{prefix: string, middleware: array, name: string}> */
    private static array $groupStack = [];

    private ?string $name = null;
    private array $wheres = [];
    private ?string $regex = null;

    private function __construct(
        public readonly string $method,
        public readonly string $uri,
        public readonly array|Closure $action,
        private array $middleware,
        private readonly string $namePrefix,
    ) {
    }

    // ---- Definisi route ----

    public static function get(string $uri, array|Closure $action): self
    {
        return self::add('GET', $uri, $action);
    }

    public static function post(string $uri, array|Closure $action): self
    {
        return self::add('POST', $uri, $action);
    }

    public static function put(string $uri, array|Closure $action): self
    {
        return self::add('PUT', $uri, $action);
    }

    public static function patch(string $uri, array|Closure $action): self
    {
        return self::add('PATCH', $uri, $action);
    }

    public static function delete(string $uri, array|Closure $action): self
    {
        return self::add('DELETE', $uri, $action);
    }

    /** Route::group(['prefix' => 'admin', 'middleware' => ['auth'], 'as' => 'admin.'], fn () => ...) */
    public static function group(array $attributes, Closure $callback): void
    {
        self::$groupStack[] = [
            'prefix' => (string) ($attributes['prefix'] ?? ''),
            'middleware' => (array) ($attributes['middleware'] ?? []),
            'name' => (string) ($attributes['as'] ?? ''),
        ];

        try {
            $callback();
        } finally {
            array_pop(self::$groupStack);
        }
    }

    /** Route::prefix('admin')->middleware(['auth'])->name('admin.')->group(fn () => ...) */
    public static function prefix(string $prefix): RouteGroup
    {
        return (new RouteGroup())->prefix($prefix);
    }

    private static function add(string $method, string $uri, array|Closure $action): self
    {
        $prefix = '';
        $middleware = [];
        $namePrefix = '';

        foreach (self::$groupStack as $group) {
            if ($group['prefix'] !== '') {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
            $middleware = array_merge($middleware, $group['middleware']);
            $namePrefix .= $group['name'];
        }

        $full = '/' . trim($prefix . '/' . trim($uri, '/'), '/');

        $route = new self($method, $full, $action, $middleware, $namePrefix);
        self::$routes[] = $route;

        return $route;
    }

    // ---- Chain method ----

    public function name(string $name): self
    {
        $this->name = $this->namePrefix . $name;
        self::$named[$this->name] = $this;

        return $this;
    }

    public function middleware(string|array $middleware): self
    {
        $this->middleware = array_merge($this->middleware, (array) $middleware);

        return $this;
    }

    /** ->where('slug', '[a-z0-9-]+') atau ->where(['id' => '[0-9]+']) */
    public function where(string|array $param, ?string $regex = null): self
    {
        $pairs = is_array($param) ? $param : [$param => (string) $regex];

        foreach ($pairs as $key => $pattern) {
            $this->wheres[$key] = $pattern;
        }
        $this->regex = null;

        return $this;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    // ---- Pencocokan dan URL ----

    /** @return Route[] */
    public static function all(): array
    {
        return self::$routes;
    }

    /** @return array<string, string>|null parameter jika cocok, null jika tidak */
    public function match(string $path): ?array
    {
        if (!preg_match($this->compile(), $path, $matches)) {
            return null;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    /** Path route dari nama, mis. '/layanan/konsultasi' (tanpa base subfolder). */
    public static function url(string $name, array $params = []): string
    {
        $route = self::$named[$name]
            ?? throw new InvalidArgumentException("Route [$name] tidak ditemukan.");

        $uri = preg_replace_callback('/\{(\w+)\}/', static function (array $m) use (&$params, $name): string {
            if (!array_key_exists($m[1], $params)) {
                throw new InvalidArgumentException("Parameter [{$m[1]}] untuk route [$name] belum diisi.");
            }
            $value = rawurlencode((string) $params[$m[1]]);
            unset($params[$m[1]]);

            return $value;
        }, $route->uri);

        return $uri . ($params !== [] ? '?' . http_build_query($params) : '');
    }

    private function compile(): string
    {
        if ($this->regex !== null) {
            return $this->regex;
        }

        $parts = preg_split('/(\{\w+\})/', $this->uri, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $regex = '';

        foreach ($parts as $part) {
            if (preg_match('/^\{(\w+)\}$/', $part, $m)) {
                $regex .= '(?P<' . $m[1] . '>' . ($this->wheres[$m[1]] ?? '[^/]+') . ')';
            } else {
                $regex .= preg_quote($part, '#');
            }
        }

        return $this->regex = '#^' . $regex . '$#u';
    }
}