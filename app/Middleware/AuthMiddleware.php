<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\Auth;

final class AuthMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        $user = Auth::user();
        $expired = false;

        if ($user !== null && !Auth::touch()) {
            $expired = true;
            $user = null;
        }

        if ($user !== null) {
            return $next($request);
        }

        if ($expired) {
            Auth::logout();
            Session::start($request);
            Session::flash('error', 'Sesi berakhir karena tidak aktif. Silakan masuk lagi.');
        }

        if ($request->isHtmx()) {
            return (new Response('', 401))->header('HX-Redirect', route('admin.login'));
        }

        if ($request->method() === 'GET') {
            Session::put('intended', $request->path());
        }

        return Response::redirect(route('admin.login'));
    }
}