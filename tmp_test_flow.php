<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Find an engineer user
$engineer = App\Models\User::where('role', 'engineer')->first();
if (!$engineer) { echo "NO ENGINEER\n"; exit; }
echo "Engineer: {$engineer->user_id} role={$engineer->role}\n";

// Find a pending report
$report = App\Models\Report::where('approval_status', 'pending')->first();
if (!$report) { echo "NO PENDING REPORT\n"; exit; }
$rid = $report->report_id;
echo "Pending report id={$rid}\n";

function hit($kernel, $method, $uri, $engineer, $data = null) {
    $request = Illuminate\Http\Request::create($uri, $method, $data ?? [], [], [], [
        'HTTP_ACCEPT' => 'application/json',
    ]);
    $request->setUserResolver(fn() => $engineer);
    try {
        $response = $kernel->handle($request);
        $status = $response->getStatusCode();
        $body = $response->getContent();
        $json = json_decode($body, true);
        echo "[$method] $uri -> HTTP $status\n";
        if ($status >= 400) {
            echo "  BODY(first 300): " . substr(strip_tags($body), 0, 300) . "\n";
        } elseif (is_array($json)) {
            echo "  JSON keys: " . implode(',', array_keys($json)) . "\n";
            if (isset($json['pagination'])) echo "  total=" . $json['pagination']['total'] . "\n";
            if (isset($json['success'])) echo "  success=" . var_export($json['success'], true) . "\n";
        }
        $kernel->terminate($request, $response);
        return $response;
    } catch (\Throwable $e) {
        echo "[$method] $uri -> EXCEPTION: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
        return null;
    }
}

hit($kernel, 'GET', '/admin/reports/data', $engineer);
echo "---- approve ----\n";
hit($kernel, 'POST', "/admin/reports/{$rid}/approve", $engineer, [
    'approval_remarks' => '',
    'accomplishment_percentage' => 50,
    '_token' => csrf_token(),
]);
echo "---- data after approve ----\n";
hit($kernel, 'GET', '/admin/reports/data', $engineer);
