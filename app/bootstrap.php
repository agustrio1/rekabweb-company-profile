<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Env;
use App\Core\ErrorHandler;
use App\Core\Router;

if (PHP_VERSION_ID < 80200) {
    http_response_code(500);
    exit('Aplikasi ini membutuhkan PHP 8.2 atau lebih baru.');
}

// Autoloader PSR-4 sederhana: App\Foo\Bar => app/Foo/Bar.php
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = BASE_PATH . '/app/' . $relative . '.php';

    if (is_file($file)) {
        require $file;
    }
});

// Dependency Composer (HTMLPurifier, PHPMailer) jika sudah ada
if (is_file(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
}

require BASE_PATH . '/app/Helpers/helpers.php';
require BASE_PATH . '/app/Helpers/web.php';

Env::load(BASE_PATH . '/.env');

date_default_timezone_set((string) Config::get('app.timezone', 'Asia/Jakarta'));

ErrorHandler::register();

// Alias middleware: 'web', 'auth', 'guest', 'can'
Router::alias((array) Config::get('middleware', []));