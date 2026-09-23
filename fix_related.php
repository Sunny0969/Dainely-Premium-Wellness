<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::connection('supabase')->table('related_content')
    ->where('source_type', 'product')
    ->where('source_id', 2)
    ->where('related_type', 'blog')
    ->whereIn('related_id', [1, 4])
    ->delete();

echo "Deleted old broken related_content links.\n";