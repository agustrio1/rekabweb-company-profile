<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;
use Throwable;

final class Migrator
{
    private const BOOTSTRAP = '000_create_migrations';

    public function __construct(
        private readonly PDO $pdo,
        private readonly string $dir,
    ) {
    }

    /** @return string[] nama migration (tanpa .sql), urut */
    public function files(): array
    {
        $names = [];

        foreach (glob($this->dir . '/*.sql') ?: [] as $path) {
            $base = basename($path, '.sql');
            if (preg_match('/^\d{3}_[a-z0-9_]+$/', $base)) {
                $names[] = $base;
            }
        }

        sort($names, SORT_STRING);

        return $names;
    }

    /** @return array<string, int> nama => batch */
    public function applied(): array
    {
        if (!$this->tableExists()) {
            return [];
        }

        $applied = [];
        foreach ($this->pdo->query('SELECT migration, batch FROM migrations')->fetchAll() as $row) {
            $applied[$row['migration']] = (int) $row['batch'];
        }

        return $applied;
    }

    /** @return array<int, array{name: string, batch: ?int}> */
    public function status(): array
    {
        $applied = $this->applied();
        $rows = [];

        foreach ($this->files() as $name) {
            $rows[] = ['name' => $name, 'batch' => $applied[$name] ?? null];
        }

        return $rows;
    }

    /** @return string[] migration yang berhasil dijalankan */
    public function run(): array
    {
        $locked = (int) $this->pdo->query("SELECT GET_LOCK('cp_migrate', 5)")->fetchColumn();
        if ($locked !== 1) {
            throw new RuntimeException('Migration sedang dijalankan oleh proses lain.');
        }

        try {
            return $this->runPending();
        } finally {
            $this->pdo->query("SELECT RELEASE_LOCK('cp_migrate')")->fetchColumn();
        }
    }

    /** @return string[] */
    private function runPending(): array
    {
        $bootstrapped = $this->ensureMigrationsTable();

        $applied = $this->applied();
        $pending = array_values(array_filter(
            $this->files(),
            static fn (string $name): bool => !isset($applied[$name])
        ));

        if ($pending === [] && !$bootstrapped) {
            return [];
        }

        $batch = (int) $this->pdo->query('SELECT COALESCE(MAX(batch), 0) FROM migrations')->fetchColumn() + 1;
        $record = $this->pdo->prepare(
            'INSERT INTO migrations (migration, batch) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE batch = VALUES(batch)'
        );

        $ran = [];

        // Tabel migrations baru dibuat oleh runner ini: catat di batch yang sama
        if ($bootstrapped) {
            $record->execute([self::BOOTSTRAP, $batch]);
            $ran[] = self::BOOTSTRAP;
        }

        foreach ($pending as $name) {
            try {
                $this->execFile($name);
                $record->execute([$name, $batch]);
            } catch (Throwable $e) {
                throw new RuntimeException("Migration [$name] gagal: " . $e->getMessage(), 0, $e);
            }

            $ran[] = $name;
        }

        return $ran;
    }

    /** @return bool true jika tabel migrations baru saja dibuat */
    private function ensureMigrationsTable(): bool
    {
        if ($this->tableExists()) {
            return false;
        }

        $this->execFile(self::BOOTSTRAP);

        return true;
    }

    private function tableExists(): bool
    {
        return $this->pdo->query("SHOW TABLES LIKE 'migrations'")->fetchColumn() !== false;
    }

    private function execFile(string $name): void
    {
        $path = $this->dir . '/' . $name . '.sql';
        $sql = is_file($path) ? file_get_contents($path) : false;

        if ($sql === false) {
            throw new RuntimeException("File migration tidak terbaca: $name.sql");
        }

        // Buang BOM jika file disimpan editor tertentu
        $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql) ?? $sql;

        foreach (self::splitStatements($sql) as $statement) {
            $this->pdo->exec($statement);
        }
    }

    /**
     * Pecah isi file SQL menjadi statement. Memahami string/identifier bertanda kutip
     * serta komentar (-- dan /* *\/). Cukup untuk migration sederhana (tanpa trigger/procedure).
     *
     * @return string[]
     */
    public static function splitStatements(string $sql): array
    {
        $statements = [];
        $buffer = '';
        $quote = null;
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $next = $sql[$i + 1] ?? '';

            if ($quote !== null) {
                $buffer .= $char;

                if ($char === '\\' && $quote !== '`') {
                    $buffer .= $next;
                    $i++;
                    continue;
                }

                if ($char === $quote) {
                    $quote = null;
                }
                continue;
            }

            if ($char === "'" || $char === '"' || $char === '`') {
                $quote = $char;
                $buffer .= $char;
                continue;
            }

            if ($char === '-' && $next === '-') {
                $newline = strpos($sql, "\n", $i);
                if ($newline === false) {
                    break;
                }
                $i = $newline;
                $buffer .= "\n";
                continue;
            }

            if ($char === '/' && $next === '*') {
                $end = strpos($sql, '*/', $i + 2);
                if ($end === false) {
                    break;
                }
                $i = $end + 1;
                continue;
            }

            if ($char === ';') {
                $statement = trim($buffer);
                if ($statement !== '') {
                    $statements[] = $statement;
                }
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        $statement = trim($buffer);
        if ($statement !== '') {
            $statements[] = $statement;
        }

        return $statements;
    }
}