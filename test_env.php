<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, ['.env']);
$dotenv->load();

echo 'USERNAME: [' . getenv('DB_USERNAME') . ']' . PHP_EOL;
echo 'PASSWORD: [' . getenv('DB_PASSWORD') . ']' . PHP_EOL;
echo 'HOST: [' . getenv('DB_HOST') . ']' . PHP_EOL;
echo 'DATABASE: [' . getenv('DB_DATABASE') . ']' . PHP_EOL;
echo 'CONNECTION: [' . getenv('DB_CONNECTION') . ']' . PHP_EOL;

echo PHP_EOL . '--- .env file contents ---' . PHP_EOL;
echo file_get_contents('.env');
