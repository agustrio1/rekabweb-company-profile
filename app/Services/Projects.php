<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ErrorHandler;
use DateTimeImmutable;
use PDO;
use Throwable;

/** Proyek portofolio (tabel projects dan project_images). */
final class Projects
{
    public const MAX_GALLERY = 12;
    private const LIST_LIMIT = 100;

    /** Field teks proyek (divalidasi lewat Settings::validateFields). */
    public static function textFields(): array
    {
        return [
            'client_name' => ['label' => 'Nama klien', 'type' => 'text', 'max' => 100],
            'location' => ['label' => 'Lokasi', 'type' => 'text', 'max' => 100, 'placeholder' => 'Contoh: Bandung, Jawa Barat'],
            'challenge' => [
                'label' => 'Tantangan', 'type' => 'textarea', 'max' => 2000, 'wide' => true,
                'hint' => 'Masalah atau kebutuhan klien di awal proyek.',
            ],
            'solution' => [
                'label' => 'Solusi', 'type' => 'textarea', 'max' => 2000, 'wide' => true,
                'hint' => 'Pendekatan yang kamu ambil.',
            ],
            'results' => [
                'label' => 'Hasil pekerjaan', 'type' => 'textarea', 'max' => 2000, 'wide' => true,
                'hint' => 'Tulis hanya hasil yang benar-benar tercapai dan bisa dibuktikan. Jangan mengarang angka.',
            ],
        ];
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM projects WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /** Semua proyek berurutan (untuk daftar admin). @return array<int, array> */
    public static function all(): array
    {
        $limit = self::LIST_LIMIT;

        return Database::connection()->query(
            "SELECT p.id, p.title, p.slug, p.status, p.published_at, p.is_featured, p.created_by,
                    c.name AS category, u.name AS author
             FROM projects p
             LEFT JOIN project_categories c ON c.id = p.category_id
             LEFT JOIN users u ON u.id = p.created_by
             ORDER BY p.sort_order ASC, p.id ASC
             LIMIT $limit"
        )->fetchAll();
    }

    /** ID gambar galeri proyek, berurutan. @return int[] */
    public static function galleryIds(int $projectId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT media_id FROM project_images WHERE project_id = ? ORDER BY sort_order ASC'
        );
        $stmt->execute([$projectId]);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * Data tampil untuk daftar ID gambar (urutan dipertahankan, ID yang tidak ada dilewati).
     *
     * @param int[] $ids
     * @return array<int, array{id: int, url: string}>
     */
    public static function galleryItems(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if ($ids === []) {
            return [];
        }

        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::connection()->prepare("SELECT id, path FROM media WHERE id IN ($in)");
        $stmt->execute($ids);

        $byId = [];
        foreach ($stmt->fetchAll() as $row) {
            $byId[(int) $row['id']] = (string) $row['path'];
        }

        $out = [];
        foreach ($ids as $id) {
            if (isset($byId[$id])) {
                $out[] = ['id' => $id, 'url' => Media::url($byId[$id])];
            }
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $in      input mentah dari form
     * @param array|null           $current baris proyek saat edit, null saat membuat
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
        } elseif (mb_strlen($title) > 150) {
            $e['title'] = 'Judul maksimal 150 karakter.';
        }
        $v['title'] = mb_substr($title, 0, 150);

        // Slug
        $slug = strtolower(self::line($in['slug'] ?? ''));
        if ($slug === '') {
            $base = $title !== '' ? Pages::slugify($title) : 'proyek';
            $v['slug'] = self::uniqueSlug($base, $id);
        } else {
            $v['slug'] = $slug;
            if (strlen($slug) > 150 || preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug) !== 1) {
                $e['slug'] = 'Slug hanya boleh huruf kecil, angka, dan tanda minus (maksimal 150 karakter).';
            } elseif (self::slugTaken($slug, $id)) {
                $e['slug'] = 'Slug sudah dipakai proyek lain.';
            }
        }

        // Kategori
        $cat = is_string($in['category_id'] ?? null) ? trim((string) $in['category_id']) : '';
        $v['category_id'] = null;
        if ($cat !== '') {
            if (preg_match('/^[0-9]{1,10}$/', $cat) === 1 && ProjectCategories::find((int) $cat) !== null) {
                $v['category_id'] = (int) $cat;
            } else {
                $e['category_id'] = 'Kategori tidak ditemukan.';
            }
        }

        // Ringkasan
        $summary = self::line($in['summary'] ?? '');
        if (mb_strlen($summary) > 300) {
            $e['summary'] = 'Ringkasan maksimal 300 karakter.';
        }
        $v['summary'] = mb_substr($summary, 0, 300);

        // Tahun selesai
        $year = is_string($in['completed_year'] ?? null) ? trim((string) $in['completed_year']) : '';
        $v['completed_year'] = null;
        if ($year !== '') {
            $max = (int) date('Y') + 1;
            if (preg_match('/^[0-9]{4}$/', $year) === 1 && (int) $year >= 1990 && (int) $year <= $max) {
                $v['completed_year'] = (int) $year;
            } else {
                $e['completed_year'] = 'Tahun harus antara 1990 dan ' . $max . '.';
            }
        }

        // Klien, lokasi, tantangan, solusi, hasil
        [$text, $textErrors] = Settings::validateFields(self::textFields(), $in);
        $v += $text;
        $e += $textErrors;
        $v['show_client'] = ($in['show_client'] ?? '') === '1' ? 1 : 0;

        // Deskripsi (disanitasi di server)
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

        if ($status === 'published' && $summary === '' && !isset($e['summary'])) {
            $e['summary'] = 'Ringkasan wajib diisi sebelum diterbitkan.';
        }

        $v['status'] = $status;
        $v['published_at'] = $publishedAt;
        $v['is_featured'] = ($in['is_featured'] ?? '') === '1' ? 1 : 0;

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

        // Galeri
        $ids = [];
        foreach (is_array($in['gallery'] ?? null) ? $in['gallery'] : [] as $g) {
            if (is_string($g) && preg_match('/^[0-9]{1,10}$/', $g) === 1 && !in_array((int) $g, $ids, true)) {
                $ids[] = (int) $g;
            }
        }
        if (count($ids) > self::MAX_GALLERY) {
            $e['gallery'] = 'Galeri maksimal ' . self::MAX_GALLERY . ' gambar.';
            $ids = array_slice($ids, 0, self::MAX_GALLERY);
        }
        if ($ids !== [] && count(self::galleryItems($ids)) !== count($ids)) {
            $e['gallery'] = 'Ada gambar galeri yang tidak ditemukan di Media.';
        }
        $v['gallery'] = $ids;

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
        $next = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM projects')->fetchColumn();

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO projects (category_id, title, slug, summary, client_name, show_client, location,
                                       completed_year, content, challenge, solution, results, featured_image_id,
                                       is_featured, status, published_at, sort_order, seo_title, seo_description,
                                       created_by, updated_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $v['category_id'], $v['title'], $v['slug'], $v['summary'], $v['client_name'], $v['show_client'],
                $v['location'], $v['completed_year'], $v['content'], $v['challenge'], $v['solution'], $v['results'],
                $v['featured_image_id'], $v['is_featured'], $v['status'], $v['published_at'], $next,
                $v['seo_title'], $v['seo_description'], $userId, $userId,
            ]);

