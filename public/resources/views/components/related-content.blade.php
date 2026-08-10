{{-- components/related-content.blade.php — Phase 2 §7.2 --}}
@props(['title' => null, 'links' => []])
@php
  $title = $title ?? __('Related Resources');
  $links = collect($links)->filter()->values();
@endphp
@if($links->count())
<section class="related-content py-8 bg-slate-50 border-t border-gray-100 mt-12" aria-labelledby="related-content-heading">
    <div class="container-site max-w-4xl mx-auto px-4">
        <h2 id="related-content-heading" class="text-2xl font-bold text-navy-800 mb-6">{{ $title }}</h2>
        <ul class="space-y-4">
            @foreach($links as $link)
                <li>
                    <a href="{{ $link['url'] }}" class="block p-4 bg-white rounded-lg border border-gray-200 hover:border-navy-500 hover:shadow-sm transition duration-150">
                        <span class="inline-block text-xs font-semibold uppercase tracking-wider text-navy-600 bg-navy-50 px-2.5 py-0.5 rounded-full mb-2">
                            {{ $link['type_label'] }}
                        </span>
                        <strong class="block text-navy-900 text-lg">{{ $link['title'] }}</strong>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endif
