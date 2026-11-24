<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ErrorHandler;
use DateTimeImmutable;
use Throwable;

final class Pages
{
    public const PER_PAGE = 15;

    /** Slug yang dipakai route sistem dan publik. */
    private const RESERVED = [
        'admin', 'login', 'logout', 'assets', 'uploads', 'storage', 'api', 'sitemap', 'sitemap.xml',
        'robots.txt', 'feed', 'search', 'cari', 'kontak', 'contact', 'layanan', 'services',
        'proyek', 'portofolio', 'portfolio', 'projects', 'artikel', 'blog', 'kategori', 'tag',
        'faq', 'tim', 'team', 'testimoni',
    ];

    public static function slugify(string $text): string
    {
        $slug = strtolower(mb_strtolower($text));
        $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', $slug), '-');
        $slug = trim(substr($slug, 0, 150), '-');

        return $slug !== '' ? $slug : 'halaman';
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM pages WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function count(string $q = '', string $status = ''): int
    {
        [$where, $params] = self::where($q, $status);
        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM pages p ' . $where);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /** @return array<int, array> */
    public static function search(string $q, string $status, int $page): array
    {
        [$where, $params] = self::where($q, $status);
        $limit = self::PER_PAGE;
        $offset = max(0, $page - 1) * $limit;

        $stmt = Database::connection()->prepare(
            "SELECT p.id, p.title, p.slug, p.status, p.published_at, p.updated_at, p.created_by,
                    u.name AS author
             FROM pages p LEFT JOIN users u ON u.id = p.created_by
             $where
             ORDER BY p.updated_at DESC, p.id DESC
             LIMIT $limit OFFSET $offset"
        );
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * @param array<string, mixed> $in      input mentah dari form
     * @param array|null           $current baris halaman saat edit, null saat membuat
     * @return array{0: array<string, mixed>, 1: array<string, string>} [nilai bersih, error per field]
     */
    public static function validate(array $in, ?array $current, bool $canPublish): array
    {
        $v = [];
        $e = [];
        $id = $current !== null ? (int) $current['id'] : null;

        // Judul
        $title = self::line($in['title'] ?? '');
        if ($title === '') {
            $e['title'] = 'Judul wajib diisi.';
        } elseif (mb_strlen($title) > 200) {
            $e['title'] = 'Judul maksimal 200 karakter.';
        }
        $v['title'] = mb_substr($title, 0, 200);

        // Slug
        $slug = strtolower(self::line($in['slug'] ?? ''));
        if ($slug === '') {
            $v['slug'] = self::uniqueSlug(self::slugify($title), $id);
        } else {
            $v['slug'] = $slug;
            if (strlen($slug) > 150 || preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug) !== 1) {
                $e['slug'] = 'Slug hanya boleh huruf kecil, angka, dan tanda minus (maksimal 150 karakter).';
            } elseif (in_array($slug, self::RESERVED, true)) {
                $e['slug'] = 'Slug ini dipakai sistem. Pilih yang lain.';
            } elseif (self::slugTaken($slug, $id)) {
                $e['slug'] = 'Slug sudah dipakai halaman lain.';
            }
        }

        // Konten (disanitasi di server)
        $raw = is_string($in['content'] ?? null) ? (string) $in['content'] : '';
        $content = '';
        if (mb_strlen($raw) > 200000) {
            $e['content'] = 'Konten terlalu panjang.';
        } else {
            try {
                $content = Sanitizer::clean($raw);
            } catch (Throwable $t) {
                ErrorHandler::log($t);
                $e['content'] = 'Konten gagal diproses. Periksa log server.';
            }
        }
        $v['content'] = $content;

        // Status dan tanggal terbit
        if ($canPublish) {
            $status = is_string($in['status'] ?? null) ? (string) $in['status'] : 'draft';
            if (!in_array($status, ['draft', 'published'], true)) {
                $e['status'] = 'Status tidak valid.';
                $status = 'draft';
            }

            $publishedAt = null;
            $rawDate = is_string($in['published_at'] ?? null) ? trim((string) $in['published_at']) : '';
            if ($rawDate !== '') {
                $date = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $rawDate);
                if ($date === false || $date->format('Y-m-d\TH:i') !== $rawDate) {
                    $e['published_at'] = 'Format tanggal tidak valid.';
                } else {
                    $publishedAt = $date->format('Y-m-d H:i:s');
                }
            }
            if ($status === 'published' && $publishedAt === null && !isset($e['published_at'])) {
                $publishedAt = $current['published_at'] ?? date('Y-m-d H:i:s');
            }
        } else {
            // Tanpa izin terbit: status dan tanggal tidak bisa diubah
            $status = (string) ($current['status'] ?? 'draft');
            $publishedAt = $current['published_at'] ?? null;
        }

        if ($status === 'published' && $content === '' && !isset($e['content'])) {
            $e['content'] = 'Konten wajib diisi sebelum diterbitkan.';
        }

        $v['status'] = $status;
        $v['published_at'] = $publishedAt;

        // Featured image
        $img = is_string($in['featured_image'] ?? null) ? trim((string) $in['featured_image']) : '';
        $v['featured_image_id'] = null;
        if ($img !== '') {
            if (preg_match('/^[0-9]{1,10}$/', $img) === 1 && Media::find((int) $img) !== null) {
                $v['featured_image_id'] = (int) $img;
            } else {
                $e['featured_image'] = 'Gambar tidak ditemukan di Media.';
            }
        }

        // SEO
        $seoTitle = self::line($in['seo_title'] ?? '');
        if (mb_strlen($seoTitle) > 70) {
            $e['seo_title'] = 'Judul SEO maksimal 70 karakter.';
        }
        $v['seo_title'] = mb_substr($seoTitle, 0, 70);

        $seoDesc = self::line($in['seo_description'] ?? '');
        if (mb_strlen($seoDesc) > 160) {
            $e['seo_description'] = 'Deskripsi SEO maksimal 160 karakter.';
        }
        $v['seo_description'] = mb_substr($seoDesc, 0, 160);

        return [$v, $e];
    }

    public static function create(array $v, ?int $userId): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO pages (title, slug, content, featured_image_id, status, published_at,
                                seo_title, seo_description, created_by, updated_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $v['title'], $v['slug'], $v['content'], $v['featured_image_id'], $v['status'],
            $v['published_at'], $v['seo_title'], $v['seo_description'], $userId, $userId,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $v, ?int $userId): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE pages SET title = ?, slug = ?, content = ?, featured_image_id = ?, status = ?,
                    published_at = ?, seo_title = ?, seo_description = ?, updated_by = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $v['title'], $v['slug'], $v['content'], $v['featured_image_id'], $v['status'],
            $v['published_at'], $v['seo_title'], $v['seo_description'], $userId, $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM pages WHERE id = ?');
        $stmt->execute([$id]);
    }

