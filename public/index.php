<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Router;

// Root proyek = folder yang berisi app/, config/, routes/, storage/.
// Layout standar: public/ ada di dalam root proyek.
// Layout public_html: app/ dkk berada satu level di atas public_html,
// jadi dirname(__DIR__) tetap benar tanpa diubah.
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);

require BASE_PATH . '/app/bootstrap.php';
require BASE_PATH . '/routes/web.php';

Router::dispatch(Request::capture())->send();