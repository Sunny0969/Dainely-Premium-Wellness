<?php
$content = file_get_contents("d:/dainly Project/Dainely-Premium-Wellness/app/Http/Controllers/Frontend/LandingPageController.php");
$start = strpos($content, 'public function show');
$end = strpos($content, 'public function checkout', $start);
echo substr($content, $start, $end - $start);