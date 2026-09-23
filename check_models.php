<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Blogs: " . \App\Models\BlogPost::count() . "\n";
echo "Education: " . \App\Models\EducationPage::count() . "\n";

$blog = \App\Models\BlogPost::first();
if ($blog) {
    echo "Blog Image: " . $blog->featured_image . "\n";
}

$edu = \App\Models\EducationPage::first();
if ($edu) {
    echo "Edu Image: " . $edu->hero_image . "\n";
}