<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Request;
use App\Core\Response;
use App\Services\Homepage;
use App\Services\SiteContent;

final class HomeController
{
    public function index(Request $request): Response
    {
        $sections = [];

        foreach (Homepage::active() as $key => $sec) {
            $data = $sec['data'];
            $count = (int) ($data['count'] ?? 3);
            $items = [];
            $image = null;

            switch ($key) {
                case 'hero':
                    $image = SiteContent::media((int) ($data['image'] ?? 0));
                    break;

                case 'about':
                    $image = SiteContent::media((int) ($data['image'] ?? 0));
                    if (trim((string) ($data['text'] ?? '')) === '' && $image === null) {
                        continue 2;
                    }
                    break;

                case 'services':
                    $items = SiteContent::services($count);
                    break;

                case 'portfolio':
                    $items = SiteContent::projects($count);
                    break;

                case 'testimonials':
                    $items = SiteContent::testimonials($count);
                    break;

                case 'faq':
                    $items = SiteContent::faqs($count);
                    break;

                case 'articles':
                    $items = SiteContent::articles($count);
                    break;

                case 'advantages':
                case 'stats':
                case 'process':
                    $items = is_array($data['items'] ?? null) ? $data['items'] : [];
                    break;
            }

            // Section berisi daftar tidak tampil kalau daftarnya kosong
            $needsItems = ['services', 'portfolio', 'testimonials', 'faq', 'articles', 'advantages', 'stats', 'process'];
            if (in_array($key, $needsItems, true) && $items === []) {
                continue;
            }

            $sections[] = ['key' => $key, 'data' => $data, 'items' => $items, 'image' => $image];
        }

        return view('site/home', ['sections' => $sections]);
    }
}