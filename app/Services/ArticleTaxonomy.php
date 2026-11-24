<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;

/** Kategori dan tag artikel. Nama tabel hanya berasal dari konstanta TYPES di bawah. */
final class ArticleTaxonomy
{
    private const TYPES = [
        'categories' => [
            'table' => 'article_categories',
            'label' => 'Kategori artikel',
            'noun' => 'kategori',
            'singular' => 'Kategori',
            'description' => 'Kelompokkan artikel, misalnya "Tips", "Berita perusahaan", atau "Panduan". Satu artikel punya satu kategori.',
            'delete_note' => 'Artikel di dalamnya tidak ikut terhapus, tapi menjadi tanpa kategori.',
            'name_max' => 80,
            'slug_max' => 100,
            'max' => 50,
            'count_sql' => 'SELECT COUNT(*) FROM articles a WHERE a.category_id = t.id',
        ],
        'tags' => [
            'table' => 'tags',
            'label' => 'Tag artikel',
            'noun' => 'tag',
            'singular' => 'Tag',
            'description' => 'Kata kunci pendukung. Satu artikel bisa punya beberapa tag. Tag juga dibuat otomatis dari form artikel.',
            'delete_note' => 'Artikel tidak ikut terhapus, tag hanya dilepas dari artikel.',
            'name_max' => 40,
            'slug_max' => 60,
            'max' => 200,
            'count_sql' => 'SELECT COUNT(*) FROM article_tag x WHERE x.tag_id = t.id',
        ],
    ];

    public static function isType(string $type): bool
    {
        return isset(self::TYPES[$type]);
    }

    /** @return array<string, mixed> */
    public static function meta(string $type): array
    {
        return self::TYPES[$type];
    }

    /** Semua item beserta jumlah artikelnya. @return array<int, array> */
    public static function all(string $type): array
    {
        $m = self::TYPES[$type];

        return Database::connection()->query(
            'SELECT t.id, t.name, t.slug, (' . $m['count_sql'] . ') AS item_count
             FROM ' . $m['table'] . ' t
             ORDER BY t.name ASC'
        )->fetchAll();
    }

    /** @return array<int, string> id => nama kategori, untuk dropdown */
    public static function categoryOptions(): array
    {
        $out = [];
        $rows = Database::connection()
            ->query('SELECT id, name FROM article_categories ORDER BY name ASC')
            ->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $out[(int) $row['id']] = (string) $row['name'];
        }

        return $out;
    }

    /** Tag yang paling banyak dipakai. @return string[] */
    public static function popularTags(int $limit = 20): array
    {
        $limit = max(1, min(50, $limit));

        return Database::connection()->query(
            "SELECT t.name
             FROM tags t LEFT JOIN article_tag x ON x.tag_id = t.id
             GROUP BY t.id, t.name
             ORDER BY COUNT(x.article_id) DESC, t.name ASC
             LIMIT $limit"
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function categoryExists(int $id): bool
    {
        return self::find('categories', $id) !== null;
    }

    public static function find(string $type, int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM ' . self::TYPES[$type]['table'] . ' WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function count(string $type): int
    {
        return (int) Database::connection()->query('SELECT COUNT(*) FROM ' . self::TYPES[$type]['table'])->fetchColumn();
    }

    /** @return array{0: string, 1: ?string} [nama bersih, pesan error] */
    public static function validateName(string $type, mixed $raw, ?int $ignoreId): array
    {
        $m = self::TYPES[$type];
        $name = self::cleanName($raw);

        if ($name === '') {
            return ['', 'Nama ' . $m['noun'] . ' wajib diisi.'];
        }
        if (mb_strlen($name) > $m['name_max']) {
            return [mb_substr($name, 0, $m['name_max']), 'Nama ' . $m['noun'] . ' maksimal ' . $m['name_max'] . ' karakter.'];
        }

        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) FROM ' . $m['table'] . ' WHERE name = ? AND id <> ?'
        );
        $stmt->execute([$name, $ignoreId ?? 0]);
        if ((int) $stmt->fetchColumn() > 0) {
            return [$name, $m['singular'] . ' "' . $name . '" sudah ada.'];
        }

        return [$name, null];
    }

    public static function create(string $type, string $name): int
    {
        $m = self::TYPES[$type];
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO ' . $m['table'] . ' (name, slug) VALUES (?, ?)');
        $stmt->execute([$name, self::uniqueSlug($m['table'], Pages::slugify($name), (int) $m['slug_max'])]);

        return (int) $pdo->lastInsertId();
    }

    /** Slug sengaja tidak berubah saat diganti nama, supaya URL tetap stabil. */
    public static function rename(string $type, int $id, string $name): void
    {
        $stmt = Database::connection()->prepare('UPDATE ' . self::TYPES[$type]['table'] . ' SET name = ? WHERE id = ?');
        $stmt->execute([$name, $id]);
    }

    public static function delete(string $type, int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM ' . self::TYPES[$type]['table'] . ' WHERE id = ?');
        $stmt->execute([$id]);
    }

    /**
     * ID tag untuk daftar nama. Tag yang belum ada dibuat.
     *
     * @param string[] $names nama yang sudah dibersihkan
     * @return int[]
     */
    public static function tagIds(array $names): array
    {
        $pdo = Database::connection();
        $find = $pdo->prepare('SELECT id FROM tags WHERE name = ? LIMIT 1');
        $insert = $pdo->prepare('INSERT INTO tags (name, slug) VALUES (?, ?)');
        $ids = [];

        foreach ($names as $name) {
            $find->execute([$name]);
            $id = $find->fetchColumn();

            if ($id === false) {
                $insert->execute([$name, self::uniqueSlug('tags', Pages::slugify($name), 60)]);
                $id = $pdo->lastInsertId();
            }

            $ids[] = (int) $id;
        }

        return array_values(array_unique($ids));
    }

    private static function cleanName(mixed $raw): string
    {
        if (!is_string($raw) || !mb_check_encoding($raw, 'UTF-8')) {
            return '';
        }

        $name = trim((string) preg_replace('/\p{Cc}+/u', ' ', $raw));

        return trim((string) preg_replace('/\s+/u', ' ', $name));
    }

    private static function uniqueSlug(string $table, string $base, int $max): string
    {
        $check = Database::connection()->prepare('SELECT COUNT(*) FROM ' . $table . ' WHERE slug = ?');

        $slug = rtrim(substr($base, 0, $max), '-');
        for ($i = 2; $i < 1000; $i++) {
            $check->execute([$slug]);
            if ((int) $check->fetchColumn() === 0) {
                break;
            }
            $suffix = '-' . $i;
            $slug = rtrim(substr($base, 0, $max - strlen($suffix)), '-') . $suffix;
        }

        return $slug;
    }
}