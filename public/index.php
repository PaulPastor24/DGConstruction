<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Automatically use absolute path on Hostinger, and relative path locally on your PC
$corePath = is_dir('/home/u381418166/domains/system.dgconphil.com') 
    ? '/home/u381418166/domains/system.dgconphil.com' 
    : __DIR__.'/..';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $corePath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $corePath . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $corePath . '/bootstrap/app.php';

$app->handleRequest(Request::capture());