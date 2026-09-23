<?php
$file = 'd:/dainly Project/Dainely-Premium-Wellness/resources/views/components/blocks/how-it-works.blade.php';
$content = <<<'EOT'
@props(['title', 'content'])
<section class="how-it-works-block bg-slate-50 py-12">
    <div class="container-site max-w-4xl mx-auto px-4">
        @if(!empty($title))
            <h2 class="text-3xl font-bold text-navy-800 mb-6 text-center">{{ $title }}</h2>
        @endif
        <div class="cms-richtext text-gray-700 max-w-none">
            <style>
                @media (max-width: 768px) {
                    .how-it-works-block .cms-richtext > div[style*="display: flex"] {
                        flex-direction: column !important;
                        text-align: center !important;
                    }
                    .how-it-works-block .cms-richtext img {
                        width: 100% !important;
                        max-width: 400px !important;
                        height: auto !important;
                        margin: 0 auto 15px auto !important;
                    }
                }
            </style>
            {!! \App\Support\CmsHtml::normalize($content) !!}
        </div>
    </div>
</section>
EOT;
file_put_contents($file, $content);
echo "Updated how-it-works.blade.php\n";