<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ErrorHandler;
use Throwable;

final class Settings
{
    /** @var array<string, string>|null */
    private static ?array $cache = null;

    /** @return array<string, array{label: string, fields: array<string, array>}> */
    public static function groups(): array
    {
        return (array) config('settings.groups', []);
    }

    /** @return array<string, array> key => definisi field */
    public static function fields(): array
    {
        $all = [];
        foreach (self::groups() as $group) {
            foreach ($group['fields'] as $key => $field) {
                $all[$key] = $field;
            }
        }

        return $all;
    }

    /** Semua nilai: default dari schema, ditimpa nilai dari database. */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $values = [];
        foreach (self::fields() as $key => $field) {
            $values[$key] = (string) ($field['default'] ?? '');
        }

        try {
            $rows = Database::connection()
                ->query('SELECT setting_key, setting_value FROM settings')
                ->fetchAll();

            foreach ($rows as $row) {
                if (array_key_exists($row['setting_key'], $values)) {
                    $values[$row['setting_key']] = (string) $row['setting_value'];
                }
            }
        } catch (Throwable $e) {
            // Tabel belum ada (migration belum jalan): pakai default, jangan matikan halaman
            ErrorHandler::log($e);
        }

        return self::$cache = $values;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $all = self::all();

        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    /**
     * Validasi dan bersihkan input pengaturan website.
     *
     * @return array{0: array<string, string>, 1: array<string, string>} [nilai, error per field]
     */
    public static function validate(array $input): array
    {
        return self::validateFields(self::fields(), $input);
    }

    /**
     * Validasi sekumpulan field berdasarkan definisinya (dipakai juga oleh Homepage).
     *
     * @param array<string, array> $fields key => definisi field
     * @return array{0: array<string, string>, 1: array<string, string>} [nilai, error per field]
     */
    public static function validateFields(array $fields, array $input): array
    {
        $values = [];
        $errors = [];

        foreach ($fields as $key => $field) {
            $label = (string) $field['label'];
            $type = (string) ($field['type'] ?? 'text');
            $max = (int) ($field['max'] ?? 255);
            $raw = $input[$key] ?? '';

            if (!is_string($raw) || !mb_check_encoding($raw, 'UTF-8')) {
                $values[$key] = '';
                $errors[$key] = $label . ' berisi karakter yang tidak valid.';
                continue;
            }

            $v = str_replace(["\r\n", "\r"], "\n", $raw);
            $v = $type === 'textarea' ? trim($v) : trim(str_replace("\n", ' ', $v));
            $v = (string) preg_replace('/[^\P{Cc}\n]+/u', '', $v); // buang karakter kontrol

            $tooLong = mb_strlen($v) > $max;
            $values[$key] = mb_substr($v, 0, $max);

            if ($v === '') {
                if (!empty($field['required'])) {
                    $errors[$key] = $label . ' wajib diisi.';
                }
                continue;
            }

            if ($tooLong) {
                $errors[$key] = $label . ' maksimal ' . $max . ' karakter.';
                continue;
            }

            $error = null;

            switch ($type) {
                case 'email':
                    $v = strtolower($v);
                    if (filter_var($v, FILTER_VALIDATE_EMAIL) === false) {
                        $error = 'Format email tidak valid.';
                    }
                    break;

                case 'phone':
                    if (preg_match('/^\+?[0-9][0-9\s\-()]{5,29}$/', $v) !== 1) {
                        $error = 'Format nomor telepon tidak valid.';
                    }
                    break;

                case 'whatsapp':
                    $digits = (string) preg_replace('/\D+/', '', $v);
                    if (str_starts_with($digits, '0')) {
                        $digits = '62' . substr($digits, 1);
                    }
                    if (preg_match('/^62[0-9]{8,13}$/', $digits) !== 1) {
                        $error = 'Gunakan nomor Indonesia, contoh 081234567890 atau 6281234567890.';
                    } else {
                        $v = $digits;
                    }
                    break;

                case 'url':
                    if (!self::isHttpUrl($v)) {
                        $error = 'Harus berupa URL lengkap yang diawali http:// atau https://.';
                    }
                    break;

                case 'link':
                    $internal = str_starts_with($v, '/')
                        && !str_starts_with($v, '//')
                        && preg_match('#^/[A-Za-z0-9_\-/.?=&\#%]*$#', $v) === 1;
                    if (!$internal && !self::isHttpUrl($v)) {
                        $error = 'Isi path internal (contoh /kontak) atau URL lengkap https://…';
                    }
                    break;

                case 'select':
                    if (!array_key_exists($v, (array) ($field['options'] ?? []))) {
                        $error = 'Pilihan tidak valid.';
                    }
                    break;

                case 'image':
                    if (preg_match('/^[0-9]{1,10}$/', $v) !== 1 || Media::find((int) $v) === null) {
                        $error = 'Gambar tidak ditemukan di Media.';
                    }
                    break;

                case 'code':
                    if (preg_match('/^[A-Za-z0-9_\-]+$/', $v) !== 1) {
                        $error = 'Hanya huruf, angka, tanda minus, dan garis bawah.';
                    }
                    break;
            }

            if ($error !== null) {
                $errors[$key] = $error;
                continue;
            }

            $values[$key] = $v;
        }

        return [$values, $errors];
    }

    /** Simpan nilai (hanya key yang ada di schema). */
    public static function save(array $values): void
    {
        $known = self::fields();
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );

        $pdo->beginTransaction();

        try {
            foreach ($values as $key => $value) {
                if (isset($known[$key])) {
                    $stmt->execute([$key, (string) $value]);
                }
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        self::$cache = null;
    }

    /** URL gambar dari pengaturan bertipe image, atau '' kalau kosong atau sudah tidak ada. */
    public static function imageUrl(string $key): string
    {
        $id = (int) (self::get($key) ?? 0);
        if ($id < 1) {
            return '';
        }

        $media = Media::find($id);

        return $media === null ? '' : Media::url((string) $media['path']);
    }

    private static function isHttpUrl(string $value): bool
    {
        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $parts = parse_url($value);

        return is_array($parts)
            && in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)
            && ($parts['host'] ?? '') !== '';
    }
}