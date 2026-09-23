<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$related = \App\Models\Supabase\RelatedContent::all();
foreach($related as $r) {
    echo $r->id . " - " . $r->source_type . " " . $r->source_id . " -> " . $r->related_type . " " . $r->related_id . "\n";
}