@props(['title', 'content'])
<section class="cta-block bg-navy-900 text-white py-16 text-center">
    <div class="container-site max-w-4xl mx-auto px-4">
        @if(!empty($title))
            <h2 class="text-4xl font-extrabold mb-4">{{ $title }}</h2>
        @endif
        <div class="prose prose-invert max-w-none mb-8 text-slate-300">
            {!! $content !!}
        </div>
        <div class="flex justify-center gap-4">
            <a href="{{ route('products.index', ['locale' => app()->getLocale()]) }}" class="btn bg-white text-navy-900 font-bold px-8 py-3 rounded-lg hover:bg-slate-100 transition duration-150">
                {{ __('Shop Now') }}
            </a>
        </div>
    </div>
</section>
