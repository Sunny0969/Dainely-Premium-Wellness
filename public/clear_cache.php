<?php
require __DIR__."/../vendor/autoload.php";
$app = require_once __DIR__."/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
\Illuminate\Support\Facades\Artisan::call("cache:clear");
\Illuminate\Support\Facades\Artisan::call("view:clear");
echo "<h1>Cache Cleared Successfully!</h1><p>Please refresh your live website.</p>";

