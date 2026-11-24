<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ErrorHandler;
use Throwable;

/** Artikel terbit untuk halaman publik (daftar, detail, kategori, tag, terkait). */
final class Blog
{
    public const PER_PAGE = 9;

    private const PUBLISHED = "a.status = 'published' AND (a.published_at IS NULL OR a.published_at <= ?)";

    private const CARD = "SELECT a.id, a.title, a.slug, a.excerpt, a.category_id,
                    COALESCE(a.published_at, a.created_at) AS pub_date,
                    IF(a.excerpt = '', LEFT(a.content, 1500), '') AS body,
                    c.name AS category, c.slug AS category_slug,
                    m.path, m.alt_text, m.width, m.height
             FROM articles a
             LEFT JOIN article_categories c ON c.id = a.category_id
             LEFT JOIN media m ON m.id = a.featured_image_id";

    /** Kategori yang punya minimal satu artikel terbit. @return array<int, array> */
    public static function categories(): array
    {
        return self::rows(
            "SELECT c.id, c.name, c.slug, COUNT(a.id) AS n
             FROM article_categories c
             JOIN articles a ON a.category_id = c.id AND " . self::PUBLISHED . "
             GROUP BY c.id, c.name, c.slug
             ORDER BY c.name ASC",
            [self::now()]
        );
    }

    /** Tag yang paling banyak dipakai artikel terbit. @return array<int, array> */
    public static function tags(int $limit = 20): array
    {
        $limit = max(1, min(50, $limit));

        return self::rows(
            "SELECT t.id, t.name, t.slug, COUNT(a.id) AS n
             FROM tags t
             JOIN article_tag x ON x.tag_id = t.id
             JOIN articles a ON a.id = x.article_id AND " . self::PUBLISHED . "
             GROUP BY t.id, t.name, t.slug
             ORDER BY n DESC, t.name ASC
             LIMIT $limit",
            [self::now()]
        );
    }

    public static function category(string $slug): ?array
    {
        $rows = self::rows('SELECT id, name, slug FROM article_categories WHERE slug = ? LIMIT 1', [$slug]);

        return $rows[0] ?? null;
    }

    public static function tag(string $slug): ?array
    {
        $rows = self::rows('SELECT id, name, slug FROM tags WHERE slug = ? LIMIT 1', [$slug]);

        return $rows[0] ?? null;
    }

    /**
     * @return array{0: array<int, array>, 1: int} [baris, total artikel]
     */
    public static function articles(?int $categoryId, ?int $tagId, int $page): array
    {
        $where = self::PUBLISHED;
        $params = [self::now()];

        if ($categoryId !== null) {
            $where .= ' AND a.category_id = ?';
            $params[] = $categoryId;
        }
        if ($tagId !== null) {
            $where .= ' AND EXISTS (SELECT 1 FROM article_tag x WHERE x.article_id = a.id AND x.tag_id = ?)';
            $params[] = $tagId;
        }

        $countRows = self::rows('SELECT COUNT(*) AS n FROM articles a WHERE ' . $where, $params);
        $total = (int) ($countRows[0]['n'] ?? 0);

        $limit = self::PER_PAGE;
        $offset = max(0, $page - 1) * $limit;

        $rows = self::rows(
            self::CARD . " WHERE $where ORDER BY a.published_at DESC, a.id DESC LIMIT $limit OFFSET $offset",
            $params
        );

        return [self::decorate($rows), $total];
    }

