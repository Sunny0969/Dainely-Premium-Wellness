<?php
$file = "routes/web.php";
$content = file_get_contents($file);
$content .= "\nRoute::get(\"/flush-all-cache\", function() { \Illuminate\Support\Facades\Cache::flush(); return \"Cache flushed completely!\"; });\n";
file_put_contents($file, $content);

