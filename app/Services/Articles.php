<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ErrorHandler;
use DateTimeImmutable;
use PDO;
use Throwable;

/** Artikel blog (tabel articles dan article_tag). */
final class Articles
{
    public const PER_PAGE = 15;
    public const MAX_TAGS = 10;

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM articles WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function count(string $q = '', string $status = ''): int
    {
        [$where, $params] = self::where($q, $status);
        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM articles a ' . $where);
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
            "SELECT a.id, a.title, a.slug, a.status, a.published_at, a.updated_at, a.created_by,
                    u.name AS author, c.name AS category
             FROM articles a
             LEFT JOIN users u ON u.id = a.author_id
             LEFT JOIN article_categories c ON c.id = a.category_id
             $where
             ORDER BY a.updated_at DESC, a.id DESC
             LIMIT $limit OFFSET $offset"
        );
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /** @return string[] nama tag artikel */
    public static function tagNames(int $articleId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT t.name FROM article_tag x JOIN tags t ON t.id = x.tag_id
             WHERE x.article_id = ? ORDER BY t.name ASC'
        );
        $stmt->execute([$articleId]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Pilihan penulis: pengguna aktif, ditambah penulis saat ini walau sudah nonaktif.
     *
     * @return array<int, string> id => nama
     */
    public static function authors(?int $currentAuthorId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, name FROM users WHERE is_active = 1 OR id = ? ORDER BY name ASC'
        );
        $stmt->execute([$currentAuthorId ?? 0]);

        $out = [];
        foreach ($stmt->fetchAll() as $row) {
            $out[(int) $row['id']] = (string) $row['name'];
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $in      input mentah dari form
     * @param array|null           $current baris artikel saat edit, null saat membuat
     * @param array{can_publish: bool, can_author: bool, user_id: ?int} $ctx
     * @return array{0: array<string, mixed>, 1: array<string, string>} [nilai bersih, error per field]
     */
    public static function validate(array $in, ?array $current, array $ctx): array
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
            $base = $title !== '' ? Pages::slugify($title) : 'artikel';
            $v['slug'] = self::uniqueSlug($base, $id);
        } else {
            $v['slug'] = $slug;
            if (strlen($slug) > 150 || preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug) !== 1) {
                $e['slug'] = 'Slug hanya boleh huruf kecil, angka, dan tanda minus (maksimal 150 karakter).';
            } elseif (self::slugTaken($slug, $id)) {
                $e['slug'] = 'Slug sudah dipakai artikel lain.';
            }
        }

        // Kategori
        $cat = is_string($in['category_id'] ?? null) ? trim((string) $in['category_id']) : '';
        $v['category_id'] = null;
        if ($cat !== '') {
            if (preg_match('/^[0-9]{1,10}$/', $cat) === 1 && ArticleTaxonomy::categoryExists((int) $cat)) {
                $v['category_id'] = (int) $cat;
            } else {
                $e['category_id'] = 'Kategori tidak ditemukan.';
            }
        }

        // Penulis
        $currentAuthor = $current !== null && $current['author_id'] !== null ? (int) $current['author_id'] : null;
        $authorId = $current !== null ? $currentAuthor : $ctx['user_id'];
        if ($ctx['can_author']) {
            $rawAuthor = is_string($in['author_id'] ?? null) ? trim((string) $in['author_id']) : '';
            if ($rawAuthor !== '') {
                if (preg_match('/^[0-9]{1,10}$/', $rawAuthor) === 1 && self::authorAllowed((int) $rawAuthor, $currentAuthor)) {
                    $authorId = (int) $rawAuthor;
                } else {
                    $e['author_id'] = 'Penulis tidak ditemukan.';
                }
            }
        }
        $v['author_id'] = $authorId;

        // Ringkasan
        $excerpt = self::line($in['excerpt'] ?? '');
        if (mb_strlen($excerpt) > 300) {
            $e['excerpt'] = 'Ringkasan maksimal 300 karakter.';
        }
        $v['excerpt'] = mb_substr($excerpt, 0, 300);

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

