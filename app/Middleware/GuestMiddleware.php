<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\Auth;

/** Halaman khusus tamu (login). Kalau sudah login, arahkan ke dashboard. */
final class GuestMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (Auth::user() !== null) {
            return Response::redirect(route('admin.dashboard'));
        }

        return $next($request);
    }
}