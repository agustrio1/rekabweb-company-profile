<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ErrorHandler;
use Throwable;

/** Halaman custom, FAQ, dan data formulir kontak untuk halaman publik. */
final class PublicPages
{
    /** Halaman terbit berdasarkan slug. */
    public static function page(string $slug): ?array
    {
        $rows = self::rows(
            "SELECT p.id, p.title, p.slug, p.content, p.seo_title, p.seo_description, p.updated_at,
                    m.path, m.alt_text, m.width, m.height
             FROM pages p LEFT JOIN media m ON m.id = p.featured_image_id
             WHERE p.slug = ? AND p.status = 'published' AND (p.published_at IS NULL OR p.published_at <= ?)
             LIMIT 1",
            [$slug, date('Y-m-d H:i:s')]
        );

        return $rows[0] ?? null;
    }

    /** Semua FAQ terbit berurutan. @return array<int, array> */
    public static function faqs(): array
    {
        return self::rows(
            "SELECT id, question, answer, category
             FROM faqs
             WHERE status = 'published'
             ORDER BY sort_order ASC, id ASC
             LIMIT 200"
        );
    }

    /** Pilihan jenis kebutuhan di formulir kontak. @return string[] */
    public static function needTypes(): array
    {
        $types = ['Konsultasi umum', 'Permintaan penawaran'];

        foreach (array_slice(Catalog::services(), 0, 30) as $service) {
            $title = trim((string) $service['title']);
            if ($title !== '' && mb_strlen($title) <= 100) {
                $types[] = $title;
            }
        }

        $types[] = 'Lainnya';

        return array_values(array_unique($types));
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
            $rows = $stmt->fetchAll();
        } catch (Throwable $e) {
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