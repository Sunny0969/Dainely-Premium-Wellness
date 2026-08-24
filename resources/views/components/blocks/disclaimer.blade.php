@props(['title', 'content'])
<section class="disclaimer-block bg-slate-50 py-8 border-t border-slate-200">
    <div class="container-site max-w-4xl mx-auto px-4 text-xs sm:text-sm text-slate-500">
        @if(!empty($title))
            <h3 class="text-sm sm:text-base font-bold text-slate-700 mb-2">{{ $title }}</h3>
        @endif
        <div class="cms-richtext">
            {!! \App\Support\CmsHtml::normalize($content) !!}
        </div>
    </div>
</section>
