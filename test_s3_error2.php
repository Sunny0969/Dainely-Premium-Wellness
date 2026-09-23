<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

config(['filesystems.disks.s3.throw' => true]);
config(['filesystems.disks.s3.http' => ['verify' => false]]);

try {
    $result = \Illuminate\Support\Facades\Storage::disk('s3')->put('test.txt', 'Hello World', 'public');
    echo "Success: " . ($result ? 'true' : 'false') . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}