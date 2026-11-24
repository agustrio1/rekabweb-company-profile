<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ErrorHandler;
use finfo;
use Throwable;

final class Media
{
    public const PER_PAGE = 24;
    private const MAX_BYTES = 5242880;      // 5 MB
    private const MAX_PIXELS = 40000000;    // anti decompression bomb
    private const MAX_DIMENSION = 1920;     // sisi terpanjang hasil konversi
    private const WEBP_QUALITY = 82;

    /** MIME (dari isi file) => ekstensi yang dipakai server */
    private const TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    public static function dir(): string
    {
        return PUBLIC_PATH . '/uploads';
    }

    public static function url(string $path): string
    {
        return url('uploads/' . $path);
    }

    /** Batas efektif: yang terkecil antara aturan aplikasi dan php.ini. */
    public static function maxBytes(): int
    {
        return min(self::MAX_BYTES, self::iniBytes('upload_max_filesize'), self::iniBytes('post_max_size'));
    }

    public static function humanSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0, ',', '.') . ' KB';
        }

        return $bytes . ' B';
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM media WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function count(): int
    {
        return (int) Database::connection()->query('SELECT COUNT(*) FROM media')->fetchColumn();
    }

    /** @return array<int, array> */
    public static function page(int $page): array
    {
        $limit = self::PER_PAGE;
        $offset = max(0, $page - 1) * $limit;

        return Database::connection()
            ->query("SELECT * FROM media ORDER BY id DESC LIMIT $limit OFFSET $offset")
            ->fetchAll();
    }

    /**
     * Simpan satu file hasil upload.
     *
     * @param array{name?: mixed, tmp_name?: mixed, error?: mixed} $file
     * @return array{0: ?array, 1: ?string} [baris media, pesan error]
     */
    public static function store(array $file, ?int $userId): array
    {
        $original = is_string($file['name'] ?? null) ? (string) $file['name'] : '';
        $label = self::cleanName($original);
        $fail = static fn (string $msg): array => [null, ($label !== '' ? $label . ': ' : '') . $msg];

        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            return $fail(self::uploadError($error));
        }

        $tmp = is_string($file['tmp_name'] ?? null) ? (string) $file['tmp_name'] : '';
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return $fail('Upload tidak valid.');
        }

        $size = (int) filesize($tmp);
        if ($size <= 0) {
            return $fail('File kosong.');
        }
        if ($size > self::MAX_BYTES) {
            return $fail('Ukuran melebihi ' . self::humanSize(self::MAX_BYTES) . '.');
        }

        // Jenis file ditentukan dari isinya, bukan dari nama atau header klien
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
        if (!is_string($mime) || !isset(self::TYPES[$mime])) {
            return $fail('Format tidak didukung. Gunakan JPG, PNG, WebP, atau GIF.');
        }

        $info = @getimagesize($tmp);
        if ($info === false || $info[0] < 1 || $info[1] < 1 || image_type_to_mime_type($info[2]) !== $mime) {
            return $fail('File bukan gambar yang valid.');
        }

        [$width, $height] = $info;
        if ($width * $height > self::MAX_PIXELS) {
            return $fail('Dimensi gambar terlalu besar.');
        }

        $sub = date('Y/m');
        $dir = self::dir() . '/' . $sub;
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            return $fail('Folder upload tidak bisa dibuat. Periksa permission public/uploads.');
        }

        // Perkecil dan ubah ke WebP kalau memungkinkan. null = simpan file asli.
        $converted = self::optimize($tmp, $mime, (int) $width, (int) $height, $size);
        $ext = $converted !== null ? $converted['ext'] : self::TYPES[$mime];

        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $target = $dir . '/' . $filename;

        if ($converted !== null) {
            $copied = @copy($converted['file'], $target);
            @unlink($converted['file']);
            if (!$copied) {
                return $fail('File gagal disimpan.');
            }

            $mime = $converted['mime'];
            $width = $converted['width'];
            $height = $converted['height'];
            $size = $converted['size'];
        } elseif (!move_uploaded_file($tmp, $target)) {
            return $fail('File gagal disimpan.');
        }
        @chmod($target, 0644);

        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare(
                'INSERT INTO media (path, original_name, mime, size, width, height, uploaded_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$sub . '/' . $filename, $label, $mime, $size, $width, $height, $userId]);
            $id = (int) $pdo->lastInsertId();
        } catch (Throwable $e) {
            @unlink($target);
            ErrorHandler::log($e);

            return $fail('Gagal menyimpan data gambar.');
        }

        return [self::find($id), null];
    }

    public static function updateAlt(int $id, string $alt): void
    {
        $stmt = Database::connection()->prepare('UPDATE media SET alt_text = ? WHERE id = ?');
        $stmt->execute([$alt, $id]);
    }

    /** Nama bagian website yang memakai gambar ini. */
    public static function usages(int $id): array
    {
        $pdo = Database::connection();
        $labels = [];

        // Pengaturan website (logo, favicon, OG image)
        $fields = array_filter(Settings::fields(), static fn (array $f): bool => ($f['type'] ?? '') === 'image');
        if ($fields !== []) {
            $keys = array_keys($fields);
            $in = implode(',', array_fill(0, count($keys), '?'));

            $stmt = $pdo->prepare(
                "SELECT setting_key FROM settings WHERE setting_value = ? AND setting_key IN ($in)"
            );
            $stmt->execute([(string) $id, ...$keys]);

            foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $key) {
                $labels[] = (string) $fields[$key]['label'];
            }
        }

        $row = self::find($id);
        $needle = $row !== null ? '%uploads/' . $row['path'] . '%' : null;

        // Halaman, layanan, proyek, dan artikel: sebagai gambar utama atau tersisip di konten
        $tables = [
            'pages' => 'Halaman',
            'services' => 'Layanan',
            'projects' => 'Proyek',
            'articles' => 'Artikel',
        ];

        foreach ($tables as $table => $noun) {
            try {
                $stmt = $pdo->prepare("SELECT title FROM $table WHERE featured_image_id = ? LIMIT 5");
                $stmt->execute([$id]);
                foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $title) {
                    $labels[] = $noun . ' "' . $title . '"';
                }

                if ($needle !== null) {
                    $stmt = $pdo->prepare("SELECT title FROM $table WHERE content LIKE ? LIMIT 5");
                    $stmt->execute([$needle]);
                    foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $title) {
                        $label = $noun . ' "' . $title . '"';
                        if (!in_array($label, $labels, true)) {
                            $labels[] = $label;
                        }
                    }
                }
            } catch (Throwable $e) {
                // Tabel belum ada (migration belum jalan): lewati
                ErrorHandler::log($e);
            }
        }

        // Galeri proyek
        try {
            $stmt = $pdo->prepare(
                'SELECT p.title FROM project_images i JOIN projects p ON p.id = i.project_id
                 WHERE i.media_id = ? LIMIT 5'
            );
            $stmt->execute([$id]);
            foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $title) {
                $labels[] = 'Galeri proyek "' . $title . '"';
            }
        } catch (Throwable $e) {
            // Tabel project_images belum ada: lewati
            ErrorHandler::log($e);
        }

        // Foto testimoni
        try {
            $stmt = $pdo->prepare('SELECT name FROM testimonials WHERE photo_id = ? LIMIT 5');
            $stmt->execute([$id]);
            foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $name) {
                $labels[] = 'Testimoni "' . $name . '"';
            }
        } catch (Throwable $e) {
            // Tabel testimonials belum ada: lewati
            ErrorHandler::log($e);
        }

        // Beranda: gambar di Hero atau Ringkasan perusahaan
        try {
            $stmt = $pdo->prepare('SELECT section_key FROM homepage_sections WHERE content LIKE ?');
            $stmt->execute(['%"image":"' . $id . '"%']);
            $defs = (array) config('homepage.sections', []);
            foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $key) {
                $labels[] = 'Beranda "' . ($defs[$key]['label'] ?? $key) . '"';
            }
        } catch (Throwable $e) {
            // Tabel homepage_sections belum ada: lewati
            ErrorHandler::log($e);
        }

        return $labels;
    }

    /** @return array{0: bool, 1: string} [berhasil, pesan] */
    public static function delete(int $id): array
    {
        $row = self::find($id);
        if ($row === null) {
            return [false, 'Gambar tidak ditemukan.'];
        }

        $used = self::usages($id);
        if ($used !== []) {
            return [false, 'Gambar sedang dipakai di: ' . implode(', ', $used) . '. Ganti dulu di sana.'];
        }

        $stmt = Database::connection()->prepare('DELETE FROM media WHERE id = ?');
        $stmt->execute([$id]);

        // Hanya hapus file kalau path cocok pola yang dibuat server ini
        $path = (string) $row['path'];
        if (preg_match('#^\d{4}/\d{2}/[a-f0-9]{32}\.(jpg|png|webp|gif)$#', $path) === 1) {
            if (!@unlink(self::dir() . '/' . $path)) {
                ErrorHandler::log(new \RuntimeException('File media gagal dihapus: ' . $path));
            }
        }

        return [true, 'Gambar dihapus.'];
    }

    /**
     * Perkecil (maks. 1920 px) dan ubah ke WebP. Hasilnya berupa file sementara di storage/cache.
     * Mengembalikan null kalau tidak perlu atau tidak bisa, dan file asli disimpan apa adanya.
     *
     * @return array{file: string, ext: string, mime: string, width: int, height: int, size: int}|null
     */
    private static function optimize(string $tmp, string $mime, int $width, int $height, int $size): ?array
    {
        $out = false;

        try {
            if (!self::convertEnabled() || !function_exists('imagewebp')) {
                return null;
            }

            // GIF animasi tidak dikonversi supaya animasinya tidak hilang
            if ($mime === 'image/gif' && self::isAnimatedGif($tmp)) {
                return null;
            }

            // WebP yang sudah cukup kecil dibiarkan
            if ($mime === 'image/webp' && max($width, $height) <= self::MAX_DIMENSION) {
                return null;
            }

            if (!self::memoryAllows($width, $height)) {
                return null;
            }

            $loaders = [
                'image/jpeg' => 'imagecreatefromjpeg',
                'image/png' => 'imagecreatefrompng',
                'image/gif' => 'imagecreatefromgif',
                'image/webp' => 'imagecreatefromwebp',
            ];
            $loader = $loaders[$mime] ?? '';
            if ($loader === '' || !function_exists($loader)) {
                return null;
            }

            $img = @$loader($tmp);
            if ($img === false) {
                return null;
            }

            $changed = false;

            // Terapkan orientasi EXIF (foto HP) sebelum metadata dibuang
            if ($mime === 'image/jpeg' && function_exists('exif_read_data') && function_exists('imagerotate')) {
                $exif = @exif_read_data($tmp);
                $angle = match ((int) ($exif['Orientation'] ?? 1)) {
                    3 => 180,
                    6 => -90,
                    8 => 90,
                    default => 0,
                };
                if ($angle !== 0) {
                    $rotated = imagerotate($img, $angle, 0);
                    if ($rotated !== false) {
                        $img = $rotated;
                        $changed = true;
                    }
                }
            }

            $w = imagesx($img);
            $h = imagesy($img);
            $longest = max($w, $h);

            if ($longest > self::MAX_DIMENSION) {
                $scale = self::MAX_DIMENSION / $longest;
                $nw = max(1, (int) round($w * $scale));
                $nh = max(1, (int) round($h * $scale));

                $dst = imagecreatetruecolor($nw, $nh);
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                imagefill($dst, 0, 0, (int) imagecolorallocatealpha($dst, 0, 0, 0, 127));

                if (!imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h)) {
                    return null;
                }

                $img = $dst;
                $w = $nw;
                $h = $nh;
                $changed = true;
            } else {
                if (!imageistruecolor($img)) {
                    imagepalettetotruecolor($img);
                }
                imagealphablending($img, false);
                imagesavealpha($img, true);
            }

            $cache = BASE_PATH . '/storage/cache';
            if (!is_dir($cache) || !is_writable($cache)) {
                return null;
            }
            $out = @tempnam($cache, 'img');
            if ($out === false) {
                return null;
            }

            // PNG (logo, ilustrasi) lossless supaya tetap tajam; foto lossy
            $quality = ($mime === 'image/png' && defined('IMG_WEBP_LOSSLESS'))
                ? IMG_WEBP_LOSSLESS
                : self::WEBP_QUALITY;

            $ok = @imagewebp($img, $out, $quality);
            $newSize = $ok ? (int) @filesize($out) : 0;

            // Pakai hasil konversi hanya kalau lebih kecil, atau gambarnya memang diperkecil/diputar
            if (!$ok || $newSize <= 0 || (!$changed && $newSize >= $size)) {
                @unlink($out);

                return null;
            }

            return [
                'file' => $out,
                'ext' => 'webp',
                'mime' => 'image/webp',
                'width' => $w,
                'height' => $h,
                'size' => $newSize,
            ];
        } catch (Throwable $e) {
            if (is_string($out)) {
                @unlink($out);
            }
            ErrorHandler::log($e);

            return null;
        }
    }

    private static function convertEnabled(): bool
    {
        return filter_var(env('MEDIA_CONVERT', 'true'), FILTER_VALIDATE_BOOLEAN);
    }

    /** Perkiraan memori untuk memuat, memutar, dan menyalin bitmap (4 byte per piksel). */
    private static function memoryAllows(int $width, int $height): bool
    {
        $need = (int) ($width * $height * 4 * 2.5);
        $free = self::iniBytes('memory_limit') - memory_get_usage(true);

        return $free > $need;
    }

    /** GIF animasi punya lebih dari satu blok kontrol grafis. */
    private static function isAnimatedGif(string $path): bool
    {
        $data = @file_get_contents($path);
        if ($data === false) {
            return true; // tidak bisa dipastikan: jangan dikonversi
        }

        return preg_match_all('#\x00\x21\xF9\x04.{4}\x00[\x2C\x21]#s', $data) > 1;
    }

    private static function cleanName(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        $name = (string) preg_replace('/\p{Cc}+/u', '', $name);

        return mb_substr(trim($name), 0, 255);
    }

    private static function uploadError(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ukuran melebihi batas server (' . self::humanSize(self::maxBytes()) . ').',
            UPLOAD_ERR_PARTIAL => 'Upload terputus, coba lagi.',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file dipilih.',
            default => 'Upload gagal di server (kode ' . $code . ').',
        };
    }

    private static function iniBytes(string $key): int
    {
        $value = trim((string) ini_get($key));
        $number = (int) $value;
        $number *= match (strtolower(substr($value, -1))) {
            'g' => 1073741824,
            'm' => 1048576,
            'k' => 1024,
            default => 1,
        };

        return $number > 0 ? $number : PHP_INT_MAX;
    }
}