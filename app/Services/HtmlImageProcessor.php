<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class HtmlImageProcessor
{
    /**
     * Parse HTML, find base64 images, upload them to E2, and return updated HTML.
     */
    public static function processBase64Images($html)
    {
        if (empty($html)) {
            return $html;
        }

        // Use regex to find all base64 images in src attributes
        $pattern = '/src="data:image\/([^;]+);base64,([^"]+)"/i';
        
        return preg_replace_callback($pattern, function ($matches) {
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
            $path = 'optimized/editor/' . $filename;

            // Upload to S3 (E2)
            Storage::disk('s3')->put($path, $imageData, 'public');
            
            // Get URL
            $url = Storage::disk('s3')->url($path);

            return 'src="' . $url . '"';
        }, $html);
    }
}
