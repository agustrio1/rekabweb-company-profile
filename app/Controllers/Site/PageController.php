<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Response;
use App\Services\PublicPages;

final class PageController
{
    public function show(string $slug): Response
    {
        $item = PublicPages::page($slug) ?? abort(404);

        return view('site/pages/show', ['item' => $item]);
    }
}