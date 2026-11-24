<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ErrorHandler;
use Throwable;

/** Layanan dan proyek terbit untuk halaman publik (daftar dan detail). */
final class Catalog
{
    public const PROJECTS_PER_PAGE = 12;

    private const PUBLISHED_SERVICE = "s.status = 'published' AND (s.published_at IS NULL OR s.published_at <= ?)";
    private const PUBLISHED_PROJECT = "p.status = 'published' AND (p.published_at IS NULL OR p.published_at <= ?)";

    private const PROJECT_CARD = "SELECT p.id, p.title, p.slug, p.summary, p.location, p.completed_year, p.category_id,
                    c.name AS category, c.slug AS category_slug, m.path, m.alt_text, m.width, m.height
             FROM projects p
             LEFT JOIN project_categories c ON c.id = p.category_id
             LEFT JOIN media m ON m.id = p.featured_image_id";

    // ---- Layanan ----

    /** @return array<int, array> */
    public static function services(): array
    {
        return self::rows(
            "SELECT s.id, s.title, s.slug, s.summary, m.path, m.alt_text, m.width, m.height
             FROM services s LEFT JOIN media m ON m.id = s.featured_image_id
             WHERE " . self::PUBLISHED_SERVICE . "
             ORDER BY s.sort_order ASC, s.id ASC
             LIMIT 100",
            [self::now()]
        );
    }

    /** Layanan terbit lengkap dengan manfaat, proses, dan FAQ. */
    public static function service(string $slug): ?array
    {
        $rows = self::rows(
            "SELECT s.*, m.path, m.alt_text, m.width, m.height
             FROM services s LEFT JOIN media m ON m.id = s.featured_image_id
             WHERE s.slug = ? AND " . self::PUBLISHED_SERVICE . "
             LIMIT 1",
            [$slug, self::now()]
        );

        if ($rows === []) {
            return null;
        }

        $row = $rows[0];
        $row['details'] = Offerings::details($row);

        return $row;
    }

    /** Layanan lain untuk rekomendasi di halaman detail. @return array<int, array> */
    public static function otherServices(int $excludeId, int $limit = 3): array
    {
        $limit = max(1, min(6, $limit));

        return self::rows(
            "SELECT s.id, s.title, s.slug, s.summary, m.path, m.alt_text, m.width, m.height
             FROM services s LEFT JOIN media m ON m.id = s.featured_image_id
             WHERE s.id <> ? AND " . self::PUBLISHED_SERVICE . "
             ORDER BY s.is_featured DESC, s.sort_order ASC, s.id ASC
             LIMIT $limit",
            [$excludeId, self::now()]
        );
    }

    // ---- Proyek ----

    /** Kategori yang punya minimal satu proyek terbit. @return array<int, array> */
    public static function projectCategories(): array
    {
        return self::rows(
            "SELECT c.id, c.name, c.slug, COUNT(p.id) AS n
             FROM project_categories c
             JOIN projects p ON p.category_id = c.id AND " . self::PUBLISHED_PROJECT . "
             GROUP BY c.id, c.name, c.slug
             ORDER BY c.name ASC",
            [self::now()]
        );
    }

    public static function projectCategory(string $slug): ?array
    {
        $rows = self::rows('SELECT id, name, slug FROM project_categories WHERE slug = ? LIMIT 1', [$slug]);

        return $rows[0] ?? null;
    }

    /**
     * @return array{0: array<int, array>, 1: int} [baris, total proyek]
     */
    public static function projects(?int $categoryId, int $page): array
    {
        $where = self::PUBLISHED_PROJECT;
        $params = [self::now()];

        if ($categoryId !== null) {
            $where .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }

        $countRows = self::rows('SELECT COUNT(*) AS n FROM projects p WHERE ' . $where, $params);
        $total = (int) ($countRows[0]['n'] ?? 0);

        $limit = self::PROJECTS_PER_PAGE;
        $offset = max(0, $page - 1) * $limit;

        $rows = self::rows(
            self::PROJECT_CARD . " WHERE $where ORDER BY p.sort_order ASC, p.id ASC LIMIT $limit OFFSET $offset",
            $params
        );

        return [$rows, $total];
    }

    /** Proyek terbit lengkap dengan galeri. */
    public static function project(string $slug): ?array
    {
        $rows = self::rows(
            "SELECT p.*, c.name AS category, c.slug AS category_slug, m.path, m.alt_text, m.width, m.height
             FROM projects p
             LEFT JOIN project_categories c ON c.id = p.category_id
             LEFT JOIN media m ON m.id = p.featured_image_id
             WHERE p.slug = ? AND " . self::PUBLISHED_PROJECT . "
             LIMIT 1",
            [$slug, self::now()]
        );

        if ($rows === []) {
            return null;
        }

        $row = $rows[0];

        $gallery = self::rows(
            'SELECT m.path, m.alt_text, m.width, m.height
             FROM project_images i JOIN media m ON m.id = i.media_id
             WHERE i.project_id = ?
             ORDER BY i.sort_order ASC',
            [(int) $row['id']]
        );
        $row['gallery'] = array_values(array_filter(array_map(
            static fn (array $g): ?array => $g['image'] ?? null,
            $gallery
        )));

        return $row;
    }

    /** Proyek lain, yang sekategori didahulukan. @return array<int, array> */
    public static function otherProjects(int $excludeId, ?int $categoryId, int $limit = 3): array
    {
        $limit = max(1, min(6, $limit));

        return self::rows(
            self::PROJECT_CARD . " WHERE p.id <> ? AND " . self::PUBLISHED_PROJECT . "
             ORDER BY (p.category_id <=> ?) DESC, p.sort_order ASC, p.id ASC
             LIMIT $limit",
            [$excludeId, self::now(), $categoryId]
        );
    }

    // ---- Internal ----

    private static function now(): string
    {
        return date('Y-m-d H:i:s');
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