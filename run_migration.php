<?php

// Set up the application
require __DIR__ . '/bootstrap/app.php';

// Create the application instance
$app = require_once __DIR__ . '/bootstrap/app.php';

// Get the Artisan command
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');

// Run the migrate command with force
exit($kernel->call('migrate', ['--force' => true]));
