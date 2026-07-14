@extends('layouts.app')

@section('title', 'Search Results for "' . $query . '"')

@section('content')
<div class="search-results-page py-12 bg-white">
    <div class="container-site max-w-4xl mx-auto px-4">
        <h1 class="text-3xl font-extrabold text-navy-900 mb-8">{{ __('Search Results') }}</h1>

        <form action="{{ route('search', ['locale' => $locale]) }}" method="GET" class="mb-10 flex gap-4">
            <input 
                type="text" 
                name="q" 
                value="{{ $query }}" 
                placeholder="{{ __('Search for products, articles...') }}" 
                class="flex-1 px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-navy-500 focus:border-transparent shadow-sm"
            >
            <button type="submit" class="btn bg-navy-600 hover:bg-navy-700 text-white px-8 py-3 rounded-lg font-semibold transition duration-150">
                {{ __('Search') }}
            </button>
        </form>

        @if($results->isEmpty())
            <div class="text-center py-12 text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <p class="text-xl font-medium mb-2">{{ __('No results found') }}</p>
                <p>{{ __('Try adjusting your keywords or checking your spelling.') }}</p>
            </div>
        @else
            <div class="space-y-6">
                @foreach($results as $result)
                    <div class="p-6 bg-slate-50 border border-gray-200 rounded-lg hover:shadow-sm transition duration-150">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="inline-block text-xs font-semibold uppercase tracking-wider text-navy-600 bg-navy-50 px-2.5 py-0.5 rounded-full">
                                {{ __($result['type']) }}
                            </span>
                            <span class="text-xs text-gray-400">Score: {{ number_format($result['rank'], 2) }}</span>
                        </div>
                        <h2 class="text-xl font-bold text-navy-900 mb-2">
                            <a href="{{ $result['url'] }}" class="hover:text-navy-600 transition">
                                {{ $result['title'] }}
                            </a>
                        </h2>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
