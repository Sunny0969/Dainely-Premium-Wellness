<?php
$file = "routes/web.php";
$content = file_get_contents($file);
$content = preg_replace("/Route::get\(\"\/fix-blog-images\".*?\}\);/s", "Route::get(\"/fix-blog-images\", function () {
    \$postTranslation = App\Models\BlogPostTranslation::where(\"slug\", \"why-mobility-matters-after-50-7-simple-ways-to-keep-moving-with-confidence\")->first();
    if (\$postTranslation) {
        \$content = \$postTranslation->content;
        \$content = preg_replace(\"/https:\/\/media\.dainely\.com\/dainely-media\/optimized\/editor\/1789759755-[^\.]+\.jpg/\", \"/images/women-walking.jpg\", \$content);
        \$content = preg_replace(\"/https:\/\/media\.dainely\.com\/dainely-media\/optimized\/editor\/1789759756-[^\.]+\.jpg/\", \"/images/lifestyle-everyday-movement.webp\", \$content);
        \$content = preg_replace(\"/https:\/\/media\.dainely\.com\/dainely-media\/optimized\/editor\/1789759757-[^\.]+\.jpg/\", \"/images/lifestyle-dainely-in-motion.png\", \$content);
        \$content = preg_replace(\"/https:\/\/media\.dainely\.com\/dainely-media\/optimized\/editor\/[^\"]+?\.jpg/\", \"/images/hero-lifestyle.png\", \$content);
        \$postTranslation->content = \$content;
        \$postTranslation->save();
        return \"Images replaced successfully!\";
    }
    return \"Post not found.\";
});", $content);
file_put_contents($file, $content);
echo "Fixed!";

