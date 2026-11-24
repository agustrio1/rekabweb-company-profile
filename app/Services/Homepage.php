<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ErrorHandler;
use Throwable;

final class Homepage
{
    /** @var array<string, array>|null */
    private static ?array $cache = null;

    /** @return array<string, array> key => definisi dari config */
    public static function definitions(): array
    {
        return (array) config('homepage.sections', []);
    }

    /**
     * Semua section, berurutan. Tiap item: key, label, active, order, data.
     * Belum pernah disimpan = urutan dan isi bawaan dari config.
     *
     * @return array<string, array>
     */
    public static function sections(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $stored = [];
        try {
            $rows = Database::connection()
                ->query('SELECT section_key, is_active, sort_order, content FROM homepage_sections')
                ->fetchAll();
            foreach ($rows as $row) {
                $stored[(string) $row['section_key']] = $row;
            }
        } catch (Throwable $e) {
            // Tabel belum ada (migration belum jalan): pakai bawaan
            ErrorHandler::log($e);
        }

        $out = [];
        $i = 0;
        foreach (self::definitions() as $key => $def) {
            $row = $stored[$key] ?? null;
            $json = [];
            if ($row !== null && is_string($row['content'])) {
                $decoded = json_decode($row['content'], true);
                $json = is_array($decoded) ? $decoded : [];
            }

            $out[$key] = [
                'key' => $key,
                'label' => (string) $def['label'],
                'active' => $row !== null ? (int) $row['is_active'] === 1 : (bool) ($def['default_active'] ?? true),
                'order' => $row !== null ? (int) $row['sort_order'] : 1000 + $i,
                'data' => self::merge($def, $json),
            ];
            $i++;
        }

        uasort($out, static fn (array $a, array $b): int => $a['order'] <=> $b['order']);

        return self::$cache = $out;
    }

    /** Hanya section aktif, berurutan. Dipakai halaman publik (PHASE 8). */
    public static function active(): array
    {
        return array_filter(self::sections(), static fn (array $s): bool => $s['active']);
    }

    /**
     * Validasi input form.
     *
     * @return array{0: array<string, array>, 1: array<string, array>} [baris siap simpan, error per section]
     */
    public static function validate(array $input, mixed $order, mixed $active): array
    {
        $defs = self::definitions();
        $activeIn = is_array($active) ? $active : [];
        $rows = [];
        $errors = [];

        foreach (self::normalizeOrder($order) as $position => $key) {
            $def = $defs[$key];
            $in = is_array($input[$key] ?? null) ? $input[$key] : [];
            $isActive = isset($activeIn[$key]);
            $sectionErrors = [];

            [$data, $fieldErrors] = Settings::validateFields($def['fields'], $in);
            if ($fieldErrors !== []) {
                $sectionErrors['f'] = $fieldErrors;
            }

            $rep = $def['repeater'] ?? null;
            if (is_array($rep)) {
                $max = (int) ($rep['max'] ?? 6);
                $min = (int) ($rep['min'] ?? 0);
                $raws = is_array($in['items'] ?? null) ? array_values($in['items']) : [];

                if (count($raws) > $max) {
                    $sectionErrors['g'][] = 'Maksimal ' . $max . ' item.';
                }

                $items = [];
                foreach (array_slice($raws, 0, $max) as $n => $raw) {
                    if (!is_array($raw) || self::blank($raw)) {
                        continue;
                    }
                    [$itemData, $itemErrors] = Settings::validateFields($rep['fields'], $raw);
                    if ($itemErrors !== []) {
                        $sectionErrors['i'][$n] = $itemErrors;
                    }
                    $items[] = $itemData;
                }

                if ($isActive && count($items) < $min) {
                    $sectionErrors['g'][] = 'Isi minimal ' . $min . ' item, atau nonaktifkan section ini.';
                }

                $data['items'] = $items;
            }

            if ($sectionErrors !== []) {
                $errors[$key] = $sectionErrors;
            }

            $rows[$key] = ['active' => $isActive, 'order' => $position + 1, 'data' => $data];
        }

        return [$rows, $errors];
    }

    /** @param array<string, array> $rows hasil validate() tanpa error */
    public static function save(array $rows): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO homepage_sections (section_key, is_active, sort_order, content)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE is_active = VALUES(is_active),
                                     sort_order = VALUES(sort_order),
                                     content = VALUES(content)'
        );

        $pdo->beginTransaction();

        try {
            foreach ($rows as $key => $row) {
                $stmt->execute([
                    $key,
                    $row['active'] ? 1 : 0,
                    (int) $row['order'],
                    json_encode($row['data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                ]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        self::$cache = null;
    }

    /**
     * Isi ulang tampilan form dengan input mentah (setelah validasi gagal).
     *
     * @param array<string, array> $sections hasil sections()
     * @return array<string, array>
     */
    public static function overlay(array $sections, array $old): array
    {
        $defs = self::definitions();
        $oldS = is_array($old['s'] ?? null) ? $old['s'] : [];
        $activeIn = is_array($old['active'] ?? null) ? $old['active'] : [];
        $result = [];

        foreach (self::normalizeOrder($old['order'] ?? []) as $key) {
            $sec = $sections[$key];
            $def = $defs[$key];
            $in = is_array($oldS[$key] ?? null) ? $oldS[$key] : [];

            foreach (array_keys($def['fields']) as $f) {
                $sec['data'][$f] = is_string($in[$f] ?? null) ? $in[$f] : '';
            }

            if (is_array($def['repeater'] ?? null)) {
                $items = [];
                $raws = is_array($in['items'] ?? null) ? array_values($in['items']) : [];
                foreach (array_slice($raws, 0, (int) $def['repeater']['max']) as $raw) {
                    $row = [];
                    foreach (array_keys($def['repeater']['fields']) as $f) {
                        $row[$f] = is_array($raw) && is_string($raw[$f] ?? null) ? $raw[$f] : '';
                    }
                    $items[] = $row;
                }
                $sec['data']['items'] = $items;
            }

            $sec['active'] = isset($activeIn[$key]);
            $result[$key] = $sec;
        }

        return $result;
    }

    /** @return string[] key section berurutan, tanpa duplikat, semua key terdaftar ikut. */
    private static function normalizeOrder(mixed $in): array
    {
        $known = array_keys(self::definitions());
        $out = [];

        foreach (is_array($in) ? $in : [] as $key) {
            if (is_string($key) && in_array($key, $known, true) && !in_array($key, $out, true)) {
                $out[] = $key;
            }
        }
        foreach ($known as $key) {
            if (!in_array($key, $out, true)) {
                $out[] = $key;
            }
        }

        return $out;
    }

    private static function merge(array $def, array $stored): array
    {
        $data = [];
        foreach ($def['fields'] as $f => $field) {
            $data[$f] = is_string($stored[$f] ?? null) ? $stored[$f] : (string) ($field['default'] ?? '');
        }

        $rep = $def['repeater'] ?? null;
        if (is_array($rep)) {
            $source = is_array($stored['items'] ?? null) ? $stored['items'] : (array) ($rep['default'] ?? []);
            $items = [];
            foreach ($source as $raw) {
                if (!is_array($raw)) {
                    continue;
                }
                $row = [];
                foreach (array_keys($rep['fields']) as $f) {
                    $row[$f] = is_string($raw[$f] ?? null) ? $raw[$f] : '';
                }
                $items[] = $row;
            }
            $data['items'] = $items;
        }

        return $data;
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
}