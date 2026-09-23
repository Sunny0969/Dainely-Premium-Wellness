<?php
$file = "d:/dainly Project/Dainely-Premium-Wellness/resources/views/components/blocks/video.blade.php";
$content = file_get_contents($file);

$search1 = <<<EOT
    // Allow bare YouTube/Vimeo URL or full iframe HTML
    if (! str_contains(\$raw, '<iframe') && preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([\w\-]+)~i', \$raw, \$m)) {
        \$url = 'https://www.youtube.com/embed/' . \$m[1];
    } elseif (! str_contains(\$raw, '<iframe') && preg_match('~vimeo\.com/(?:video/)?(\d+)~i', \$raw, \$m)) {
        \$url = 'https://player.vimeo.com/video/' . \$m[1];
    }
EOT;

$replace1 = <<<EOT
    // Allow bare YouTube/Vimeo URL or full iframe HTML
    \$isVertical = false;
    
    if (! str_contains(\$raw, '<iframe') && preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/|youtube\.com/shorts/)([\w\-]+)~i', \$raw, \$m)) {
        \$url = 'https://www.youtube.com/embed/' . \$m[1];
        if (str_contains(strtolower(\$raw), '/shorts/')) {
            \$isVertical = true;
        }
    } elseif (! str_contains(\$raw, '<iframe') && preg_match('~vimeo\.com/(?:video/)?(\d+)~i', \$raw, \$m)) {
        \$url = 'https://player.vimeo.com/video/' . \$m[1];
    } elseif (str_contains(\$raw, '<iframe')) {
        // Check if iframe has width and height to determine orientation
        if (preg_match('/width=["\']?(\d+)/i', \$raw, \$w) && preg_match('/height=["\']?(\d+)/i', \$raw, \$h)) {
            if (intval(\$h[1]) > intval(\$w[1])) {
                \$isVertical = true;
            }
        } elseif (str_contains(strtolower(\$raw), 'tiktok.com') || str_contains(strtolower(\$raw), '/shorts/')) {
            \$isVertical = true;
        }
    }
    
    \$aspectClass = \$isVertical ? 'aspect-[9/16] max-w-sm mx-auto' : 'aspect-video';
EOT;

$content = str_replace($search1, $replace1, $content);

$search2 = '<div class="relative w-full overflow-hidden rounded-2xl shadow-lg bg-black aspect-video">';
$replace2 = '<div class="relative w-full overflow-hidden rounded-2xl shadow-lg bg-black {{ $aspectClass }}">';
$content = str_replace($search2, $replace2, $content);

file_put_contents($file, $content);
echo "Done";