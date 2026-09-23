<?php
$content = file_get_contents("d:/dainly Project/Dainely-Premium-Wellness/app/Http/Controllers/Admin/AdminLandingController.php");
preg_match('/public function updateBlock\(.*?\n    \}/s', $content, $matches);
if (empty($matches)) {
    preg_match('/public function updateBlock\(.*?\n    \}/s', $content, $matches);
    // If it's longer, let's just use string operations
    $start = strpos($content, 'public function updateBlock');
    $end = strpos($content, 'public function deleteBlock', $start);
    echo substr($content, $start, $end - $start);
} else {
    echo $matches[0];
}