<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/** Memulai session, memverifikasi CSRF untuk method yang mengubah data. */
final class WebMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        Session::start($request);

        if (!in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true) && !Csrf::verify($request)) {
            throw new HttpException(419);
        }

        return $next($request)->header('Cache-Control', 'no-store, private');
    }
}