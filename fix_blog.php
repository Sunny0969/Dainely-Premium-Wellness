<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$post = App\Models\BlogPost::where("slug", "why-mobility-matters-after-50-7-simple-ways-to-keep-moving-with-confidence")->first();
if ($post) {
    $content = $post->content;
    
    // Replace first image with women-walking.jpg
    $content = preg_replace("/https:\/\/media\.dainely\.com\/dainely-media\/optimized\/editor\/1789759755-[^.]+\.jpg/", "/images/women-walking.jpg", $content);
    
    // Replace second image with lifestyle-everyday-movement.webp
    $content = preg_replace("/https:\/\/media\.dainely\.com\/dainely-media\/optimized\/editor\/1789759756-[^.]+\.jpg/", "/images/lifestyle-everyday-movement.webp", $content);
    
    // Replace third image with lifestyle-dainely-in-motion.png
    $content = preg_replace("/https:\/\/media\.dainely\.com\/dainely-media\/optimized\/editor\/1789759757-[^.]+\.jpg/", "/images/lifestyle-dainely-in-motion.png", $content);
    
    // Replace any remaining media.dainely.com images with a fallback
    $content = preg_replace("/https:\/\/media\.dainely\.com\/dainely-media\/optimized\/editor\/[^\"]+/", "/images/hero-lifestyle.png", $content);

    $post->content = $content;
    $post->save();
    echo "Blog post images replaced successfully!\n";
} else {
    echo "Blog post not found!\n";
}

