<?php

declare(strict_types=1);

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Middleware\WebMiddleware;

return [
    'web' => WebMiddleware::class,
    'auth' => AuthMiddleware::class,
    'guest' => GuestMiddleware::class,
    'can' => PermissionMiddleware::class,
];