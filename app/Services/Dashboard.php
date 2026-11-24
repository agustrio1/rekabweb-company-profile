<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ErrorHandler;
use Throwable;

final class Dashboard
{
    /** Modul konten. Nama tabel hanya berasal dari konstanta ini. 'dated' = punya published_at. */
    private const CONTENT = [
        'pages' => ['label' => 'Halaman', 'route' => 'admin.pages', 'dated' => true],
        'services' => ['label' => 'Layanan', 'route' => 'admin.services', 'dated' => true],
        'projects' => ['label' => 'Portofolio', 'route' => 'admin.projects', 'dated' => true],
        'articles' => ['label' => 'Artikel', 'route' => 'admin.articles', 'dated' => true],
        'testimonials' => ['label' => 'Testimoni', 'route' => 'admin.testimonials', 'dated' => false],
        'faqs' => ['label' => 'FAQ', 'route' => 'admin.faqs', 'dated' => false],
    ];

    /**
     * Jumlah terbit, draft, dan terjadwal per modul konten.
     *
     * @return array<string, array{key: string, label: string, route: string, total: int, live: int, draft: int, scheduled: int}>
     */
    public static function contentSummary(): array
    {
        $out = [];
        $now = date('Y-m-d H:i:s');

        foreach (self::CONTENT as $table => $meta) {
            $row = [
                'key' => $table,
                'label' => $meta['label'],
                'route' => $meta['route'],
                'total' => 0,
                'live' => 0,
                'draft' => 0,
                'scheduled' => 0,
            ];

            try {
                $pdo = Database::connection();

                if ($meta['dated']) {
                    $stmt = $pdo->prepare(
                        "SELECT COUNT(*) AS total,
                                SUM(status = 'draft') AS draft,
                                SUM(status = 'published' AND (published_at IS NULL OR published_at <= ?)) AS live,
                                SUM(status = 'published' AND published_at > ?) AS scheduled
                         FROM $table"
                    );
                    $stmt->execute([$now, $now]);
                } else {
                    $stmt = $pdo->query(
                        "SELECT COUNT(*) AS total,
                                SUM(status = 'draft') AS draft,
                                SUM(status = 'published') AS live,
                                0 AS scheduled
                         FROM $table"
                    );
                }

                $data = $stmt->fetch();
                if (is_array($data)) {
                    $row['total'] = (int) $data['total'];
                    $row['live'] = (int) $data['live'];
                    $row['draft'] = (int) $data['draft'];
                    $row['scheduled'] = (int) $data['scheduled'];
                }
            } catch (Throwable $e) {
                ErrorHandler::log($e);
            }

            $out[$table] = $row;
        }

        return $out;
    }

    /** @return array{new: int, total: int} */
    public static function inquiryStats(): array
    {
        try {
            $counts = Inquiries::statusCounts();

            return ['new' => $counts['new'], 'total' => array_sum($counts)];
        } catch (Throwable $e) {
            ErrorHandler::log($e);

            return ['new' => 0, 'total' => 0];
        }
    }

    /** @return array<int, array> */
    public static function recentInquiries(int $limit = 5): array
    {
        try {
            return Inquiries::recent($limit);
        } catch (Throwable $e) {
            ErrorHandler::log($e);

            return [];
        }
    }

    public static function mediaCount(): int
    {
        try {
            return Media::count();
        } catch (Throwable $e) {
            ErrorHandler::log($e);

            return 0;
        }
    }

    /** @return array{active: int, total: int} */
    public static function homepageStats(): array
    {
        return [
            'active' => count(Homepage::active()),
            'total' => count(Homepage::definitions()),
        ];
    }

    /** Halaman yang terakhir diubah. @return array<int, array> */
    public static function recentPages(int $limit = 5): array
    {
        try {
            $limit = max(1, min(20, $limit));

            return Database::connection()->query(
                "SELECT p.id, p.title, p.status, p.published_at, p.updated_at, u.name AS editor
                 FROM pages p LEFT JOIN users u ON u.id = p.updated_by
                 ORDER BY p.updated_at DESC, p.id DESC
                 LIMIT $limit"
            )->fetchAll();
        } catch (Throwable $e) {
            ErrorHandler::log($e);

            return [];
        }
    }

    /**
     * Daftar kelengkapan website.
     *
     * @return array<int, array{label: string, done: bool, hint: string}>
     */
    public static function checklist(): array
    {
        $filled = static fn (string $key): bool => trim((string) Settings::get($key, '')) !== '';

        return [
            ['label' => 'Logo perusahaan', 'done' => Settings::imageUrl('logo') !== '', 'hint' => 'Unggah dan pilih logo di tab Perusahaan.'],
            ['label' => 'Favicon', 'done' => Settings::imageUrl('favicon') !== '', 'hint' => 'Ikon kecil di tab browser.'],
            ['label' => 'Nomor WhatsApp', 'done' => $filled('whatsapp'), 'hint' => 'Dipakai tombol WhatsApp di website.'],
            ['label' => 'Email dan alamat', 'done' => $filled('email') && $filled('address'), 'hint' => 'Tampil di halaman kontak dan footer.'],
            ['label' => 'Meta description default', 'done' => $filled('seo_description'), 'hint' => 'Tab SEO. Tampil di hasil pencarian Google.'],
            ['label' => 'Gambar OG default', 'done' => Settings::imageUrl('og_image') !== '', 'hint' => 'Tampil saat link dibagikan di WhatsApp dan sosial media.'],
        ];
    }

    public static function isNoindex(): bool
    {
        return Settings::get('robots', 'index') === 'noindex';
    }
}