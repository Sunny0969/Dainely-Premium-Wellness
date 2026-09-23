<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$slug = 'a-built-for-the-moments';

$lp = \App\Models\LandingPage::where('slug', $slug)->first();
if ($lp) echo "LandingPage found\n";

$blog = \App\Models\BlogPostTranslation::where('slug', $slug)->first();
if ($blog) echo "Blog found\n";

$page = \App\Models\Page::where('slug', $slug)->first();
if ($page) echo "Page found\n";