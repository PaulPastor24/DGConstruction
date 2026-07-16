<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $c = app()->make(App\Http\Controllers\AdminDashboardController::class);
    $r = new Illuminate\Http\Request();
    $user = App\Models\User::where('role', 'engineer')->first();
    if (!$user) { echo "NO ENGINEER USER\n"; exit; }
    $r->setUserResolver(fn() => $user);
    $m = new ReflectionMethod($c, 'reportsData');
    $m->setAccessible(true);
    $resp = $m->invoke($c, $r);
    $data = $resp->getData();
    echo 'OK total=' . $data->pagination->total . "\n";
} catch (\Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
