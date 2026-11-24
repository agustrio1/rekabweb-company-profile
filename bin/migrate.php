<?php

declare(strict_types=1);

use App\Core\Database;
use App\Core\Migrator;

// Hanya boleh dijalankan dari command line
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');

require BASE_PATH . '/app/bootstrap.php';

$command = $argv[1] ?? 'status';

try {
    $migrator = new Migrator(Database::connection(), BASE_PATH . '/database/migrations');

    switch ($command) {
        case 'status':
            foreach ($migrator->status() as $row) {
                $mark = $row['batch'] === null ? ' ' : 'x';
                $batch = $row['batch'] === null ? 'belum dijalankan' : 'batch ' . $row['batch'];
                echo "[$mark] {$row['name']}  ($batch)\n";
            }
            break;

        case 'run':
            $ran = $migrator->run();

            if ($ran === []) {
                echo "Tidak ada migration baru.\n";
                break;
            }

            foreach ($ran as $name) {
                echo "Dijalankan: $name\n";
            }
            echo count($ran) . " migration selesai.\n";
            break;

        default:
            fwrite(STDERR, "Perintah tidak dikenal: $command\nGunakan: php bin/migrate.php [status|run]\n");
            exit(1);
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'Gagal: ' . $e->getMessage() . "\n");
    exit(1);
}