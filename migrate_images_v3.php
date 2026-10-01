<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Storage;
use App\Models\BlogPostTranslation;
use App\Models\Catalog\EducationPage;
use App\Models\Supabase\LandingPage;
use App\Models\Supabase\PageBlock;

$log = [];
$s3 = Storage::disk('s3');

function migrateHtmlImages($html, $newFolder, $s3, &$log, &$changed) {
    if (empty($html)) return $html;
    
    $prefixes = [
        'dainely/media/blogs/',
        'dainely/media/education/',
        'optimized/editor/',
        'images/'
    ];
    
    foreach ($prefixes as $prefix) {
        $pattern = '/(https?:\/\/[^\s"\'<]+)?\/?' . preg_quote($prefix, '/') . '([a-zA-Z0-9_.-]+)/i';
        
        $html = preg_replace_callback($pattern, function($matches) use ($prefix, $newFolder, $s3, &$log, &$changed) {
            $baseDomain = $matches[1] ?? ''; // e.g. https://media.dainely.com/dainely-media/
            $filename = $matches[2];
            
            $oldPath = $prefix . $filename;
            $newPath = $newFolder . '/' . $filename;
            
            if ($oldPath !== $newPath) {
                try {
                    if ($s3->exists($oldPath)) {
                        if (!$s3->exists($newPath)) {
                            $s3->copy($oldPath, $newPath);
                            $log[] = "Copied $oldPath -> $newPath";
                        }
                        $changed = true;
                        
                        // We need to return the full new URL or the correct relative path
                        // If it has domain, replace the prefix inside the full URL
                        $fullOldStr = $matches[0];
                        return str_replace($oldPath, $newPath, $fullOldStr);
                    }
                } catch (\Exception $e) {
                    $log[] = "Error copying $oldPath: " . $e->getMessage();
                }
            }
            
            return $matches[0];
        }, $html);
    }
    
    return $html;
}

// 1. Blogs
echo "Migrating Blogs...\n";
foreach (BlogPostTranslation::all() as $blog) {
    $changed = false;
    $newContent = migrateHtmlImages($blog->content, 'blogs', $s3, $log, $changed);
    if ($changed) {
        $blog->content = $newContent;
        $blog->save();
        echo "Updated Blog ID: {$blog->id}\n";
    }
}

// 2. Education Pages
echo "Migrating Education Pages...\n";
foreach (EducationPage::all() as $edu) {
    $changed = false;
    $edu->hero_description = migrateHtmlImages($edu->hero_description, 'education', $s3, $log, $changed);
    $edu->treatments_description = migrateHtmlImages($edu->treatments_description, 'education', $s3, $log, $changed);
    
    $items = $edu->content_items;
    if (is_array($items)) {
        foreach ($items as &$item) {
            if (isset($item['label'])) {
                $item['label'] = migrateHtmlImages($item['label'], 'education', $s3, $log, $changed);
            }
            if (isset($item['description'])) {
                $item['description'] = migrateHtmlImages($item['description'], 'education', $s3, $log, $changed);
            }
            if (isset($item['content'])) {
                $item['content'] = migrateHtmlImages($item['content'], 'education', $s3, $log, $changed);
            }
        }
        $edu->content_items = $items;
    }
    
    if ($changed) {
        $edu->save();
        echo "Updated Education Page ID: {$edu->id}\n";
    }
}

// 3. Landing Pages
echo "Migrating Landing Pages...\n";
foreach (LandingPage::with('pageBlocks')->get() as $page) {
    $folder = 'landing-pages/' . $page->slug;
    foreach ($page->pageBlocks as $block) {
        $changed = false;
        $newContent = migrateHtmlImages($block->content, $folder, $s3, $log, $changed);
        if ($changed) {
            $block->content = $newContent;
            $block->save();
            echo "Updated Block ID: {$block->id} for Landing Page: {$page->slug}\n";
        }
    }
}

echo "\nMigration Complete!\n";
file_put_contents('migration_log_v3.txt', implode("\n", $log));