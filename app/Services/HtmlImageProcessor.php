<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class HtmlImageProcessor
{
    /**
     * Parse HTML, find base64 images, upload them to E2, and return updated HTML.
     */
    public static function processBase64Images($html, $folder = 'optimized/editor')
    {
        if (empty($html)) {
            return $html;
        }

        // Use regex to find all base64 images in src attributes
        $pattern = '/src="data:image\/([^;]+);base64,([^"]+)"/i';
        
        return preg_replace_callback($pattern, function ($matches) use ($folder) {
            $extension = $matches[1]; // e.g., jpeg, png, gif
            if ($extension === 'jpeg') {
                $extension = 'jpg';
            }
            
            $base64Data = $matches[2];
            $imageData = base64_decode($base64Data);
            
            if ($imageData === false) {
                return $matches[0]; // If decode fails, leave it as is
            }

            // Generate unique filename
            $filename = time() . '-' . Str::random(10) . '.' . $extension;
            $folderClean = trim($folder, '/');
            $path = $folderClean . '/' . $filename;

            // Save locally
            $localDir = public_path('images/' . $folderClean);
            if (!file_exists($localDir)) {
                @mkdir($localDir, 0755, true);
            }
            @file_put_contents($localDir . '/' . $filename, $imageData);

            if ($folderClean === 'blogs') {
                $publicBlogs = public_path('blogs');
                if (!file_exists($publicBlogs)) {
                    @mkdir($publicBlogs, 0755, true);
                }
                @file_put_contents($publicBlogs . '/' . $filename, $imageData);
                @file_put_contents(public_path('images/' . $filename), $imageData);
            }

            // Upload to S3 (E2)
            try {
                Storage::disk('s3')->put($path, $imageData, 'public');
            } catch (\Throwable $e) {}
            
            // Get URL
            $url = asset('images/' . $folderClean . '/' . $filename);

            return 'src="' . $url . '"';
        }, $html);
    }
}