<?php

declare(strict_types=1);

namespace App\Core;

use Closure;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;

final class Router
{
    /** @var array<string, class-string> */
    private static array $aliases = [];

    /**
     * Daftarkan alias middleware, mis. Router::alias(['auth' => AuthMiddleware::class]).
     * Kontrak middleware: handle(Request $request, callable $next, string ...$args): Response
     * Dipakai di route sebagai 'auth' atau 'role:administrator,editor'.
     */
    public static function alias(array $map): void
    {
        self::$aliases = array_merge(self::$aliases, $map);
    }

    public static function dispatch(Request $request): Response
    {
        $method = $request->method();

        // Hindari duplikasi URL: /layanan/ => 301 ke /layanan
        if ($request->hasTrailingSlash() && in_array($method, ['GET', 'HEAD'], true)) {
            $qs = $request->queryString();

            return Response::redirect(url($request->path()) . ($qs !== '' ? '?' . $qs : ''), 301);
        }

        if ($method === 'HEAD') {
            $method = 'GET';
        }

        $allowed = [];

        foreach (Route::all() as $route) {
            $params = $route->match($request->path());
            if ($params === null) {
                continue;
            }

            if ($route->method !== $method) {
                $allowed[] = $route->method;
                continue;
            }

            return self::run($route, $request, $params);
        }

        if ($allowed !== []) {
            throw new HttpException(405, '', ['Allow' => implode(', ', array_unique($allowed))]);
        }

        throw new HttpException(404);
    }

    private static function run(Route $route, Request $request, array $params): Response
    {
        $core = static fn (Request $req): Response => self::toResponse(self::invoke($route->action, $req, $params));

        $pipeline = array_reduce(
            array_reverse($route->getMiddleware()),
            static function (callable $next, string $spec): callable {
                return static function (Request $req) use ($next, $spec): Response {
                    [$class, $args] = self::resolveMiddleware($spec);

                    return (new $class())->handle($req, $next, ...$args);
                };
            },
            $core
        );

        return $pipeline($request);
    }

    /** @return array{0: class-string, 1: string[]} */
    private static function resolveMiddleware(string $spec): array
    {
        $name = $spec;
        $args = [];

        if (str_contains($spec, ':')) {
            [$name, $argString] = explode(':', $spec, 2);
            $args = $argString === '' ? [] : explode(',', $argString);
        }

        $class = self::$aliases[$name] ?? (class_exists($name) ? $name : null);
        if ($class === null) {
            throw new RuntimeException("Middleware [$name] tidak terdaftar.");
        }

        return [$class, $args];
    }

    /** Parameter dicocokkan berdasarkan nama; Request di-inject berdasarkan tipe. */
    private static function invoke(array|Closure $action, Request $request, array $params): mixed
    {
        $reflection = is_array($action)
            ? new ReflectionMethod($action[0], $action[1])
            : new ReflectionFunction($action);

        $args = [];

        foreach ($reflection->getParameters() as $param) {
            $type = $param->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()
                && is_a($type->getName(), Request::class, true)) {
                $args[] = $request;
                continue;
            }

            if (array_key_exists($param->getName(), $params)) {
                $args[] = self::castParam($params[$param->getName()], $type);
                continue;
            }

            if ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
                continue;
            }

            throw new RuntimeException('Parameter [' . $param->getName() . '] tidak bisa diisi dari route.');
        }

        if (is_array($action)) {
            return (new $action[0]())->{$action[1]}(...$args);
        }

        return $action(...$args);
    }

    private static function castParam(string $value, mixed $type): mixed
    {
        if (!$type instanceof ReflectionNamedType || !$type->isBuiltin()) {
            return $value;
        }

        return match ($type->getName()) {
            'int' => preg_match('/^-?\d+$/', $value) ? (int) $value : throw new HttpException(404),
            'float' => is_numeric($value) ? (float) $value : throw new HttpException(404),
            default => $value,
        };
    }

    private static function toResponse(mixed $result): Response
    {
        return match (true) {
            $result instanceof Response => $result,
            is_string($result) => Response::html($result),
            is_array($result) => Response::json($result),
            $result === null => new Response('', 204),
            default => throw new RuntimeException('Controller harus mengembalikan Response, string, atau array.'),
        };
    }
}