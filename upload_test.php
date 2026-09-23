<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\MediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

echo "Downloading image...\n";
$imageUrl = "https://images.unsplash.com/photo-1504279664890-136a82299d1b?w=600&auto=format&fit=crop&q=60";
$tempPath = sys_get_temp_dir() . '/pickleball.jpg';
file_put_contents($tempPath, file_get_contents($imageUrl));

echo "Simulating UploadedFile...\n";
$file = new UploadedFile($tempPath, 'pickleball.jpg', 'image/jpeg', null, true);

echo "Uploading via MediaService to IDrive E2...\n";
$media = MediaService::upload($file, 'education', 'Pickleball Injury Prevention');

echo "Media ID: " . $media->id . "\n";
echo "Original Path: " . $media->original_path . "\n";
echo "Responsive Widths: " . json_encode($media->responsive_widths) . "\n";

echo "Attaching to Education Page...\n";
// Find the page
$page = DB::table('education_pages')->where('slug', 'pickleball-injury-prevention')->first();
if ($page) {
    DB::table('education_pages')->where('id', $page->id)->update(['hero_media_id' => $media->id]);
    echo "Successfully attached to page ID: " . $page->id . "\n";
} else {
    // If exact slug not found, let's just find any page with pickleball in title or slug
    $page = DB::table('education_pages')->where('slug', 'like', '%pickleball%')->first();
    if ($page) {
        DB::table('education_pages')->where('id', $page->id)->update(['hero_media_id' => $media->id]);
        echo "Attached to alternative page ID: " . $page->id . " (slug: " . $page->slug . ")\n";
    } else {
        echo "Could not find pickleball education page!\n";
    }
}

echo "Verifying E2 Storage...\n";
if (Storage::disk('s3')->exists($media->original_path)) {
    echo "SUCCESS: Original file exists on E2!\n";
} else {
    echo "ERROR: Original file NOT found on E2.\n";
}
if (Storage::disk('s3')->exists($media->storage_path)) {
    echo "SUCCESS: Optimized file exists on E2!\n";
} else {
    echo "ERROR: Optimized file NOT found on E2.\n";
}
