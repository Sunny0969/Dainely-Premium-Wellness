<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use App\Models\Catalog\EducationPage;

$s3UrlBase = 'https://media.dainely.com/dainely-media';

function uploadLocalToS3($localPath, $folder = 'images') {
    global $s3UrlBase;
    
    // Remove leading slash if any
    $localPath = ltrim($localPath, '/');
    
    if (str_contains($localPath, 'media.dainely.com')) {
        return $localPath;
    }

    $absolutePath = public_path($localPath);
    if (!file_exists($absolutePath)) {
        if (str_contains($localPath, 'dainely.com/images/')) {
            $parsed = parse_url($localPath, PHP_URL_PATH);
            if ($parsed) {
                $absolutePath = public_path(ltrim($parsed, '/'));
            }
        }
    }

    if (file_exists($absolutePath) && is_file($absolutePath)) {
        $filename = basename($absolutePath);
        $uniqueFilename = time() . '-' . Str::random(5) . '-' . $filename;
        $s3Path = $folder . '/' . $uniqueFilename;
        
        $contents = file_get_contents($absolutePath);
        if (Storage::disk('s3')->put($s3Path, $contents, 'public')) {
            return Storage::disk('s3')->url($s3Path);
        }
    }
    
    return $localPath;
}

function processHtmlContent($html) {
    if (empty($html)) return $html;
    
    $html = \App\Services\HtmlImageProcessor::processBase64Images($html);
    
    $pattern = '/src=["\'](\/?images\/[^"\']+)["\']/i';
    $html = preg_replace_callback($pattern, function($matches) {
        $originalSrc = $matches[1];
        $newSrc = uploadLocalToS3($originalSrc, 'optimized/editor');
        return 'src="' . $newSrc . '"';
    }, $html);
    
    $pattern2 = '/src=["\'](https?:\/\/(www\.)?dainely\.com\/?images\/[^"\']+)["\']/i';
    $html = preg_replace_callback($pattern2, function($matches) {
        $originalSrc = $matches[1];
        $newSrc = uploadLocalToS3($originalSrc, 'optimized/editor');
        return 'src="' . $newSrc . '"';
    }, $html);

    return $html;
}

// 1. BLOG POSTS
$blogs = BlogPost::all();
$blogCount = 0;
foreach ($blogs as $blog) {
    $updated = false;
    if ($blog->featured_image && !str_contains($blog->featured_image, 'media.dainely.com')) {
        $newUrl = uploadLocalToS3($blog->featured_image, 'images');
        if ($newUrl !== $blog->featured_image) {
            $blog->featured_image = $newUrl;
            $updated = true;
        }
    }
    if ($updated) {
        $blog->save();
        $blogCount++;
    }
}

$translations = BlogPostTranslation::all();
$transCount = 0;
foreach ($translations as $trans) {
    $newContent = processHtmlContent($trans->content);
    if ($newContent !== $trans->content) {
        $trans->content = $newContent;
        $trans->save();
        $transCount++;
    }
}

// 2. EDUCATION PAGES
$edus = EducationPage::all();
$eduCount = 0;
foreach ($edus as $edu) {
    $updated = false;
    
    if ($edu->hero_image && !str_contains($edu->hero_image, 'media.dainely.com')) {
        $newUrl = uploadLocalToS3($edu->hero_image, 'images');
        if ($newUrl !== $edu->hero_image) {
            $edu->hero_image = $newUrl;
            $updated = true;
        }
    }
    
    if ($edu->hero_description) {
        $newDesc = processHtmlContent($edu->hero_description);
        if ($newDesc !== $edu->hero_description) {
            $edu->hero_description = $newDesc;
            $updated = true;
        }
    }
    
    if ($edu->treatments_description) {
        $newDesc = processHtmlContent($edu->treatments_description);
        if ($newDesc !== $edu->treatments_description) {
            $edu->treatments_description = $newDesc;
            $updated = true;
        }
    }
    
    $rootCauses = is_string($edu->root_causes) ? json_decode($edu->root_causes, true) : $edu->root_causes;
    if (is_array($rootCauses)) {
        $changed = false;
        foreach ($rootCauses as &$cause) {
            if (isset($cause['description'])) {
                $newDesc = processHtmlContent($cause['description']);
                if ($newDesc !== $cause['description']) {
                    $cause['description'] = $newDesc;
                    $changed = true;
                }
            }
        }
        if ($changed) {
            $edu->root_causes = $rootCauses;
            $updated = true;
        }
    }
    
    $treatments = is_string($edu->treatments) ? json_decode($edu->treatments, true) : $edu->treatments;
    if (is_array($treatments)) {
        $changed = false;
        foreach ($treatments as &$treat) {
            if (is_string($treat)) {
                $newDesc = processHtmlContent($treat);
                if ($newDesc !== $treat) {
                    $treat = $newDesc;
                    $changed = true;
                }
            } elseif (is_array($treat) && isset($treat['content'])) {
                $newDesc = processHtmlContent($treat['content']);
                if ($newDesc !== $treat['content']) {
                    $treat['content'] = $newDesc;
                    $changed = true;
                }
            }
        }
        if ($changed) {
            $edu->treatments = $treatments;
            $updated = true;
        }
    }
    
    $blocks = is_string($edu->blocks) ? json_decode($edu->blocks, true) : $edu->blocks;
    if (is_array($blocks)) {
        $changed = false;
        foreach ($blocks as &$block) {
            if (isset($block['content'])) {
                $newDesc = processHtmlContent($block['content']);
                if ($newDesc !== $block['content']) {
                    $block['content'] = $newDesc;
                    $changed = true;
                }
            }
            if (isset($block['image']) && !str_contains($block['image'], 'media.dainely.com')) {
                $newUrl = uploadLocalToS3($block['image'], 'images');
                if ($newUrl !== $block['image']) {
                    $block['image'] = $newUrl;
                    $changed = true;
                }
            }
        }
        if ($changed) {
            $edu->blocks = $blocks;
            $updated = true;
        }
    }

    if ($updated) {
        $edu->save();
        $eduCount++;
    }
}

echo "Migrated $blogCount Blog images.\n";
echo "Migrated $transCount Blog HTML contents.\n";
echo "Migrated $eduCount Education pages.\n";