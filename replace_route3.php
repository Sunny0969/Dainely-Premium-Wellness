<?php
$file = "routes/web.php";
$content = file_get_contents($file);

$content = preg_replace("/Route::get\(\\'\\/organize-all-media\\'.*?\}\\);\s*/s", "", $content);
$content = preg_replace("/Route::get\(\"\\/organize-all-media\".*?\}\\);\s*/s", "", $content);

$route = <<<'EOT'

Route::get("/organize-all-media", function () {
    $log = [];
    
    function processModel($model, $type, &$log, $slug = null) {
        $changed = false;
        $patterns = [
            "blog" => "/(optimized\\/editor|images|dainely\\/media\\/blogs)\\/([^\s\\\"\\'\\<\\>]+)/i",
            "edu" => "/(optimized\\/editor|images|dainely\\/media\\/education)\\/([^\s\\\"\\\'\\<\\>]+)/i",
            "landing" => "/(optimized\\/editor|Landing Pages|Lanfing%20Pages|images|dainely\\/media\\/landing|dainely\\/media\\/Landing page)\\/([^\s\\\"\\\'\\<\\>]+)/i"
        ];
        $dest = [
            "blog" => "dainely/blogs/",
            "edu" => "dainely/media/Education/",
            "landing" => "dainely/media/Landing page/"
        ];
        
        $attrs = $model->getAttributes();
        foreach ($attrs as $key => $val) {
            if (is_string($val)) {
                if (preg_match_all($patterns[$type], $val, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) {
                        $oldPath = $match[1] . "/" . $match[2];
                        $newPath = $dest[$type];
                        if ($slug) $newPath .= $slug . "/";
                        $newPath .= $match[2];
                        
                        $copyOld = urldecode($oldPath);
                        $copyNew = urldecode($newPath);

                        if ($oldPath !== $newPath) {
                            try {
                                if (\Illuminate\Support\Facades\Storage::disk("s3")->exists($copyOld)) {
                                    \Illuminate\Support\Facades\Storage::disk("s3")->copy($copyOld, $copyNew);
                                } else {
                                    // Try without urldecode
                                    if (\Illuminate\Support\Facades\Storage::disk("s3")->exists($oldPath)) {
                                        \Illuminate\Support\Facades\Storage::disk("s3")->copy($oldPath, $newPath);
                                    }
                                }
                                $val = str_replace($oldPath, $newPath, $val);
                                $val = str_replace(addcslashes($oldPath, "/"), addcslashes($newPath, "/"), $val);
                                $changed = true;
                                $log[] = "Moved $oldPath -> $newPath";
                            } catch (\Exception $e) {
                                $log[] = "Copy error for $oldPath: " . $e->getMessage();
                            }
                        }
                    }
                }
                $attrs[$key] = $val;
            }
        }
        
        if ($changed) {
            $model->setRawAttributes($attrs);
            $model->save();
        }
    }

    try {
        // 1. Blogs
        foreach (\App\Models\BlogPostTranslation::all() as $m) processModel($m, "blog", $log);
        
        // 2. Education
        foreach (\App\Models\Catalog\EducationPage::all() as $m) processModel($m, "edu", $log);
        
        // 3. Landing Pages
        foreach (\App\Models\Supabase\PageBlock::all() as $m) {
            $slug = null;
            if ($m->blockable_type === 'App\\Models\\Supabase\\LandingPage' && $m->blockable) {
                $slug = $m->blockable->slug;
            }
            processModel($m, "landing", $log, $slug);
        }
        
        return "Done! Log:<br>" . implode("<br>", $log);
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage() . " at line " . $e->getLine();
    }
});

EOT;

file_put_contents($file, $content . $route);
echo "Updated!";