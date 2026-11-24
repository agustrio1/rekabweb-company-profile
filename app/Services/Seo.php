<?php

declare(strict_types=1);

namespace App\Services;

/** Meta SEO halaman publik: title, description, canonical, Open Graph, JSON-LD. */
final class Seo
{
    /**
     * Kunci $page (semuanya opsional): home (bool), title, seo_title, description, path,
     * image (URL gambar dari Media), type (website|article), noindex (bool),
     * jsonld (daftar array skema tambahan).
     *
     * @return array{title: string, description: string, canonical: ?string, robots: string,
     *               image: string, type: string, verification: string, jsonld: array<int, array>}
     */
    public static function build(array $page): array
    {
        $name = Site::name();
        $tagline = self::clean(setting('tagline', ''));
        $home = !empty($page['home']);

        $seoTitle = self::clean($page['seo_title'] ?? '');
        $pageTitle = self::clean($page['title'] ?? '');
        $title = $seoTitle !== '' ? $seoTitle : $pageTitle;

        if ($home) {
            if ($title !== '') {
                $full = $title;
            } else {
                $default = self::clean(setting('seo_title', ''));
                $full = $default !== '' ? $default : ($tagline !== '' ? $name . ' - ' . $tagline : $name);
            }
        } else {
            $full = $title !== '' ? $title . ' | ' . $name : $name;
        }

        $description = self::clean($page['description'] ?? '');
        if ($description === '') {
            $description = self::clean(setting('seo_description', ''));
        }
        if ($description === '') {
            $description = self::clean(setting('description', ''));
        }

        $origin = self::origin();
        $path = '/' . ltrim((string) ($page['path'] ?? '/'), '/');

        $image = (string) ($page['image'] ?? '');
        if ($image === '') {
            $image = setting_image('og_image');
        }

        $noindex = !empty($page['noindex']) || setting('robots', 'index') === 'noindex';

        $jsonld = [];
        if ($origin !== '') {
            if ($home) {
                $jsonld[] = self::organization($name, $origin);
                $jsonld[] = [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebSite',
                    'name' => $name,
                    'url' => $origin . url('/'),
                ];
            }
            foreach ((array) ($page['jsonld'] ?? []) as $item) {
                if (is_array($item)) {
                    $jsonld[] = $item;
                }
            }
        }

        return [
            'title' => $full,
            'description' => self::excerpt($description, 160),
            'canonical' => $origin !== '' ? $origin . url($path) : null,
            'robots' => $noindex ? 'noindex, nofollow' : 'index, follow',
            'image' => self::absolute($image),
            'type' => ($page['type'] ?? 'website') === 'article' ? 'article' : 'website',
            'verification' => trim((string) setting('google_verification', '')),
            'jsonld' => $jsonld,
        ];
    }

    /**
     * JSON-LD BreadcrumbList.
     *
     * @param array<int, array{label: string, path: ?string}> $items urutan dari beranda sampai halaman ini
     */
    public static function breadcrumbs(array $items): array
    {
        $origin = self::origin();
        if ($origin === '' || $items === []) {
            return [];
        }

        $list = [];
        foreach (array_values($items) as $i => $item) {
            $entry = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => (string) $item['label']];
            if (!empty($item['path'])) {
                $entry['item'] = $origin . url((string) $item['path']);
            }
            $list[] = $entry;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $list,
        ];
    }

    /** Potong teks (boleh HTML) jadi ringkasan maksimal $max karakter di batas kata. */
    public static function excerpt(string $html, int $max = 160): string
    {
        $text = (string) preg_replace('/<[^>]*>/', ' ', $html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim((string) preg_replace('/\s+/u', ' ', $text));

        if (mb_strlen($text) <= $max) {
            return $text;
        }

        $cut = mb_substr($text, 0, $max - 1);
        $space = mb_strrpos($cut, ' ');
        if ($space !== false && $space > (int) ($max * 0.6)) {
            $cut = mb_substr($cut, 0, $space);
        }

        return rtrim($cut, " ,.;:-") . '…';
    }

    /** URL absolut (untuk OG dan JSON-LD). Tanpa APP_URL, dikembalikan apa adanya. */
    public static function absolute(string $url): string
    {
        if ($url === '' || preg_match('#^https?://#i', $url) === 1) {
            return $url;
        }

        return self::origin() . $url;
    }

    /** Scheme + host (+ port) dari APP_URL, tanpa subfolder. Kosong kalau APP_URL belum diisi. */
    private static function origin(): string
    {
        $parts = parse_url((string) config('app.url', ''));
        if (!is_array($parts) || empty($parts['host'])) {
            return '';
        }

        return ($parts['scheme'] ?? 'https') . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
    }

    private static function organization(string $name, string $origin): array
    {
        $org = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $name,
            'url' => $origin . url('/'),
        ];

        $logo = setting_image('logo');
        if ($logo !== '') {
            $org['logo'] = self::absolute($logo);
        }

        $phone = trim((string) setting('phone', ''));
        if ($phone !== '') {
            $org['telephone'] = $phone;
        }

        $email = trim((string) setting('email', ''));
        if ($email !== '') {
            $org['email'] = $email;
        }

        $same = array_map(static fn (array $s): string => $s['url'], Site::socials());
        if ($same !== []) {
            $org['sameAs'] = $same;
        }

        return $org;
    }

    private static function clean(mixed $value): string
    {
        if (!is_string($value)) {
            return '';
        }

        return trim((string) preg_replace('/\s+/u', ' ', $value));
    }
}