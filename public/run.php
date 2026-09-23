<?php
require __DIR__."/../vendor/autoload.php";
$app = require_once __DIR__."/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "Running migrations...<br>";
$kernel->call("migrate", ["--force" => true]);
echo nl2br($kernel->output()) . "<br><br>";

echo "Clearing caches...<br>";
$kernel->call("optimize:clear");
$kernel->call("config:cache");
$kernel->call("view:cache");
echo nl2br($kernel->output()) . "<br><br>";

echo "<h2>All done! Speed optimizations applied successfully.</h2>";

