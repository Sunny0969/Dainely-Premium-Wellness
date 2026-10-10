<?php
$file = "routes/web.php";
$content = file_get_contents($file);
$route = "
Route::get(\"/organize-all-media\", function () {
    \$log = [];
    try {
        // Find and replace in Blogs
        \$blogs = App\Models\BlogPostTranslation::all();
        foreach (\$blogs as \$blog) {
            \$changed = false;
            \$html = \$blog->content;
            if (strpos(\$html, \"optimized/editor/\") !== false) {
                \$html = str_replace(\"optimized/editor/\", \"dainely/media/blogs/\", \$html);
                \$changed = true;
            }
            if (strpos(\$html, \"/images/\") !== false) {
                \$html = str_replace(\"dainely-media/images/\", \"dainely-media/dainely/media/blogs/\", \$html);
                \$changed = true;
            }
            if (\$changed) {
                \$blog->content = \$html;
                \$blog->save();
            }
        }

        // Find and replace in Education
        \$edus = App\Models\Supabase\EducationPageTranslation::all();
        foreach (\$edus as \$edu) {
            \$changed = false;
            \$html = \$edu->content;
            if (\$html && strpos(\$html, \"optimized/editor/\") !== false) {
                \$html = str_replace(\"optimized/editor/\", \"dainely/media/education/\", \$html);
                \$changed = true;
            }
            if (\$html && strpos(\$html, \"/images/\") !== false) {
                \$html = str_replace(\"dainely-media/images/\", \"dainely-media/dainely/media/education/\", \$html);
                \$changed = true;
            }
            if (\$changed) {
                \$edu->content = \$html;
                \$edu->save();
            }
        }
        
        // Find and replace in PageBlocks (Landing Pages)
        \$blocks = App\Models\Supabase\PageBlock::all();
        foreach (\$blocks as \$block) {
            \$changed = false;
            \$html = \$block->content;
            if (\$html && strpos(\$html, \"Landing Pages/\") !== false) {
                \$html = str_replace(\"Landing Pages/\", \"dainely/media/landing/\", \$html);
                \$changed = true;
            }
            if (\$html && strpos(\$html, \"optimized/editor/\") !== false) {
                \$html = str_replace(\"optimized/editor/\", \"dainely/media/landing/\", \$html);
                \$changed = true;
            }
            if (\$changed) {
                \$block->content = \$html;
                \$block->save();
            }
        }
        
        return \"Database updated! Please use your IDrive Dashboard to drag and drop the folders (Landing Pages -> dainely/media/landing, optimized -> dainely/media/others).\";
    } catch (\Exception \$e) {
        return \"Error: \" . \$e->getMessage();
    }
});
";
if (strpos($content, "/organize-all-media") === false) {
    file_put_contents($file, $content . $route);
    echo "Route added!";
} else {
    echo "Route already exists.";
}

