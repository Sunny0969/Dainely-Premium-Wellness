<?php
$file = 'd:/dainly Project/Dainely-Premium-Wellness/resources/views/components/blocks/video.blade.php';
$content = file_get_contents($file);

$search = <<<EOT
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

$replace = <<<EOT
    <style>
        .video-block-container iframe {
            width: 100% !important;
            height: 100% !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            border: none !important;
            max-width: 100% !important;
            min-width: 100% !important;
            min-height: 100% !important;
            transform: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .video-block-container > div > div {
            width: 100% !important;
            height: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
        }
    </style>
EOT;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Updated video css\n";