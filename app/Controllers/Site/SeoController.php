<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Response;
use App\Services\Sitemap;

final class SeoController
{
    public function sitemap(): Response
    {
        if (!Sitemap::enabled()) {
            abort(404);
        }

        return new Response(Sitemap::xml(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function robots(): Response
    {
        return new Response(Sitemap::robots(), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}