        // Tag
        $v['tags'] = self::parseTags($in['tags'] ?? '', $e);

        // Status dan tanggal terbit
        if ($ctx['can_publish']) {
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

        // Gambar utama
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
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO articles (category_id, author_id, title, slug, excerpt, content, featured_image_id,
                                       status, published_at, seo_title, seo_description, created_by, updated_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $v['category_id'], $v['author_id'], $v['title'], $v['slug'], $v['excerpt'], $v['content'],
                $v['featured_image_id'], $v['status'], $v['published_at'], $v['seo_title'],
                $v['seo_description'], $userId, $userId,
            ]);

            $id = (int) $pdo->lastInsertId();
            self::saveTags($pdo, $id, $v['tags']);
            $pdo->commit();

            return $id;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function update(int $id, array $v, ?int $userId): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'UPDATE articles SET category_id = ?, author_id = ?, title = ?, slug = ?, excerpt = ?,
                        content = ?, featured_image_id = ?, status = ?, published_at = ?,
                        seo_title = ?, seo_description = ?, updated_by = ?
                 WHERE id = ?'
            );
            $stmt->execute([
                $v['category_id'], $v['author_id'], $v['title'], $v['slug'], $v['excerpt'], $v['content'],
                $v['featured_image_id'], $v['status'], $v['published_at'], $v['seo_title'],
                $v['seo_description'], $userId, $id,
            ]);

            self::saveTags($pdo, $id, $v['tags']);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function delete(int $id): void
    {
        // article_tag ikut terhapus (ON DELETE CASCADE)
        $stmt = Database::connection()->prepare('DELETE FROM articles WHERE id = ?');
        $stmt->execute([$id]);
    }

    /** @param string[] $names */
    private static function saveTags(PDO $pdo, int $articleId, array $names): void
    {
        $ids = ArticleTaxonomy::tagIds($names);

        $pdo->prepare('DELETE FROM article_tag WHERE article_id = ?')->execute([$articleId]);

        $insert = $pdo->prepare('INSERT INTO article_tag (article_id, tag_id) VALUES (?, ?)');
        foreach ($ids as $tagId) {
            $insert->execute([$articleId, $tagId]);
        }
    }

    /**
     * @param array<string, string> $errors
     * @return string[]
     */
    private static function parseTags(mixed $raw, array &$errors): array
    {
        if (!is_string($raw) || !mb_check_encoding($raw, 'UTF-8')) {
            return [];
        }

        $names = [];
        $seen = [];

        foreach (explode(',', $raw) as $part) {
            $name = (string) preg_replace('/\p{Cc}+/u', ' ', $part);
            $name = trim((string) preg_replace('/\s+/u', ' ', $name));
            if ($name === '') {
                continue;
            }
            if (mb_strlen($name) > 40) {
                $errors['tags'] = 'Tiap tag maksimal 40 karakter.';
                continue;
            }

            $key = mb_strtolower($name);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $names[] = $name;
        }

        if (count($names) > self::MAX_TAGS) {
            $errors['tags'] = 'Maksimal ' . self::MAX_TAGS . ' tag per artikel.';
            $names = array_slice($names, 0, self::MAX_TAGS);
        }

        return $names;
    }

    private static function authorAllowed(int $userId, ?int $currentAuthor): bool
    {
        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) FROM users WHERE id = ? AND (is_active = 1 OR id = ?)'
        );
        $stmt->execute([$userId, $currentAuthor ?? 0]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /** @return array{0: string, 1: array<int, string>} */
    private static function where(string $q, string $status): array
    {
        $clauses = [];
        $params = [];

        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
            $clauses[] = '(a.title LIKE ? OR a.slug LIKE ?)';
            array_push($params, $like, $like);
        }
        if (in_array($status, ['draft', 'published'], true)) {
            $clauses[] = 'a.status = ?';
            $params[] = $status;
        }

        return [$clauses === [] ? '' : 'WHERE ' . implode(' AND ', $clauses), $params];
    }

    private static function slugTaken(string $slug, ?int $ignoreId): bool
    {
        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM articles WHERE slug = ? AND id <> ?');
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