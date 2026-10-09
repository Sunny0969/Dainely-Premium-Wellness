<?php
$file = "routes/web.php";
$content = file_get_contents($file);

// Remove the old routes
$content = preg_replace("/Route::get\(\'\/organize-all-media\'.*?\}\\);\s*/s", "", $content);
$content = preg_replace("/Route::get\(\"\/organize-all-media\".*?\}\\);\s*/s", "", $content);

$route = <<<'EOT'

Route::get("/organize-all-media", function () {
    $log = [];
    try {
        // 1. Blogs -> dainely/blogs
        $blogs = App\Models\BlogPostTranslation::all();
        foreach ($blogs as $blog) {
            $changed = false;
            $html = $blog->content;
            if (preg_match_all("/media\.dainely\.com\/dainely-media\/(optimized\/editor|images|dainely\/media\/blogs)\/([^\\\"\'\s]+)/i", $html, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $oldPath = $match[1] . "/" . $match[2];
                    $newPath = "dainely/blogs/" . $match[2];
                    if ($oldPath !== $newPath && \Illuminate\Support\Facades\Storage::disk("s3")->exists($oldPath)) {
                        \Illuminate\Support\Facades\Storage::disk("s3")->copy($oldPath, $newPath);
                        $html = str_replace($oldPath, $newPath, $html);
                        $changed = true;
                        $log[] = "Moved $oldPath -> $newPath (Blog:$blog->id)";
                    }
                }
            }
            if ($changed) {
                $blog->content = $html;
                $blog->save();
            }
        }

        // 2. Education -> dainely/media/Education
        $edus = App\Models\Supabase\EducationPageTranslation::all();
        foreach ($edus as $edu) {
            $changed = false;
            $html = $edu->content;
            if ($html && preg_match_all("/media\.dainely\.com\/dainely-media\/(optimized\/editor|images|dainely\/media\/education)\/([^\\\"\'\s]+)/i", $html, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $oldPath = $match[1] . "/" . $match[2];
                    $newPath = "dainely/media/Education/" . $match[2];
                    if ($oldPath !== $newPath && \Illuminate\Support\Facades\Storage::disk("s3")->exists($oldPath)) {
                        \Illuminate\Support\Facades\Storage::disk("s3")->copy($oldPath, $newPath);
                        $html = str_replace($oldPath, $newPath, $html);
                        $changed = true;
                        $log[] = "Moved $oldPath -> $newPath (Edu:$edu->id)";
                    }
                }
            }
            if ($changed) {
                $edu->content = $html;
                $edu->save();
            }
        }
        
        // 3. Landing Pages -> dainely/media/Landing page
        $blocks = App\Models\Supabase\PageBlock::all();
        foreach ($blocks as $block) {
            $changed = false;
            $html = $block->content;
            if ($html && preg_match_all("/media\.dainely\.com\/dainely-media\/(optimized\/editor|Landing Pages|dainely\/media\/landing)\/([^\\\"\'\s]+)/i", $html, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $oldPath = $match[1] . "/" . $match[2];
                    $newPath = "dainely/media/Landing page/" . $match[2];
                    if ($oldPath !== $newPath && \Illuminate\Support\Facades\Storage::disk("s3")->exists($oldPath)) {
                        \Illuminate\Support\Facades\Storage::disk("s3")->copy($oldPath, $newPath);
                        $html = str_replace($oldPath, $newPath, $html);
                        $changed = true;
                        $log[] = "Moved $oldPath -> $newPath (LandingBlock:$block->id)";
                    }
                }
            }
            if ($changed) {
                $block->content = $html;
                $block->save();
            }
        }
        
        return "Done migrating! Log:<br>" . implode("<br>", $log);
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

Route::get("/test-unsplash", function () {
    try {
        $url = "https://images.unsplash.com/photo-1664286074176-5206ee5dc878?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MXx8YmVsdHLlbnwwfHwwfHx8Mg3D%CD";
        $contents = file_get_contents($url);
        $filename = "dummy-test-" . time() . ".jpg";
        $path = "dainely/media/Landing page/" . $filename;
        \Illuminate\Support\Facades\Storage::disk("s3")->put($path, $contents, "public");
        $s3Url = \Illuminate\Support\Facades\Storage::disk("s3")->url($path);
        
        return "Success! Unsplash image uploaded to Landing page folder. URL: <a href=\"$s3Url\" target=\"_blank\">$s3Url</a>";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});
EOT;

file_put_contents($file, $content . $route);
echo "Routes updated!";
