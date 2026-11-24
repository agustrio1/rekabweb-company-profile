<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;

/** Kategori proyek portofolio. */
final class ProjectCategories
{
    public const MAX = 50;

    /** Semua kategori beserta jumlah proyeknya. @return array<int, array> */
    public static function all(): array
    {
        return Database::connection()->query(
            'SELECT c.id, c.name, c.slug,
                    (SELECT COUNT(*) FROM projects p WHERE p.category_id = c.id) AS project_count
             FROM project_categories c
             ORDER BY c.name ASC'
        )->fetchAll();
    }

    /** @return array<int, string> id => nama, untuk dropdown */
    public static function options(): array
    {
        $out = [];
        $rows = Database::connection()
            ->query('SELECT id, name FROM project_categories ORDER BY name ASC')
            ->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $out[(int) $row['id']] = (string) $row['name'];
        }

        return $out;
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM project_categories WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function count(): int
    {
        return (int) Database::connection()->query('SELECT COUNT(*) FROM project_categories')->fetchColumn();
    }

    /** @return array{0: string, 1: ?string} [nama bersih, pesan error] */
    public static function validateName(mixed $raw, ?int $ignoreId): array
    {
        $name = '';
        if (is_string($raw) && mb_check_encoding($raw, 'UTF-8')) {
            $name = trim((string) preg_replace('/\p{Cc}+/u', ' ', $raw));
            $name = (string) preg_replace('/\s+/u', ' ', $name);
        }

        if ($name === '') {
            return ['', 'Nama kategori wajib diisi.'];
        }
        if (mb_strlen($name) > 80) {
            return [mb_substr($name, 0, 80), 'Nama kategori maksimal 80 karakter.'];
        }

        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) FROM project_categories WHERE name = ? AND id <> ?'
        );
        $stmt->execute([$name, $ignoreId ?? 0]);
        if ((int) $stmt->fetchColumn() > 0) {
            return [$name, 'Kategori "' . $name . '" sudah ada.'];
        }

        return [$name, null];
    }

    public static function create(string $name): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO project_categories (name, slug) VALUES (?, ?)');
        $stmt->execute([$name, self::uniqueSlug(Pages::slugify($name))]);

        return (int) $pdo->lastInsertId();
    }

    /** Slug sengaja tidak berubah saat diganti nama, supaya URL tetap stabil. */
    public static function rename(int $id, string $name): void
    {
        $stmt = Database::connection()->prepare('UPDATE project_categories SET name = ? WHERE id = ?');
        $stmt->execute([$name, $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM project_categories WHERE id = ?');
        $stmt->execute([$id]);
    }

    private static function uniqueSlug(string $base): string
    {
        $pdo = Database::connection();
        $check = $pdo->prepare('SELECT COUNT(*) FROM project_categories WHERE slug = ?');

        $slug = substr($base, 0, 100);
        for ($i = 2; $i < 1000; $i++) {
            $check->execute([$slug]);
            if ((int) $check->fetchColumn() === 0) {
                break;
            }
            $suffix = '-' . $i;
            $slug = rtrim(substr($base, 0, 100 - strlen($suffix)), '-') . $suffix;
        }

        return $slug;
    }
}