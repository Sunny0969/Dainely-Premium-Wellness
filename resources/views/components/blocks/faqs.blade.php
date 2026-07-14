@props(['title', 'content'])
<section class="faqs-block bg-slate-50 py-12">
    <div class="container-site max-w-4xl mx-auto px-4">
        @if(!empty($title))
            <h2 class="text-3xl font-bold text-navy-800 mb-8 text-center">{{ $title }}</h2>
        @endif
        <div class="space-y-4">
            {!! $content !!}
        </div>
    </div>
</section>
