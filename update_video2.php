<?php
$file = 'd:/dainly Project/Dainely-Premium-Wellness/resources/views/components/blocks/video.blade.php';
$content = file_get_contents($file);

$search = '<section class="video-block py-12 bg-slate-50 border-t border-gray-100">';
$replace = <<<'EOT'
<section class="video-block py-12 bg-slate-50 border-t border-gray-100">
    <style>
        .video-block-container iframe {
            width: 100% !important;
            height: 100% !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            border: none !important;
            max-width: 100% !important;
        }
    </style>
EOT;

$content = str_replace($search, $replace, $content);

$search2 = '<div class="relative w-full overflow-hidden rounded-2xl shadow-lg bg-black {{ $aspectClass }}">';
$replace2 = '<div class="video-block-container relative w-full overflow-hidden rounded-2xl shadow-lg bg-black {{ $aspectClass }}">';
$content = str_replace($search2, $replace2, $content);

file_put_contents($file, $content);
echo "Updated video.blade.php for responsive iframe\n";