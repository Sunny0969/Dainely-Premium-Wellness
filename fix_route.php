<?php
$file = "routes/web.php";
$content = file_get_contents($file);
$content = str_replace(
    "\$content = preg_replace(\"/https:\/\/media\.dainely\.com\/dainely-media\/optimized\/editor\/[^\"]+?\.jpg/\", \"/images/hero-lifestyle.png\", \$content);",
    "\$content = preg_replace(\"/https:\/\/media\.dainely\.com\/dainely-media\/optimized\/editor\/[^\\\"]+?\.jpg/\", \"/images/hero-lifestyle.png\", \$content);",
    $content
);
file_put_contents($file, $content);
echo "Fixed!";

