<?php
// Remove BOM and rewrite Media.php
$file = 'app/Models/Media.php';
$content = file_get_contents($file);
$content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
file_put_contents($file, $content);

// Remove BOM and rewrite MediaService.php
$file2 = 'app/Services/MediaService.php';
$content2 = file_get_contents($file2);
$content2 = preg_replace('/^\xEF\xBB\xBF/', '', $content2);
file_put_contents($file2, $content2);
