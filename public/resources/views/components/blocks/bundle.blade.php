@props([
    'title' => null,
    'content' => null,
    'bundleView' => null,
    'ctaUrl' => null,
    'ctaLabel' => null,
])
@php
    /** @var array|null $bundleView */
    $view = $bundleView;
    $currency = $view['currency'] ?? 'USD';
    $fmt = fn (float $n) => '$' . number_format($n, 2);
@endphp

@if($view && !empty($view['components']))
<section class="bundle-block py-12 bg-white border-t border-gray-100" aria-labelledby="bundle-heading-{{ $view['bundle']->id }}">
    <div class="container-site max-w-4xl mx-auto px-4">
        <h2 id="bundle-heading-{{ $view['bundle']->id }}" class="text-3xl font-bold text-navy-800 mb-2 text-center">
            {{ $title ?: $view['bundle']->title }}
        </h2>
        @if(!empty($view['bundle']->description) || !empty($content))
            <div class="prose max-w-none text-center text-slate-600 mb-8">
                {!! $content ?: ('<p>' . e($view['bundle']->description) . '</p>') !!}
            </div>
        @endif

        <ul class="space-y-4 mb-8">
            @foreach($view['components'] as $component)
                <li class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 bg-slate-50">
                    <div class="w-16 h-16 rounded-lg overflow-hidden bg-white border border-slate-100 shrink-0">
                        @if(!empty($component['image']))
                            <img src="{{ $component['image'] }}" alt="{{ $component['title'] }}" class="w-full h-full object-cover" loading="lazy">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-300 text-xs">N/A</div>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-navy-900 truncate">{{ $component['title'] }}</p>
                        <p class="text-sm text-slate-500">
                            {{ __('bundles.qty') }}: {{ $component['quantity'] }}
                            · {{ $fmt($component['unit_price']) }} {{ __('bundles.each') }}
                        </p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="font-bold text-navy-800">{{ $fmt($component['line_total']) }}</p>
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-5 rounded-2xl bg-navy-900 text-white">
            <div>
                <p class="text-sm text-navy-200 uppercase tracking-wide">{{ __('bundles.bundle_total') }}</p>
                <p class="text-3xl font-extrabold">{{ $fmt($view['total']) }}</p>
            </div>
            <form method="POST" action="{{ $view['add_url'] }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center justify-center bg-white text-navy-900 font-bold px-8 py-3 rounded-xl hover:bg-slate-100 transition">
                    {{ $ctaLabel ?: __('bundles.add_to_cart') }}
                </button>
            </form>
        </div>
    </div>
</section>
@elseif(!empty($title) || !empty($content))
<section class="bundle-block py-8">
    <div class="container-site max-w-4xl mx-auto px-4 text-center text-slate-500 text-sm">
        <p>{{ $title }}</p>
        <p>{{ __('bundles.missing_or_empty') }}</p>
        @if(!empty($content))
            <p class="font-mono text-xs mt-2">bundle id: {{ $content }}</p>
        @endif
    </div>
</section>
@endif
