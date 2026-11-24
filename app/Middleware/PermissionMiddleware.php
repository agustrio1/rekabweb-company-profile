<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\Auth;

/** Dipakai sebagai 'can:izin.satu,izin.dua' (cukup salah satu terpenuhi). */
final class PermissionMiddleware
{
    public function handle(Request $request, callable $next, string ...$permissions): Response
    {
        foreach ($permissions as $permission) {
            if (Auth::can($permission)) {
                return $next($request);
            }
        }

        throw new HttpException(403);
    }
}