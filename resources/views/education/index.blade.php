@extends('layouts.app')
@section('title', 'Education & Resources | Dainely')
@section('meta_description', 'Explore our comprehensive library of education and wellness resources.')

@section('content')

<section class="bg-navy-900 text-white py-20 lg:py-28 relative overflow-hidden">
    <div class="absolute inset-0 bg-grid-white/[0.05] bg-[length:32px_32px]"></div>
    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10 text-center">
        <h1 class="font-display font-bold text-4xl lg:text-6xl tracking-tight mb-6 text-white">Education & Resources</h1>
        <p class="text-xl text-navy-200 max-w-2xl mx-auto leading-relaxed">Discover expertly crafted guides and routines designed to help you move freely, reduce discomfort, and embrace an active lifestyle.</p>
    </div>
</section>

<section class="py-16 lg:py-24 bg-slate-50" x-data="{ activeCategory: 'All' }">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        
        @if(isset($breadcrumbs) && is_array($breadcrumbs))
            <div class="mb-10 text-sm text-slate-500 flex flex-wrap items-center gap-2">
                @foreach($breadcrumbs as $index => $crumb)
                    @if($crumb['url'])
                        <a href="{{ $crumb['url'] }}" class="hover:text-navy-600 transition-colors">{{ $crumb['name'] }}</a>
                    @else
                        <span class="text-slate-400 font-medium">{{ $crumb['name'] }}</span>
                    @endif
                    
                    @if(!$loop->last)
                        <svg class="w-3 h-3 text-slate-300 mx-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    @endif
                @endforeach
            </div>
        @endif

        {{-- Filter Tags / Category Pills --}}
        <div class="flex flex-wrap items-center gap-3 mb-12">
            <button 
                @click="activeCategory = 'All'"
                :class="activeCategory === 'All' ? 'bg-navy-600 text-white border-navy-600' : 'bg-white border-slate-200 text-slate-600 hover:border-navy-200 hover:text-navy-700'"
                class="px-4 py-2 rounded-full text-sm font-semibold shadow-sm transition-colors cursor-pointer border focus:outline-none">
                All Articles
            </button>
            @php
                $allCategories = [
                    'Movement & Mobility',
                    'Back & Core Support',
                    'Posture & Alignment',
                    'Recovery & Relaxation',
                    'Active 50+ Lifestyle',
                    'Everyday Comfort'
                ];
            @endphp
            @foreach($allCategories as $cat)
                <button 
                    @click="activeCategory = '{{ addslashes($cat) }}'"
                    :class="activeCategory === '{{ addslashes($cat) }}' ? 'bg-navy-600 text-white border-navy-600' : 'bg-white border-slate-200 text-slate-600 hover:border-navy-200 hover:text-navy-700'"
                    class="px-4 py-2 rounded-full border text-sm font-semibold shadow-sm transition-colors cursor-pointer focus:outline-none">
                    {{ $cat }}
                </button>
            @endforeach
        </div>

        @forelse($groupedPages as $category => $pages)
            <div class="mb-16 last:mb-0" x-show="activeCategory === 'All' || activeCategory === '{{ addslashes($category) }}'" x-transition>
                <div class="flex items-center gap-4 mb-8">
                    <h2 class="font-display font-bold text-2xl lg:text-3xl text-navy-900">{{ $category }}</h2>
                    <div class="h-px bg-slate-200 flex-grow mt-1"></div>
                </div>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($pages as $page)
                        <a href="{{ route('education.show', ['locale' => $locale, 'slug' => $page->slug]) }}" class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md hover:border-navy-200 transition-all group flex flex-col h-full overflow-hidden">
                            @if($page->hero_image)
                                <img src="{{ Str::startsWith($page->hero_image, ['http://', 'https://', '//']) ? $page->hero_image : asset('images/' . $page->hero_image) }}" alt="{{ $page->title }}" class="w-full h-48 object-cover">
                            @else
                                <div class="w-full h-48 bg-slate-100 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5L18.5 7H20M9 11h.01M15 11h.01M9 15h.01M15 15h.01M9 19h.01M15 19h.01"/></svg>
                                </div>
                            @endif
                            <div class="p-6 flex-grow flex flex-col">
                                <div class="flex-grow">
                                    <h3 class="font-bold text-lg text-slate-900 group-hover:text-navy-700 transition-colors mb-2 leading-tight">{{ $page->title }}</h3>
                                    @if($page->hero_description)
                                        <p class="text-slate-500 text-sm line-clamp-2 leading-relaxed">{{ $page->hero_description }}</p>
                                    @endif
                                </div>
                                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-slate-400">
                                    <span>{{ $page->read_time ?: '5 min read' }}</span>
                                    <span class="text-navy-600 group-hover:translate-x-1 transition-transform flex items-center gap-1">Read Guide <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-20">
                <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <h3 class="text-xl font-bold text-slate-700 mb-2">No Resources Found</h3>
                <p class="text-slate-500">We are currently updating our education library. Check back soon.</p>
            </div>
        @endforelse

    </div>
</section>

@endsection
