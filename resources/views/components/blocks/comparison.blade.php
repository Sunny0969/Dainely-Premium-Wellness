@props(['title' => null, 'content' => null])
<section class="comparison-block py-12 bg-white border-t border-gray-100">
    <div class="container-site max-w-5xl mx-auto px-4">
        @if(!empty($title))
            <h2 class="text-3xl font-bold text-navy-800 mb-8 text-center">{{ $title }}</h2>
        @endif
        <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow-sm">
            <div class="comparison-content prose max-w-none p-4 md:p-6
                [&_table]:w-full [&_table]:border-collapse
                [&_th]:bg-navy-900 [&_th]:text-white [&_th]:px-4 [&_th]:py-3 [&_th]:text-left
                [&_td]:px-4 [&_td]:py-3 [&_td]:border-t [&_td]:border-slate-100
                [&_tr:nth-child(even)_td]:bg-slate-50">
                {!! $content !!}
            </div>
        </div>
        <p class="text-center text-xs text-slate-400 mt-3">
            Tip: paste an HTML <code>&lt;table&gt;</code> in block content for feature comparison.
        </p>
    </div>
</section>
