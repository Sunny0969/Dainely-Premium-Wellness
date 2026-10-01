<?php
$file = "routes/web.php";
$content = file_get_contents($file);
$content = str_replace(
    "\$post = App\Models\BlogPost::where(\"slug\", \"why-mobility-matters-after-50-7-simple-ways-to-keep-moving-with-confidence\")->first();",
    "\$postTranslation = App\Models\BlogPostTranslation::where(\"slug\", \"why-mobility-matters-after-50-7-simple-ways-to-keep-moving-with-confidence\")->first();\n    \$post = \$postTranslation ? \$postTranslation->blogPost : null;\n    if (\$postTranslation) {\n        \$content = \$postTranslation->content;\n",
    $content
);
$content = str_replace(
    "\$post->content = \$content;\n        \$post->save();",
    "\$postTranslation->content = \$content;\n        \$postTranslation->save();",
    $content
);
file_put_contents($file, $content);
echo "Fixed!";

