<?php

declare(strict_types=1);

use App\Core\Database;
use App\Services\ArticleTaxonomy;
use App\Services\Articles;
use App\Services\Faqs;
use App\Services\Homepage;
use App\Services\Inquiries;
use App\Services\Media;
use App\Services\Offerings;
use App\Services\Pages;
use App\Services\ProjectCategories;
use App\Services\Projects;
use App\Services\Sanitizer;
use App\Services\Settings;
use App\Services\Testimonials;

// Hanya boleh dijalankan dari command line
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');

require BASE_PATH . '/app/bootstrap.php';

/** Gambar abstrak dalam palet situs. Deterministik per nama. */
function seed_image(int $w, int $h, string $seed): GdImage
{
    $schemes = [
        [[31, 58, 52], [246, 243, 238], [180, 83, 42], [60, 92, 84]],
        [[246, 243, 238], [31, 58, 52], [180, 83, 42], [221, 215, 204]],
        [[20, 24, 31], [180, 83, 42], [246, 243, 238], [60, 66, 76]],
        [[221, 215, 204], [20, 24, 31], [31, 58, 52], [246, 243, 238]],
        [[180, 83, 42], [246, 243, 238], [31, 58, 52], [214, 130, 92]],
    ];

    $hash = crc32($seed);
    mt_srand($hash);
    $s = $schemes[$hash % count($schemes)];

    $img = imagecreatetruecolor($w, $h);
    $col = static fn (array $c): int => (int) imagecolorallocate($img, $c[0], $c[1], $c[2]);

    imagefilledrectangle($img, 0, 0, $w - 1, $h - 1, $col($s[0]));

    for ($i = 0; $i < 5; $i++) {
        $rw = (int) ($w * mt_rand(15, 45) / 100);
        $rh = (int) ($h * mt_rand(20, 60) / 100);
        $x = mt_rand(0, $w - $rw);
        $y = mt_rand(0, $h - $rh);
        imagefilledrectangle($img, $x, $y, $x + $rw, $y + $rh, $col($s[1 + $i % 3]));
    }

    $d = (int) (min($w, $h) * mt_rand(25, 45) / 100);
    imagefilledellipse($img, mt_rand($d, $w - $d), mt_rand($d, $h - $d), $d, $d, $col($s[2]));

    imagesetthickness($img, max(2, (int) ($w / 200)));
    for ($i = 0; $i < 3; $i++) {
        imageline($img, mt_rand(0, $w), 0, mt_rand(0, $w), $h, $col($s[1]));
    }

    return $img;
}

function seed_prompt(string $label): string
{
    fwrite(STDERR, $label);

    return trim((string) fgets(STDIN));
}

$args = array_slice($argv, 1);
$fresh = in_array('--fresh', $args, true);
$force = in_array('--force', $args, true);
$yes = in_array('--yes', $args, true);
$withInquiries = in_array('--inquiries', $args, true);

$created = []; // file gambar yang dibuat, dibersihkan kalau seed gagal

