<?php
$file = 'd:/dainly Project/Dainely-Premium-Wellness/resources/views/components/blocks/video.blade.php';
$content = file_get_contents($file);

$search = "        \$url = 'https://www.youtube.com/embed/' . \$m[1];";
$replace = "        \$url = 'https://www.youtube.com/embed/' . \$m[1] . '?controls=1&mute=0&rel=0&modestbranding=1&playsinline=1';";
$content = str_replace($search, $replace, $content);

$searchIframe = <<<'EOT'
            @if(str_contains($raw, '<iframe'))
                <div class="absolute inset-0 [&_iframe]:w-full [&_iframe]:h-full [&_iframe]:absolute [&_iframe]:inset-0">
                    {!! str_replace('<iframe ', '<iframe referrerpolicy="strict-origin-when-cross-origin" ', $raw) !!}
                </div>
EOT;

$replaceIframe = <<<'EOT'
            @if(str_contains($raw, '<iframe'))
                <div class="absolute inset-0 [&_iframe]:w-full [&_iframe]:h-full [&_iframe]:absolute [&_iframe]:inset-0">
                    @php
                        $iframeHtml = str_replace('<iframe ', '<iframe referrerpolicy="strict-origin-when-cross-origin" ', $raw);
                        if (str_contains($iframeHtml, 'youtube.com/embed/')) {
                            $iframeHtml = preg_replace('/(youtube\.com\/embed\/[\w\-]+)(\?|")/', '$1?controls=1&mute=0&rel=0&modestbranding=1&playsinline=1&', $iframeHtml);
                            $iframeHtml = str_replace('&&', '&', $iframeHtml);
                            $iframeHtml = str_replace('&"', '"', $iframeHtml);
                        }
                    @endphp
                    {!! $iframeHtml !!}
                </div>
EOT;

$content = str_replace($searchIframe, $replaceIframe, $content);
file_put_contents($file, $content);
echo "Updated video.blade.php\n";