<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;
use Throwable;

/** Inquiry dari calon klien (tabel inquiries). */
final class Inquiries
{
    public const PER_PAGE = 20;
    private const THROTTLE_MAX = 5; // inquiry per IP per jam

    /** @return array<string, string> kode => label */
    public static function statuses(): array
    {
        return [
            'new' => 'Baru',
            'contacted' => 'Sudah dihubungi',
            'closed' => 'Selesai',
            'spam' => 'Spam',
        ];
    }

    /** Field formulir publik (divalidasi lewat Settings::validateFields). */
    public static function publicFields(): array
    {
        return [
            'name' => ['label' => 'Nama', 'type' => 'text', 'max' => 100, 'required' => true],
            'email' => ['label' => 'Email', 'type' => 'email', 'max' => 190, 'required' => true],
            'whatsapp' => ['label' => 'Nomor WhatsApp', 'type' => 'whatsapp', 'max' => 25, 'required' => true],
            'company' => ['label' => 'Perusahaan', 'type' => 'text', 'max' => 100],
            'need_type' => ['label' => 'Jenis kebutuhan', 'type' => 'text', 'max' => 100],
            'message' => ['label' => 'Pesan', 'type' => 'textarea', 'max' => 2000, 'required' => true],
        ];
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT i.*, u.name AS handler
             FROM inquiries i LEFT JOIN users u ON u.id = i.handled_by
             WHERE i.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function count(string $q = '', string $status = ''): int
    {
        [$where, $params] = self::where($q, $status);
        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM inquiries i ' . $where);
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
            "SELECT i.id, i.name, i.email, i.whatsapp, i.company, i.need_type, i.status, i.created_at
             FROM inquiries i
             $where
             ORDER BY i.created_at DESC, i.id DESC
             LIMIT $limit OFFSET $offset"
        );
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /** Jumlah inquiry per status. @return array<string, int> */
    public static function statusCounts(): array
    {
        $out = array_fill_keys(array_keys(self::statuses()), 0);

        $rows = Database::connection()
            ->query('SELECT status, COUNT(*) AS n FROM inquiries GROUP BY status')
            ->fetchAll();

        foreach ($rows as $row) {
            if (isset($out[$row['status']])) {
                $out[$row['status']] = (int) $row['n'];
            }
        }

        return $out;
    }

    /** Jumlah inquiry baru (untuk badge menu). Aman kalau tabel belum ada. */
    public static function newCount(): int
    {
        try {
            return (int) Database::connection()
                ->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")
                ->fetchColumn();
        } catch (Throwable) {
            return 0;
        }
    }

    /** Inquiry terbaru. @return array<int, array> */
    public static function recent(int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));

        return Database::connection()->query(
            "SELECT id, name, need_type, status, created_at
             FROM inquiries
             ORDER BY created_at DESC, id DESC
             LIMIT $limit"
        )->fetchAll();
    }

    /**
     * Validasi kiriman formulir publik.
     *
     * @param array<string, mixed> $in
     * @return array{0: array<string, string>, 1: array<string, string>} [nilai bersih, error per field]
     */
    public static function validatePublic(array $in): array
    {
        [$v, $e] = Settings::validateFields(self::publicFields(), $in);

        if (!isset($e['message']) && $v['message'] !== '' && mb_strlen($v['message']) < 10) {
            $e['message'] = 'Pesan minimal 10 karakter.';
        }

        return [$v, $e];
    }

    /** True kalau IP ini sudah mengirim terlalu banyak inquiry dalam satu jam terakhir. */
    public static function throttled(string $ip): bool
    {
        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) FROM inquiries WHERE ip = ? AND created_at > (NOW() - INTERVAL 1 HOUR)'
        );
        $stmt->execute([substr($ip, 0, 45)]);

        return (int) $stmt->fetchColumn() >= self::THROTTLE_MAX;
    }

    /** @param array<string, string> $v hasil validatePublic() tanpa error */
    public static function create(array $v, string $ip): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO inquiries (name, email, whatsapp, company, need_type, message, ip)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $v['name'], $v['email'], $v['whatsapp'], $v['company'], $v['need_type'],
            $v['message'], substr($ip, 0, 45),
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Validasi perubahan status dan catatan dari admin.
     *
     * @param array<string, mixed> $in
     * @return array{0: array<string, string>, 1: array<string, string>}
     */
    public static function validateUpdate(array $in): array
    {
        $errors = [];

        $status = is_string($in['status'] ?? null) ? (string) $in['status'] : '';
        if (!isset(self::statuses()[$status])) {
            $errors['status'] = 'Status tidak valid.';
            $status = 'new';
        }

        [$note, $noteErrors] = Settings::validateFields(
            ['admin_note' => ['label' => 'Catatan internal', 'type' => 'textarea', 'max' => 2000]],
            $in
        );
        $errors += $noteErrors;

        return [['status' => $status, 'admin_note' => $note['admin_note']], $errors];
    }

    public static function update(int $id, array $v, ?int $userId): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE inquiries SET status = ?, admin_note = ?, handled_by = ? WHERE id = ?'
        );
        $stmt->execute([$v['status'], $v['admin_note'], $userId, $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM inquiries WHERE id = ?');
        $stmt->execute([$id]);
    }

    /** @return array{0: string, 1: array<int, string>} */
    private static function where(string $q, string $status): array
    {
        $clauses = [];
        $params = [];

        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
            $clauses[] = '(i.name LIKE ? OR i.email LIKE ? OR i.company LIKE ? OR i.whatsapp LIKE ?)';
            array_push($params, $like, $like, $like, $like);
        }
        if (isset(self::statuses()[$status])) {
            $clauses[] = 'i.status = ?';
            $params[] = $status;
        }

        return [$clauses === [] ? '' : 'WHERE ' . implode(' AND ', $clauses), $params];
    }
}