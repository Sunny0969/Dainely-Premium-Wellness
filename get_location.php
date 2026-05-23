<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$token = config('square.access_token');
$env   = config('square.environment', 'sandbox');
$base  = $env === 'production'
    ? 'https://connect.squareup.com/v2'
    : 'https://connect.squareupsandbox.com/v2';

$response = \Illuminate\Support\Facades\Http::withHeaders([
    'Authorization'  => 'Bearer ' . $token,
    'Square-Version' => '2024-10-17',
    'Content-Type'   => 'application/json',
])->get($base . '/locations');

echo "Status: " . $response->status() . "\n";
echo "Body: " . $response->body() . "\n";
