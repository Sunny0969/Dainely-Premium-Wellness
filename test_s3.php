<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $result = \Illuminate\Support\Facades\Storage::disk('s3')->put('test.txt', 'Hello World', 'public');
    echo "Success: " . ($result ? 'true' : 'false') . "\n";
    echo "URL: " . \Illuminate\Support\Facades\Storage::disk('s3')->url('test.txt') . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}