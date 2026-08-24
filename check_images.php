<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$blocks = \App\Models\Supabase\PageBlock::where('block_type', 'lifestyle')->get();
foreach ($blocks as $b) {
    echo $b->id . ': ' . $b->title . ' -> ' . substr($b->content, 0, 150) . "\n";
}
