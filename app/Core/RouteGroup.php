<?php

declare(strict_types=1);

namespace App\Core;

use Closure;

final class RouteGroup
{
    private string $prefix = '';
    private array $middleware = [];
    private string $name = '';

    public function prefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function middleware(string|array $middleware): self
    {
        $this->middleware = array_merge($this->middleware, (array) $middleware);

        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function group(Closure $callback): void
    {
        Route::group([
            'prefix' => $this->prefix,
            'middleware' => $this->middleware,
            'as' => $this->name,
        ], $callback);
    }
}