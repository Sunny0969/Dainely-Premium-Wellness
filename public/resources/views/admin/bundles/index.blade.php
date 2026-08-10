@extends('layouts.admin')

@section('admin_title', 'Bundles & Offers')

@section('admin_content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    {{-- Create Bundle --}}
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm h-fit">
        <h2 class="text-lg font-bold text-slate-800 mb-6">Create Product Bundle</h2>
        <form action="/{{ $adminBase }}/bundles" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Bundle Title</label>
                <input type="text" name="title" required placeholder="e.g. Daily Relief System" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Shopify Bundle Product GID</label>
                <input type="text" name="bundle_shopify_product_id" required placeholder="gid://shopify/Product/12345" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Locale</label>
                <select name="locale" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="en">English (en)</option>
                    <option value="fr">French (fr)</option>
                    <option value="de">German (de)</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Description</label>
                <textarea name="description" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
            </div>

            <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 rounded-lg text-sm transition">
                Create Bundle
            </button>
        </form>
    </div>

    {{-- List Bundles --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
            <h2 class="text-lg font-bold text-slate-800">Existing Product Bundles</h2>
        </div>

        <div class="divide-y divide-slate-200">
            @forelse($bundles as $bundle)
                <div class="p-6 flex justify-between items-center gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-xs font-bold uppercase">
                                {{ $bundle->locale }}
                            </span>
                            <span class="text-xs text-slate-400 font-mono">
                                GID: {{ $bundle->bundle_shopify_product_id }}
                            </span>
                        </div>
                        <strong class="block text-slate-900 text-lg">{{ $bundle->title }}</strong>
                        <p class="text-slate-500 text-sm">{{ $bundle->description }}</p>
                    </div>

                    <div>
                        <a href="/{{ $adminBase }}/bundles/{{ $bundle->id }}/edit" class="bg-navy-600 hover:bg-navy-700 text-white text-xs font-bold px-3 py-2 rounded transition">
                            Edit Components
                        </a>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-slate-400">No bundles created yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
