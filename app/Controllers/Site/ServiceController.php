<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Response;
use App\Services\Catalog;

final class ServiceController
{
    public function index(): Response
    {
        return view('site/services/index', ['items' => Catalog::services()]);
    }

    public function show(string $slug): Response
    {
        $service = Catalog::service($slug) ?? abort(404);

        return view('site/services/show', [
            'service' => $service,
            'others' => Catalog::otherServices((int) $service['id'], 3),
        ]);
    }
}