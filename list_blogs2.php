<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$blogs = DB::connection('supabase')->table('blog_posts')->get();
echo "Total blogs: " . count($blogs) . "\n";
$translations = DB::connection('supabase')->table('blog_post_translations')->get();
foreach($translations as $t) {
    echo "ID " . $t->blog_post_id . " - " . $t->locale . " - " . $t->slug . "\n";
}