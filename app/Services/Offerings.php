<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ErrorHandler;
use DateTimeImmutable;
use PDO;
use Throwable;

/** Layanan perusahaan (tabel services). */
final class Offerings
{
    private const LIST_LIMIT = 100;

    /** Definisi daftar item (manfaat, proses, FAQ). */
    public static function repeaters(): array
    {
        return [
            'benefits' => [
                'label' => 'Manfaat layanan',
                'item_label' => 'Manfaat',
                'max' => 8,
                'hint' => 'Hasil nyata yang didapat klien. Hindari klaim yang tidak bisa dibuktikan.',
                'fields' => [
                    'title' => ['label' => 'Judul', 'type' => 'text', 'max' => 80, 'required' => true],
                    'text' => ['label' => 'Penjelasan', 'type' => 'textarea', 'max' => 250],
                ],
            ],
            'process' => [
                'label' => 'Proses pengerjaan',
                'item_label' => 'Langkah',
                'max' => 8,
                'hint' => 'Urutan kerja dari awal sampai selesai.',
                'fields' => [
                    'title' => ['label' => 'Judul langkah', 'type' => 'text', 'max' => 80, 'required' => true],
                    'text' => ['label' => 'Penjelasan', 'type' => 'textarea', 'max' => 250],
                ],
            ],
            'faqs' => [
                'label' => 'FAQ layanan',
                'item_label' => 'Pertanyaan',
                'max' => 8,
                'hint' => 'Pertanyaan yang sering diajukan calon klien tentang layanan ini.',
                'fields' => [
                    'question' => ['label' => 'Pertanyaan', 'type' => 'text', 'max' => 160, 'required' => true],
                    'answer' => ['label' => 'Jawaban', 'type' => 'textarea', 'max' => 600, 'required' => true],
                ],
            ],
        ];
    }

    /** Field CTA per layanan (kosong = pakai CTA default dari Pengaturan). */
    public static function ctaFields(): array
    {
        return [
            'cta_label' => [
                'label' => 'Teks tombol CTA', 'type' => 'text', 'max' => 40,
                'hint' => 'Kosong = memakai teks CTA dari Pengaturan.',
            ],
            'cta_url' => [
                'label' => 'Tujuan tombol CTA', 'type' => 'link', 'max' => 200,
                'hint' => 'Path internal (contoh /kontak) atau URL lengkap. Kosong = memakai CTA dari Pengaturan.',
            ],
        ];
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM services WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /** Semua layanan berurutan (untuk daftar admin). @return array<int, array> */
    public static function all(): array
    {
        $limit = self::LIST_LIMIT;

        return Database::connection()->query(
            "SELECT s.id, s.title, s.slug, s.status, s.published_at, s.is_featured, s.sort_order,
                    s.updated_at, s.created_by, u.name AS author
             FROM services s LEFT JOIN users u ON u.id = s.created_by
             ORDER BY s.sort_order ASC, s.id ASC
             LIMIT $limit"
        )->fetchAll();
    }

    /** Manfaat, proses, dan FAQ dari kolom details. @return array{benefits: array, process: array, faqs: array} */
    public static function details(array $row): array
    {
        $decoded = is_string($row['details'] ?? null) ? json_decode((string) $row['details'], true) : null;
        $decoded = is_array($decoded) ? $decoded : [];

        $out = [];
        foreach (array_keys(self::repeaters()) as $key) {
            $out[$key] = is_array($decoded[$key] ?? null) ? $decoded[$key] : [];
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $in      input mentah dari form
     * @param array|null           $current baris layanan saat edit, null saat membuat
     * @return array{0: array<string, mixed>, 1: array<string, mixed>} [nilai bersih, error]
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
            $base = $title !== '' ? Pages::slugify($title) : 'layanan';
            $v['slug'] = self::uniqueSlug($base, $id);
        } else {
            $v['slug'] = $slug;
            if (strlen($slug) > 150 || preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug) !== 1) {
                $e['slug'] = 'Slug hanya boleh huruf kecil, angka, dan tanda minus (maksimal 150 karakter).';
            } elseif (self::slugTaken($slug, $id)) {
                $e['slug'] = 'Slug sudah dipakai layanan lain.';
            }
        }

        // Ringkasan
        $summary = self::line($in['summary'] ?? '');
        if (mb_strlen($summary) > 300) {
            $e['summary'] = 'Ringkasan maksimal 300 karakter.';
        }
        $v['summary'] = mb_substr($summary, 0, 300);

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

        // CTA
        [$cta, $ctaErrors] = Settings::validateFields(self::ctaFields(), $in);
        $v['cta_label'] = $cta['cta_label'] ?? '';
        $v['cta_url'] = $cta['cta_url'] ?? '';
        $e += $ctaErrors;

        // Daftar item
        $details = [];
        foreach (self::repeaters() as $key => $rep) {
            $details[$key] = self::validateRepeater($key, $rep, $in[$key] ?? [], $e);
        }
        $v['details'] = $details;

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
        $next = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM services')->fetchColumn();

        $stmt = $pdo->prepare(
            'INSERT INTO services (title, slug, summary, content, featured_image_id, details,
                                   cta_label, cta_url, is_featured, status, published_at, sort_order,
                                   seo_title, seo_description, created_by, updated_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $v['title'], $v['slug'], $v['summary'], $v['content'], $v['featured_image_id'],
            self::encode($v['details']), $v['cta_label'], $v['cta_url'], $v['is_featured'],
            $v['status'], $v['published_at'], $next, $v['seo_title'], $v['seo_description'],
            $userId, $userId,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $v, ?int $userId): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE services SET title = ?, slug = ?, summary = ?, content = ?, featured_image_id = ?,
                    details = ?, cta_label = ?, cta_url = ?, is_featured = ?, status = ?,
                    published_at = ?, seo_title = ?, seo_description = ?, updated_by = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $v['title'], $v['slug'], $v['summary'], $v['content'], $v['featured_image_id'],
            self::encode($v['details']), $v['cta_label'], $v['cta_url'], $v['is_featured'],
            $v['status'], $v['published_at'], $v['seo_title'], $v['seo_description'],
            $userId, $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM services WHERE id = ?');
        $stmt->execute([$id]);
    }

