<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ErrorHandler;
use Throwable;

/** Sitemap XML dan robots.txt. Hanya konten yang sudah tayang. */
final class Sitemap
{
    private const LIMIT = 5000;

    /** Sitemap hanya dibuat kalau APP_URL terisi dan pengindeksan tidak diblokir. */
    public static function enabled(): bool
    {
        return trim((string) config('app.url', '')) !== '' && setting('robots', 'index') !== 'noindex';
    }

    public static function robots(): string
    {
        if (setting('robots', 'index') === 'noindex') {
            return "User-agent: *\nDisallow: " . url('/') . "\n";
        }

        $lines = [
            'User-agent: *',
            'Disallow: ' . url('/admin'),
            'Disallow: ' . url('/kontak/terkirim'),
            'Allow: ' . url('/'),
        ];

        if (trim((string) config('app.url', '')) !== '') {
            $lines[] = '';
            $lines[] = 'Sitemap: ' . Seo::absolute(url('/sitemap.xml'));
        }

        return implode("\n", $lines) . "\n";
    }

    public static function xml(): string
    {
        $out = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];

        foreach (self::urls() as $item) {
            $out[] = '  <url>';
            $out[] = '    <loc>' . htmlspecialchars(Seo::absolute(url($item['path'])), ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>';
            if ($item['date'] !== null) {
                $out[] = '    <lastmod>' . $item['date'] . '</lastmod>';
            }
            $out[] = '  </url>';
        }

        $out[] = '</urlset>';

        return implode("\n", $out) . "\n";
    }

    /** @return array<int, array{path: string, date: ?string}> */
    public static function urls(): array
    {
        $now = date('Y-m-d H:i:s');
        $urls = [['path' => '/', 'date' => null], ['path' => '/kontak', 'date' => null]];

        $add = static function (string $path, ?string $updated = null) use (&$urls): void {
            $time = $updated !== null ? strtotime($updated) : false;
            $urls[] = ['path' => $path, 'date' => $time !== false ? date('Y-m-d', $time) : null];
        };

        $limit = self::LIMIT;

        $services = self::rows(
            "SELECT slug, updated_at FROM services
             WHERE status = 'published' AND (published_at IS NULL OR published_at <= ?)
             ORDER BY sort_order ASC, id ASC LIMIT $limit",
            [$now]
        );
        if ($services !== []) {
            $add('/layanan');
            foreach ($services as $r) {
                $add('/layanan/' . $r['slug'], (string) $r['updated_at']);
            }
        }

        $projects = self::rows(
            "SELECT slug, updated_at FROM projects
             WHERE status = 'published' AND (published_at IS NULL OR published_at <= ?)
             ORDER BY sort_order ASC, id ASC LIMIT $limit",
            [$now]
        );
        if ($projects !== []) {
            $add('/proyek');
            foreach (self::rows(
                "SELECT DISTINCT c.slug FROM project_categories c
                 JOIN projects p ON p.category_id = c.id
                 AND p.status = 'published' AND (p.published_at IS NULL OR p.published_at <= ?)
                 ORDER BY c.slug ASC",
                [$now]
            ) as $r) {
                $add('/proyek/kategori/' . $r['slug']);
            }
            foreach ($projects as $r) {
                $add('/proyek/' . $r['slug'], (string) $r['updated_at']);
            }
        }

        $articles = self::rows(
            "SELECT slug, updated_at FROM articles
             WHERE status = 'published' AND (published_at IS NULL OR published_at <= ?)
             ORDER BY published_at DESC, id DESC LIMIT $limit",
            [$now]
        );
        if ($articles !== []) {
            $add('/blog');
            foreach (self::rows(
                "SELECT DISTINCT c.slug FROM article_categories c
                 JOIN articles a ON a.category_id = c.id
                 AND a.status = 'published' AND (a.published_at IS NULL OR a.published_at <= ?)
                 ORDER BY c.slug ASC",
                [$now]
            ) as $r) {
                $add('/blog/kategori/' . $r['slug']);
            }
            foreach (self::rows(
                "SELECT DISTINCT t.slug FROM tags t
                 JOIN article_tag x ON x.tag_id = t.id
                 JOIN articles a ON a.id = x.article_id
                 AND a.status = 'published' AND (a.published_at IS NULL OR a.published_at <= ?)
                 ORDER BY t.slug ASC",
                [$now]
            ) as $r) {
                $add('/blog/tag/' . $r['slug']);
            }
            foreach ($articles as $r) {
                $add('/blog/' . $r['slug'], (string) $r['updated_at']);
            }
        }

        if (self::rows("SELECT 1 FROM faqs WHERE status = 'published' LIMIT 1") !== []) {
            $add('/faq');
        }

        foreach (self::rows(
            "SELECT slug, updated_at FROM pages
             WHERE status = 'published' AND (published_at IS NULL OR published_at <= ?)
             ORDER BY slug ASC LIMIT $limit",
            [$now]
        ) as $r) {
            $add('/' . $r['slug'], (string) $r['updated_at']);
        }

        return $urls;
    }

    /**
     * @param array<int, mixed> $params
     * @return array<int, array>
     */
    private static function rows(string $sql, array $params = []): array
    {
        try {
            $stmt = Database::connection()->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll();
        } catch (Throwable $e) {
            ErrorHandler::log($e);

            return [];
        }
    }
}