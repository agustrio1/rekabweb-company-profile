<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Response;
use App\Services\PublicPages;

final class FaqController
{
    public function index(): Response
    {
        return view('site/faq', ['faqs' => PublicPages::faqs()]);
    }
}