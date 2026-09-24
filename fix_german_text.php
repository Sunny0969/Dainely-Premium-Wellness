<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$page = App\Models\Catalog\EducationPage::where("slug", "active-50-plus")->where("locale", "en")->first();
if (!$page) {
    echo "Page not found\n";
    exit;
}
foreach ($page->pageBlocks as $block) {
    echo "ID: " . $block->id . "\n";
    echo "Locale: " . $block->locale . "\n";
    echo "Title: " . $block->title . "\n";
    if (strpos($block->content, 'Medizinischer Haftungsausschluss') !== false || strpos($block->title, 'Medizinischer Haftungsausschluss') !== false) {
        echo "FOUND GERMAN TEXT IN THIS BLOCK\n";
        
        // FIX IT
        $block->title = "Medical Disclaimer";
        $block->content = "<p><em>The information provided on this site is for general educational and informational purposes only. It is not intended to diagnose, treat, cure, or prevent any disease, condition, or injury, and it should not be considered medical advice.</em></p><p><em>Physical activity and exercise needs vary from person to person. Consult a qualified healthcare professional if you have a medical condition, recent injury, significant mobility or balance concerns, or questions about the appropriate type or amount of activity for you.</em></p><p><em>Dainely products are designed for general support and comfort and are not intended to replace professional medical evaluation, diagnosis, or treatment.</em></p>";
        $block->save();
        echo "FIXED!\n";
    }
}
