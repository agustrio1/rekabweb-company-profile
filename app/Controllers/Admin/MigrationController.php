<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\ErrorHandler;
use App\Core\Migrator;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\ServerCheck;
use Throwable;

final class MigrationController
{
    public function index(Request $request): Response
    {
        $rows = $this->migrator()->status();
        $pending = count(array_filter($rows, static fn (array $row): bool => $row['batch'] === null));

        return view('admin/migrations', [
            'rows' => $rows,
            'pending' => $pending,
            'checks' => ServerCheck::run($request->isSecure()),
        ]);
    }

    public function run(): Response
    {
        try {
            $ran = $this->migrator()->run();

            Session::flash(
                'success',
                $ran === []
                    ? 'Tidak ada migration baru.'
                    : count($ran) . ' migration dijalankan: ' . implode(', ', $ran)
            );
        } catch (Throwable $e) {
            ErrorHandler::log($e);
            Session::flash('error', $e->getMessage());
        }

        return redirect_route('admin.migrations');
    }

    private function migrator(): Migrator
    {
        return new Migrator(Database::connection(), base_path('database/migrations'));
    }
}