    /** Geser satu posisi ke atas atau ke bawah; seluruh urutan dinomori ulang. */
    public static function move(int $id, string $direction): void
    {
        $pdo = Database::connection();
        $ids = array_map(
            'intval',
            $pdo->query('SELECT id FROM services ORDER BY sort_order ASC, id ASC')->fetchAll(PDO::FETCH_COLUMN)
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
        $stmt = $pdo->prepare('UPDATE services SET sort_order = ?, updated_at = updated_at WHERE id = ?');
        $pdo->beginTransaction();

        try {
            foreach ($ids as $position => $serviceId) {
                $stmt->execute([$position + 1, $serviceId]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * @param array<string, mixed> $errors diisi per key: ['g' => [pesan], 'i' => [indeks => [field => pesan]]]
     * @return array<int, array<string, string>>
     */
    private static function validateRepeater(string $key, array $rep, mixed $raw, array &$errors): array
    {
        $rows = is_array($raw) ? array_values($raw) : [];
        $max = (int) $rep['max'];

        if (count($rows) > $max) {
            $errors[$key]['g'][] = 'Maksimal ' . $max . ' item.';
        }

        $items = [];
        foreach (array_slice($rows, 0, $max) as $n => $row) {
            if (!is_array($row) || self::blank($row)) {
                continue;
            }

            [$data, $itemErrors] = Settings::validateFields($rep['fields'], $row);
            if ($itemErrors !== []) {
                $errors[$key]['i'][$n] = $itemErrors;
            }
            $items[] = $data;
        }

        return $items;
    }

    private static function blank(array $row): bool
    {
        foreach ($row as $value) {
            if (is_string($value) && trim($value) !== '') {
                return false;
            }
        }

        return true;
    }

    private static function encode(array $details): string
    {
        return json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    private static function slugTaken(string $slug, ?int $ignoreId): bool
    {
        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM services WHERE slug = ? AND id <> ?');
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