            $id = (int) $pdo->lastInsertId();
            self::saveGallery($pdo, $id, $v['gallery']);
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
                'UPDATE projects SET category_id = ?, title = ?, slug = ?, summary = ?, client_name = ?,
                        show_client = ?, location = ?, completed_year = ?, content = ?, challenge = ?,
                        solution = ?, results = ?, featured_image_id = ?, is_featured = ?, status = ?,
                        published_at = ?, seo_title = ?, seo_description = ?, updated_by = ?
                 WHERE id = ?'
            );
            $stmt->execute([
                $v['category_id'], $v['title'], $v['slug'], $v['summary'], $v['client_name'], $v['show_client'],
                $v['location'], $v['completed_year'], $v['content'], $v['challenge'], $v['solution'], $v['results'],
                $v['featured_image_id'], $v['is_featured'], $v['status'], $v['published_at'],
                $v['seo_title'], $v['seo_description'], $userId, $id,
            ]);

            self::saveGallery($pdo, $id, $v['gallery']);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function delete(int $id): void
    {
        // project_images ikut terhapus (ON DELETE CASCADE)
        $stmt = Database::connection()->prepare('DELETE FROM projects WHERE id = ?');
        $stmt->execute([$id]);
    }

    /** Geser satu posisi ke atas atau ke bawah; seluruh urutan dinomori ulang. */
    public static function move(int $id, string $direction): void
    {
        $pdo = Database::connection();
        $ids = array_map(
            'intval',
            $pdo->query('SELECT id FROM projects ORDER BY sort_order ASC, id ASC')->fetchAll(PDO::FETCH_COLUMN)
        );

        $i = array_search($id, $ids, true);
        if ($i === false) {
            return;
        }

        $j = $direction === 'up' ? $i - 1 : $i + 1;
        if ($j < 0 || $j >= count($ids)) {
            return;
        }

        [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];

        // updated_at dipertahankan: mengubah urutan bukan mengubah isi
        $stmt = $pdo->prepare('UPDATE projects SET sort_order = ?, updated_at = updated_at WHERE id = ?');
        $pdo->beginTransaction();

        try {
            foreach ($ids as $position => $projectId) {
                $stmt->execute([$position + 1, $projectId]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** @param int[] $ids */
    private static function saveGallery(PDO $pdo, int $projectId, array $ids): void
    {
        $pdo->prepare('DELETE FROM project_images WHERE project_id = ?')->execute([$projectId]);

        $insert = $pdo->prepare(
            'INSERT INTO project_images (project_id, media_id, sort_order) VALUES (?, ?, ?)'
        );
        foreach (array_values($ids) as $position => $mediaId) {
            $insert->execute([$projectId, $mediaId, $position + 1]);
        }
    }

    private static function slugTaken(string $slug, ?int $ignoreId): bool
    {
        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM projects WHERE slug = ? AND id <> ?');
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