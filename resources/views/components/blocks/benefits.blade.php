@props(['title', 'content'])
<section class="benefits-block bg-white py-12 border-t border-b border-gray-100">
    <div class="container-site max-w-4xl mx-auto px-4">
        @if(!empty($title))
            <h2 class="text-3xl font-bold text-navy-800 mb-6 text-center">{{ $title }}</h2>
        @endif
        <div class="prose max-w-none text-gray-700 leading-relaxed">
            {!! $content !!}
        </div>
    </div>
</section>
