<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
// LOCAL
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// PRODUCTION (uncomment ini, comment LOCAL di atas)
// if (file_exists($maintenance = __DIR__.'/../laravel-app/storage/framework/maintenance.php')) {
//     require $maintenance;
// }
// require __DIR__.'/../laravel-app/vendor/autoload.php';
// $app = require_once __DIR__.'/../laravel-app/bootstrap/app.php';

$app->handleRequest(Request::capture());
