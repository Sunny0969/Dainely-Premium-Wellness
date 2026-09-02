<?php
// Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain');

echo "Laravel base_path: " . base_path() . "\n";
echo "Laravel public_path: " . public_path() . "\n";
echo "Current __DIR__: " . __DIR__ . "\n\n";

// List files in current folder's images
$currentDirImages = __DIR__ . '/images';
echo "Files in " . $currentDirImages . ":\n";
if (is_dir($currentDirImages)) {
    $files = scandir($currentDirImages);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo " - " . $file . " (" . filesize($currentDirImages . '/' . $file) . " bytes)\n";
        }
    }
} else {
    echo "Directory does not exist!\n";
}

echo "\nFiles in public_path('images'):\n";
$publicPathImages = public_path('images');
if (is_dir($publicPathImages)) {
    $files = scandir($publicPathImages);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo " - " . $file . " (" . filesize($publicPathImages . '/' . $file) . " bytes)\n";
        }
    }
} else {
    echo "Directory does not exist!\n";
}