    /** @return array{0: string, 1: array<int, string>} */
    private static function where(string $q, string $status): array
    {
        $clauses = [];
        $params = [];

        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
            $clauses[] = '(p.title LIKE ? OR p.slug LIKE ?)';
            array_push($params, $like, $like);
        }
        if (in_array($status, ['draft', 'published'], true)) {
            $clauses[] = 'p.status = ?';
            $params[] = $status;
        }

        return [$clauses === [] ? '' : 'WHERE ' . implode(' AND ', $clauses), $params];
    }

    private static function slugTaken(string $slug, ?int $ignoreId): bool
    {
        if (in_array($slug, self::RESERVED, true)) {
            return true;
        }

        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM pages WHERE slug = ? AND id <> ?');
        $stmt->execute([$slug, $ignoreId ?? 0]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private static function uniqueSlug(string $base, ?int $ignoreId): string
    {
        $slug = $base;
        for ($i = 2; self::slugTaken($slug, $ignoreId) && $i < 1000; $i++) {
            $suffix = '-' . $i;
            $slug = rtrim(substr($base, 0, 150 - strlen($suffix)), '-') . $suffix;
        }

        return $slug;
    }

    /** Teks satu baris tanpa karakter kontrol. */
    private static function line(mixed $raw): string
    {
        if (!is_string($raw) || !mb_check_encoding($raw, 'UTF-8')) {
            return '';
        }

        $s = trim(str_replace(["\r\n", "\r", "\n"], ' ', $raw));

        return (string) preg_replace('/\p{Cc}+/u', '', $s);
    }
}