try {
    if (config('app.env') === 'production' && !$force) {
        fwrite(STDERR, "Ditolak: APP_ENV=production. Seed berisi data fiktif. Pakai --force kalau memang disengaja.\n");
        exit(1);
    }

    if (!function_exists('imagecreatetruecolor')) {
        fwrite(STDERR, "Ekstensi GD tidak aktif, gambar contoh tidak bisa dibuat.\n");
        exit(1);
    }

    $pdo = Database::connection();

    $userId = (int) $pdo->query(
        "SELECT u.id FROM users u JOIN roles r ON r.id = u.role_id
         WHERE r.name = 'super_admin' AND u.is_active = 1 ORDER BY u.id ASC LIMIT 1"
    )->fetchColumn();

    if ($userId < 1) {
        fwrite(STDERR, "Belum ada Super Admin aktif. Jalankan dulu: php bin/make-admin.php\n");
        exit(1);
    }

    $existing = [];
    foreach (['media', 'pages', 'services', 'projects', 'articles', 'settings'] as $table) {
        if ((int) $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn() > 0) {
            $existing[] = $table;
        }
    }

    if ($existing !== [] && !$fresh) {
        fwrite(STDERR, 'Database sudah berisi data (' . implode(', ', $existing) . ").\n"
            . "Jalankan dengan --fresh untuk menghapus semua konten lalu mengisi ulang. Akun pengguna tidak dihapus.\n");
        exit(1);
    }

    if ($fresh) {
        if (!$yes && strtoupper(seed_prompt("Semua konten (halaman, layanan, proyek, artikel, media, pengaturan, inquiry) akan DIHAPUS.\nKetik YA untuk lanjut: ")) !== 'YA') {
            fwrite(STDERR, "Dibatalkan.\n");
            exit(1);
        }

        foreach ($pdo->query('SELECT path FROM media')->fetchAll(PDO::FETCH_COLUMN) as $path) {
            if (preg_match('#^\d{4}/\d{2}/[a-f0-9]{32}\.(jpg|png|webp|gif)$#', (string) $path) === 1) {
                @unlink(Media::dir() . '/' . $path);
            }
        }

        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        try {
            foreach ([
                'project_images', 'article_tag', 'projects', 'project_categories', 'articles',
                'article_categories', 'tags', 'services', 'pages', 'testimonials', 'faqs',
                'inquiries', 'homepage_sections', 'settings', 'media',
            ] as $table) {
                $pdo->exec("TRUNCATE TABLE $table");
            }
        } finally {
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        }
        echo "Konten lama dihapus.\n";
    }

    /** @var array<string, mixed> $data */
    $data = require BASE_PATH . '/database/seeds/demo.php';

    $now = time();
    $ago = static fn (int $days): string => date('Y-m-d H:i:s', $now - $days * 86400 - 3600);
    $clean = static fn (string $html): string => Sanitizer::clean($html);

    // ---- Gambar ----
    $sub = date('Y/m');
    $dir = Media::dir() . '/' . $sub;
    if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Folder public/uploads tidak bisa dibuat.');
    }

    $webp = function_exists('imagewebp');
    $insertMedia = $pdo->prepare(
        'INSERT INTO media (path, original_name, mime, size, width, height, alt_text, uploaded_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $img = [];
    foreach ($data['images'] as $key => [$w, $h, $alt]) {
        $png = $key === 'favicon';
        $ext = $png ? 'png' : ($webp ? 'webp' : 'jpg');
        $mime = $png ? 'image/png' : ($webp ? 'image/webp' : 'image/jpeg');
        $name = bin2hex(random_bytes(16)) . '.' . $ext;
        $file = $dir . '/' . $name;

        $canvas = seed_image((int) $w, (int) $h, (string) $key);
        $ok = $png ? imagepng($canvas, $file) : ($webp ? imagewebp($canvas, $file, 80) : imagejpeg($canvas, $file, 85));
        if (!$ok) {
            throw new RuntimeException('Gagal menulis gambar ' . $key);
        }
        @chmod($file, 0644);
        $created[] = $file;

        $insertMedia->execute([$sub . '/' . $name, 'contoh-' . $key . '.' . $ext, $mime, (int) filesize($file), $w, $h, $alt, $userId]);
        $img[$key] = (int) $pdo->lastInsertId();
    }
    echo count($img) . " gambar contoh dibuat.\n";

    $imageId = static fn (string $key): ?int => $img[$key] ?? null;

    // ---- Pengaturan ----
    $settings = $data['settings'];
    $settings['favicon'] = (string) $img['favicon'];
    $settings['og_image'] = (string) $img['og'];
    Settings::save($settings);
    echo "Pengaturan website diisi.\n";

    // ---- Beranda ----
    $rows = [];
    $order = 0;
    foreach (Homepage::sections() as $key => $sec) {
        $d = $sec['data'];
        if (isset($data['homepage'][$key])) {
            $d = array_replace($d, $data['homepage'][$key]);
        }
        if (is_string($d['image'] ?? null) && str_starts_with($d['image'], '@')) {
            $d['image'] = (string) $img[substr($d['image'], 1)];
        }
        $rows[$key] = ['active' => $key !== 'stats', 'order' => ++$order, 'data' => $d];
    }
    Homepage::save($rows);
    echo "Beranda diisi (Statistik sengaja nonaktif).\n";

    // ---- Halaman ----
    foreach ($data['pages'] as $i => $page) {
        Pages::create([
            'title' => $page['title'],
            'slug' => $page['slug'],
            'content' => $clean($page['content']),
            'featured_image_id' => null,
            'status' => 'published',
            'published_at' => $ago(60 - $i),
            'seo_title' => '',
            'seo_description' => $page['description'],
        ], $userId);
    }
    echo count($data['pages']) . " halaman dibuat.\n";

    // ---- Layanan ----
    foreach ($data['services'] as $i => $s) {
        Offerings::create([
            'title' => $s['title'],
            'slug' => $s['slug'],
            'summary' => $s['summary'],
            'content' => $clean($s['content']),
            'featured_image_id' => $imageId($s['image']),
            'details' => ['benefits' => $s['benefits'], 'process' => $s['process'], 'faqs' => $s['faqs']],
            'cta_label' => '',
            'cta_url' => '',
            'is_featured' => $s['featured'] ? 1 : 0,
            'status' => 'published',
            'published_at' => $ago(50 - $i),
            'seo_title' => '',
            'seo_description' => '',
        ], $userId);
    }
    echo count($data['services']) . " layanan dibuat.\n";

    // ---- Kategori dan proyek ----
    $projectCats = [];
    foreach ($data['project_categories'] as $name) {
        $projectCats[$name] = ProjectCategories::create($name);
    }

    foreach ($data['projects'] as $p) {
        Projects::create([
            'category_id' => $projectCats[$p['category']] ?? null,
            'title' => $p['title'],
            'slug' => $p['slug'],
            'summary' => $p['summary'],
            'client_name' => $p['client'],
            'show_client' => $p['show_client'] ? 1 : 0,
            'location' => $p['location'],
            'completed_year' => $p['year'],
            'content' => $clean($p['content']),
            'challenge' => $p['challenge'],
            'solution' => $p['solution'],
            'results' => $p['results'],
            'featured_image_id' => $imageId($p['image']),
            'is_featured' => $p['featured'] ? 1 : 0,
            'status' => 'published',
            'published_at' => $ago($p['days']),
            'seo_title' => '',
            'seo_description' => '',
            'gallery' => array_values(array_filter(array_map($imageId, $p['gallery']))),
        ], $userId);
    }
    echo count($data['projects']) . ' proyek dan ' . count($projectCats) . " kategori dibuat.\n";

    // ---- Blog ----
    $articleCats = [];
    foreach ($data['article_categories'] as $name) {
        $articleCats[$name] = ArticleTaxonomy::create('categories', $name);
    }

    foreach ($data['articles'] as $a) {
        Articles::create([
            'category_id' => $articleCats[$a['category']] ?? null,
            'author_id' => $userId,
            'title' => $a['title'],
            'slug' => $a['slug'],
            'excerpt' => $a['excerpt'],
            'content' => $clean($a['content']),
            'featured_image_id' => $imageId($a['image']),
            'status' => 'published',
            'published_at' => $ago($a['days']),
            'seo_title' => '',
            'seo_description' => $a['seo_description'],
            'tags' => $a['tags'],
        ], $userId);
    }
    echo count($data['articles']) . " artikel dibuat.\n";

    // ---- Testimoni dan FAQ ----
    foreach ($data['testimonials'] as $t) {
        Testimonials::create($t + ['photo_id' => null, 'status' => 'published'], $userId);
    }
    foreach ($data['faqs'] as $f) {
        Faqs::create($f + ['status' => 'published'], $userId);
    }
    echo count($data['testimonials']) . ' testimoni dan ' . count($data['faqs']) . " FAQ dibuat.\n";

    // ---- Inquiry (opsional) ----
    if ($withInquiries) {
        foreach ($data['inquiries'] as $q) {
            $id = Inquiries::create([
                'name' => $q['name'], 'email' => $q['email'], 'whatsapp' => $q['whatsapp'],
                'company' => $q['company'], 'need_type' => $q['need_type'], 'message' => $q['message'],
            ], $q['ip']);

            if ($q['status'] !== 'new') {
                Inquiries::update($id, ['status' => $q['status'], 'admin_note' => $q['note']], $userId);
            }
        }
        echo count($data['inquiries']) . " inquiry contoh dibuat.\n";
    }

    echo "\nSeed selesai. Semua data fiktif, ganti sebelum dipakai klien.\n";
    echo "Pengindeksan Google diset DIBLOKIR. Ubah di Pengaturan, tab SEO, saat data asli sudah terpasang.\n";
} catch (Throwable $e) {
    foreach ($created as $file) {
        @unlink($file);
    }
    fwrite(STDERR, 'Seed gagal: ' . $e->getMessage() . "\n");
    fwrite(STDERR, "Sebagian data mungkin sudah masuk. Jalankan ulang dengan --fresh.\n");
    exit(1);
}