<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;
use Throwable;

/** Pertanyaan umum (tabel faqs). */
final class Faqs
{
    private const LIST_LIMIT = 100;

    public static function fields(): array
    {
        return [
            'question' => ['label' => 'Pertanyaan', 'type' => 'text', 'max' => 200, 'required' => true, 'wide' => true],
            'answer' => ['label' => 'Jawaban', 'type' => 'textarea', 'max' => 1500, 'required' => true, 'wide' => true],
            'category' => [
                'label' => 'Kategori', 'type' => 'text', 'max' => 60,
                'placeholder' => 'Contoh: Harga, Proses kerja',
                'hint' => 'Opsional. FAQ dengan kategori sama dikelompokkan di halaman FAQ.',
            ],
        ];
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM faqs WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /** @return array<int, array> */
    public static function all(): array
    {
        $limit = self::LIST_LIMIT;

        return Database::connection()->query(
            "SELECT id, question, category, status, created_by
             FROM faqs
             ORDER BY sort_order ASC, id ASC
             LIMIT $limit"
        )->fetchAll();
    }

    /**
     * @param array<string, mixed> $in
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    public static function validate(array $in, ?array $current, bool $canPublish): array
    {
        [$v, $e] = Settings::validateFields(self::fields(), $in);

        if ($canPublish) {
            $status = is_string($in['status'] ?? null) ? (string) $in['status'] : 'draft';
            if (!in_array($status, ['draft', 'published'], true)) {
                $e['status'] = 'Status tidak valid.';
                $status = 'draft';
            }
        } else {
            $status = (string) ($current['status'] ?? 'draft');
        }
        $v['status'] = $status;

        return [$v, $e];
    }

    public static function create(array $v, ?int $userId): int
    {
        $pdo = Database::connection();
        $next = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM faqs')->fetchColumn();

        $stmt = $pdo->prepare(
            'INSERT INTO faqs (question, answer, category, status, sort_order, created_by, updated_by)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$v['question'], $v['answer'], $v['category'], $v['status'], $next, $userId, $userId]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $v, ?int $userId): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE faqs SET question = ?, answer = ?, category = ?, status = ?, updated_by = ? WHERE id = ?'
        );
        $stmt->execute([$v['question'], $v['answer'], $v['category'], $v['status'], $userId, $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM faqs WHERE id = ?');
        $stmt->execute([$id]);
    }

    /** Geser satu posisi; seluruh urutan dinomori ulang. */
    public static function move(int $id, string $direction): void
    {
        $pdo = Database::connection();
        $ids = array_map(
            'intval',
            $pdo->query('SELECT id FROM faqs ORDER BY sort_order ASC, id ASC')->fetchAll(PDO::FETCH_COLUMN)
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

        $stmt = $pdo->prepare('UPDATE faqs SET sort_order = ?, updated_at = updated_at WHERE id = ?');
        $pdo->beginTransaction();

        try {
            foreach ($ids as $position => $rowId) {
                $stmt->execute([$position + 1, $rowId]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}