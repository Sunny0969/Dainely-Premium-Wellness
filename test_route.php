<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/dainely-admin-panel/editor-upload', 'POST');
// We need to attach a file to the request.
// Or just let's check if the route exists:
$route = app('router')->getRoutes()->match($request);
echo $route->getActionName() . "\n";
