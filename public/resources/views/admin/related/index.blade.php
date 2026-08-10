@extends('layouts.admin')

@section('admin_title', 'Internal Knowledge Graph')

@section('admin_content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="{
    sourceType: 'product',
    relatedType: 'education',
}">

    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm h-fit">
        <h2 class="text-lg font-bold text-slate-800 mb-6">Create Content Link</h2>
        <form action="/{{ $adminBase }}/related" method="POST" class="space-y-4">
            @csrf

            <div class="p-4 bg-slate-50 rounded-lg border border-slate-200 space-y-3">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Source Entity</h3>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Source Type</label>
                    <select name="source_type" x-model="sourceType" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs">
                        <option value="product">Product</option>
                        <option value="landing_page">Landing Page</option>
                        <option value="education">Education</option>
                        <option value="blog">Blog</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Source Resource</label>
                    <select name="source_id" x-show="sourceType === 'product'" x-bind:disabled="sourceType !== 'product'" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs">
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}">{{ $prod->title }} (#{{ $prod->id }})</option>
                        @endforeach
                    </select>
                    <select name="source_id" x-show="sourceType === 'landing_page'" x-bind:disabled="sourceType !== 'landing_page'" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs">
                        @foreach($landingPages as $page)
                            <option value="{{ $page->id }}">{{ $page->title }} ({{ $page->locale }})</option>
                        @endforeach
                    </select>
                    <select name="source_id" x-show="sourceType === 'education'" x-bind:disabled="sourceType !== 'education'" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs">
                        @foreach($educationPages as $edu)
                            <option value="{{ $edu['id'] }}">{{ $edu['title'] }}</option>
                        @endforeach
                    </select>
                    <select name="source_id" x-show="sourceType === 'blog'" x-bind:disabled="sourceType !== 'blog'" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs">
                        @foreach($blogPosts as $post)
                            <option value="{{ $post['id'] }}">{{ $post['title'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="p-4 bg-slate-50 rounded-lg border border-slate-200 space-y-3">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Related Entity</h3>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Related Type</label>
                    <select name="related_type" x-model="relatedType" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs">
                        <option value="product">Product</option>
                        <option value="landing_page">Landing Page</option>
                        <option value="education">Education</option>
                        <option value="blog">Blog</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Related Resource</label>
                    <select name="related_id" x-show="relatedType === 'product'" x-bind:disabled="relatedType !== 'product'" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs">
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}">{{ $prod->title }} (#{{ $prod->id }})</option>
                        @endforeach
                    </select>
                    <select name="related_id" x-show="relatedType === 'landing_page'" x-bind:disabled="relatedType !== 'landing_page'" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs">
                        @foreach($landingPages as $page)
                            <option value="{{ $page->id }}">{{ $page->title }} ({{ $page->locale }})</option>
                        @endforeach
                    </select>
                    <select name="related_id" x-show="relatedType === 'education'" x-bind:disabled="relatedType !== 'education'" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs">
                        @foreach($educationPages as $edu)
                            <option value="{{ $edu['id'] }}">{{ $edu['title'] }}</option>
                        @endforeach
                    </select>
                    <select name="related_id" x-show="relatedType === 'blog'" x-bind:disabled="relatedType !== 'blog'" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs">
                        @foreach($blogPosts as $post)
                            <option value="{{ $post['id'] }}">{{ $post['title'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Display Order</label>
                <input type="number" name="display_order" value="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 rounded-lg text-sm transition">
                Link Content
            </button>
        </form>
        <p class="mt-4 text-xs text-slate-500">
            Example: Product → Education / Blog so the product page shows Related Resources.
        </p>
    </div>

    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
            <h2 class="text-lg font-bold text-slate-800">Content Relationship Links</h2>
        </div>

        <div class="divide-y divide-slate-200">
            @forelse($relations as $rel)
                <div class="p-6 flex flex-wrap justify-between items-center gap-4 text-sm">
                    <div class="space-y-1 min-w-0">
                        <div class="flex items-center gap-2 text-slate-600 flex-wrap">
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-xs font-bold capitalize">
                                {{ $rel->source_type }} #{{ $rel->source_id }}
                            </span>
                            <span>➔</span>
                            <span class="px-2 py-0.5 rounded bg-navy-50 text-navy-700 text-xs font-bold capitalize">
                                {{ $rel->related_type }} #{{ $rel->related_id }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <form action="/{{ $adminBase }}/related/{{ $rel->id }}/update" method="POST" class="flex items-center gap-2">
                            @csrf
                            <label class="text-xs text-slate-500">Order</label>
                            <input type="number" name="display_order" value="{{ $rel->display_order }}" class="w-20 rounded-lg border border-slate-300 px-2 py-1 text-sm">
                            <button type="submit" class="text-navy-700 hover:text-navy-900 text-sm font-bold">Save</button>
                        </form>
                        <form action="/{{ $adminBase }}/related/{{ $rel->id }}/delete" method="POST" onsubmit="return confirm('Remove this relationship link?');">
                            @csrf
                            <button type="submit" class="text-rose-600 hover:text-rose-800 text-sm font-bold">Remove</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-slate-400">No content relationship links defined yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
