<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ErrorHandler;
use Throwable;

/** Konten terbit untuk halaman publik. Semua query hanya mengambil yang sudah tayang. */
final class SiteContent
{
    private const MONTHS = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    /** @return array{url: string, alt: string, width: int, height: int}|null */
    public static function media(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        try {
            $row = Media::find($id);
        } catch (Throwable $e) {
            ErrorHandler::log($e);

            return null;
        }

        return $row === null
            ? null
            : self::image((string) $row['path'], (string) $row['alt_text'], $row['width'], $row['height']);
    }

    /** @return array{url: string, alt: string, width: int, height: int}|null */
    public static function image(?string $path, ?string $alt, mixed $width, mixed $height): ?array
    {
        if ($path === null || $path === '') {
            return null;
        }

        return [
            'url' => Media::url($path),
            'alt' => (string) $alt,
            'width' => (int) $width,
            'height' => (int) $height,
        ];
    }

    /** "12 Maret 2026" dari string tanggal database. */
    public static function dateId(?string $datetime): string
    {
        $time = $datetime !== null ? strtotime($datetime) : false;
        if ($time === false) {
            return '';
        }

        return (int) date('j', $time) . ' ' . self::MONTHS[(int) date('n', $time)] . ' ' . date('Y', $time);
    }

    /** Layanan terbit, unggulan lebih dulu. @return array<int, array> */
    public static function services(int $limit): array
    {
        $limit = self::limit($limit);

        return self::rows(
            "SELECT s.id, s.title, s.slug, s.summary, m.path, m.alt_text, m.width, m.height
             FROM services s LEFT JOIN media m ON m.id = s.featured_image_id
             WHERE s.status = 'published' AND (s.published_at IS NULL OR s.published_at <= ?)
             ORDER BY s.is_featured DESC, s.sort_order ASC, s.id ASC
             LIMIT $limit",
            [self::now()]
        );
    }

    /** Proyek terbit, unggulan lebih dulu. @return array<int, array> */
    public static function projects(int $limit): array
    {
        $limit = self::limit($limit);

        return self::rows(
            "SELECT p.id, p.title, p.slug, p.summary, p.location, p.completed_year,
                    c.name AS category, m.path, m.alt_text, m.width, m.height
             FROM projects p
             LEFT JOIN project_categories c ON c.id = p.category_id
             LEFT JOIN media m ON m.id = p.featured_image_id
             WHERE p.status = 'published' AND (p.published_at IS NULL OR p.published_at <= ?)
             ORDER BY p.is_featured DESC, p.sort_order ASC, p.id ASC
             LIMIT $limit",
            [self::now()]
        );
    }

    /** @return array<int, array> */
    public static function testimonials(int $limit): array
    {
        $limit = self::limit($limit);

        return self::rows(
            "SELECT t.id, t.name, t.job_title, t.company, t.quote, m.path, m.alt_text, m.width, m.height
             FROM testimonials t LEFT JOIN media m ON m.id = t.photo_id
             WHERE t.status = 'published'
             ORDER BY t.sort_order ASC, t.id ASC
             LIMIT $limit"
        );
    }

    /** @return array<int, array> */
    public static function faqs(int $limit): array
    {
        $limit = self::limit($limit);

        return self::rows(
            "SELECT id, question, answer, category
             FROM faqs
             WHERE status = 'published'
             ORDER BY sort_order ASC, id ASC
             LIMIT $limit"
        );
    }

    /** Artikel terbit terbaru. @return array<int, array> */
    public static function articles(int $limit): array
    {
        $limit = self::limit($limit);

        $rows = self::rows(
            "SELECT a.id, a.title, a.slug, a.excerpt, a.content, a.published_at,
                    c.name AS category, m.path, m.alt_text, m.width, m.height
             FROM articles a
             LEFT JOIN article_categories c ON c.id = a.category_id
             LEFT JOIN media m ON m.id = a.featured_image_id
             WHERE a.status = 'published' AND (a.published_at IS NULL OR a.published_at <= ?)
             ORDER BY a.published_at DESC, a.id DESC
             LIMIT $limit",
            [self::now()]
        );

        foreach ($rows as &$row) {
            $excerpt = trim((string) $row['excerpt']);
            $row['summary'] = $excerpt !== '' ? $excerpt : Seo::excerpt((string) $row['content'], 140);
            unset($row['content']);
        }
        unset($row);

        return $rows;
    }

    private static function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    private static function limit(int $limit): int
    {
        return max(1, min(12, $limit));
    }

    /**
     * Jalankan query dan tambahkan kunci 'image' bila ada kolom path media.
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
            // Tabel belum ada (migration belum jalan): section dilewati
            ErrorHandler::log($e);

            return [];
        }

        foreach ($rows as &$row) {
            if (array_key_exists('path', $row)) {
                $row['image'] = self::image($row['path'], $row['alt_text'], $row['width'], $row['height']);
                unset($row['path'], $row['alt_text'], $row['width'], $row['height']);
            }
        }
        unset($row);

        return $rows;
    }
}