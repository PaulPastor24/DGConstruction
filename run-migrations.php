<?php
// Script to run migrations and seeding without interactive prompts

require __DIR__ . '/bootstrap/app.php';

use Illuminate\Database\Console\Migrations\FreshCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

$app = app();
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

// Register commands
$kernel->bootstrap();

// Run migrate:fresh with --seed
$input = new ArrayInput([
    'command' => 'migrate:fresh',
    '--seed' => true,
    '--force' => true,
]);

$output = new BufferedOutput();

try {
    $status = $kernel->handle($input, $output);
    echo $output->fetch();
    echo "Migration and seeding completed with status: $status\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $output->fetch();
}