    /** Artikel terbit lengkap dengan penulis, kategori, gambar, dan tag. */
    public static function article(string $slug): ?array
    {
        $rows = self::rows(
            "SELECT a.*, COALESCE(a.published_at, a.created_at) AS pub_date,
                    u.name AS author, c.name AS category, c.slug AS category_slug,
                    m.path, m.alt_text, m.width, m.height
             FROM articles a
             LEFT JOIN users u ON u.id = a.author_id
             LEFT JOIN article_categories c ON c.id = a.category_id
             LEFT JOIN media m ON m.id = a.featured_image_id
             WHERE a.slug = ? AND " . self::PUBLISHED . "
             LIMIT 1",
            [$slug, self::now()]
        );

        if ($rows === []) {
            return null;
        }

        $row = $rows[0];
        $row['tags'] = self::rows(
            'SELECT t.id, t.name, t.slug
             FROM article_tag x JOIN tags t ON t.id = x.tag_id
             WHERE x.article_id = ?
             ORDER BY t.name ASC',
            [(int) $row['id']]
        );

        return $row;
    }

    /**
     * Artikel terkait: paling banyak berbagi tag, lalu sekategori, lalu terbaru.
     *
     * @param int[] $tagIds
     * @return array<int, array>
     */
    public static function related(int $articleId, ?int $categoryId, array $tagIds, int $limit = 3): array
    {
        $limit = max(1, min(6, $limit));
        $tagIds = array_values(array_unique(array_map('intval', $tagIds)));
        $in = $tagIds === [] ? '0' : implode(',', array_fill(0, count($tagIds), '?'));

        $sql = "SELECT a.id, a.title, a.slug, a.excerpt, a.category_id,
                       COALESCE(a.published_at, a.created_at) AS pub_date,
                       IF(a.excerpt = '', LEFT(a.content, 1500), '') AS body,
                       c.name AS category, c.slug AS category_slug,
                       m.path, m.alt_text, m.width, m.height,
                       (SELECT COUNT(*) FROM article_tag x WHERE x.article_id = a.id AND x.tag_id IN ($in)) AS shared
                FROM articles a
                LEFT JOIN article_categories c ON c.id = a.category_id
                LEFT JOIN media m ON m.id = a.featured_image_id
                WHERE a.id <> ? AND " . self::PUBLISHED . "
                ORDER BY shared DESC, (a.category_id <=> ?) DESC, a.published_at DESC, a.id DESC
                LIMIT $limit";

        $params = [...$tagIds, $articleId, self::now(), $categoryId];

        return self::decorate(self::rows($sql, $params));
    }

    /** Perkiraan menit baca (200 kata per menit), minimal 1. */
    public static function readingMinutes(string $html): int
    {
        $text = trim((string) preg_replace('/<[^>]*>/', ' ', $html));
        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        return max(1, (int) ceil(count($words ?: []) / 200));
    }

    // ---- Internal ----

    private static function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Tambahkan kunci 'summary' (ringkasan atau potongan isi) dan buang kolom body.
     *
     * @param array<int, array> $rows
     * @return array<int, array>
     */
    private static function decorate(array $rows): array
    {
        foreach ($rows as &$row) {
            $excerpt = trim((string) $row['excerpt']);

            if ($excerpt !== '') {
                $row['summary'] = $excerpt;
            } else {
                // Buang tag yang terpotong di ujung potongan isi
                $body = (string) preg_replace('/<[^>]*$/', '', (string) $row['body']);
                $row['summary'] = Seo::excerpt($body, 150);
            }

            unset($row['body']);
        }
        unset($row);

        return $rows;
    }

    /**
     * Jalankan query dan ubah kolom media menjadi kunci 'image'.
     *
     * @param array<int, mixed> $params
     * @return array<int, array>
     */
    private static function rows(string $sql, array $params = []): array
    {
        try {
            $stmt = Database::connection()->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
        } catch (Throwable $e) {
            // Tabel belum ada (migration belum jalan): halaman tampil kosong
            ErrorHandler::log($e);

            return [];
        }

        foreach ($rows as &$row) {
            if (array_key_exists('path', $row)) {
                $row['image'] = SiteContent::image($row['path'], $row['alt_text'], $row['width'], $row['height']);
                unset($row['path'], $row['alt_text'], $row['width'], $row['height']);
            }
        }
        unset($row);

        return $rows;
    }
}