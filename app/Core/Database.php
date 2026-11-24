<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;
use Throwable;

final class Database
{
    private static ?PDO $pdo = null;

    /** Koneksi dibuat lazy: halaman tanpa query tidak menyentuh DB. */
    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $c = (array) Config::get('database');

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $c['host'] ?? '127.0.0.1',
                (int) ($c['port'] ?? 3306),
                $c['name'] ?? '',
                $c['charset'] ?? 'utf8mb4'
            );

            try {
                $pdo = new PDO($dsn, (string) ($c['user'] ?? ''), (string) ($c['pass'] ?? ''), [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);

                // Samakan zona waktu sesi DB dengan PHP (format +07:00, aman dari injeksi)
                $pdo->exec("SET time_zone = '" . date('P') . "'");

                self::$pdo = $pdo;
            } catch (PDOException $e) {
                // Bungkus supaya detail kredensial tidak ikut terbawa
                throw new RuntimeException('Koneksi database gagal. Periksa konfigurasi .env.');
            }
        }

        return self::$pdo;
    }

    /** Jalankan beberapa query dalam satu transaksi. */
    public static function transaction(callable $callback): mixed
    {
        $pdo = self::connection();
        $pdo->beginTransaction();

        try {
            $result = $callback($pdo);
            $pdo->commit();

            return $result;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}