<?php
$file = 'd:/dainly Project/Dainely-Premium-Wellness/resources/views/components/blocks/video.blade.php';
$content = file_get_contents($file);

$searchIframe = <<<'EOT'
                    @php
                        $iframeHtml = str_replace('<iframe ', '<iframe referrerpolicy="strict-origin-when-cross-origin" ', $raw);
                        if (str_contains($iframeHtml, 'youtube.com/embed/')) {
                            $iframeHtml = preg_replace('/(youtube\.com\/embed\/[\w\-]+)(\?|")/', '$1?controls=1&mute=0&rel=0&modestbranding=1&playsinline=1&', $iframeHtml);
                            $iframeHtml = str_replace('&&', '&', $iframeHtml);
                            $iframeHtml = str_replace('&"', '"', $iframeHtml);
                        }
                    @endphp
EOT;

$replaceIframe = <<<'EOT'
                    @php
                        $iframeHtml = str_replace('<iframe ', '<iframe referrerpolicy="strict-origin-when-cross-origin" ', $raw);
                        
                        // Strip out harmful inline styles the user might have pasted (like transform: translate)
                        $iframeHtml = preg_replace('/style=["\'][^"\']*transform:[^"\']*["\']/i', '', $iframeHtml);
                        
                        if (str_contains($iframeHtml, 'youtube.com/embed/')) {
                            // Remove any existing mute=1 or controls=0 that the user pasted in the URL
                            $iframeHtml = str_replace(['mute=1', 'controls=0'], ['mute=0', 'controls=1'], $iframeHtml);
                            
                            // Inject our params if they don't exist
                            $iframeHtml = preg_replace('/(youtube\.com\/embed\/[\w\-]+)(\?|")/', '$1?controls=1&mute=0&rel=0&modestbranding=1&playsinline=1&', $iframeHtml);
                            $iframeHtml = str_replace('&&', '&', $iframeHtml);
                            $iframeHtml = str_replace('&"', '"', $iframeHtml);
                        }
                    @endphp
EOT;

$content = str_replace($searchIframe, $replaceIframe, $content);
file_put_contents($file, $content);
echo "Updated video blade php params logic\n";