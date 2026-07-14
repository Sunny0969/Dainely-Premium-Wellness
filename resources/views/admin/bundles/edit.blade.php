@extends('layouts.admin')

@section('admin_title', 'Edit Bundle Components: ' . $bundle->title)

@section('admin_content')
<div class="space-y-8">
    {{-- Bundle Info --}}
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <h2 class="text-lg font-bold text-slate-800 mb-6">Bundle Settings</h2>
        <form action="/admin/bundles/{{ $bundle->id }}/update" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Bundle Title</label>
                <input type="text" name="title" value="{{ $bundle->title }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Shopify Bundle Product GID</label>
                <input type="text" name="bundle_shopify_product_id" value="{{ $bundle->bundle_shopify_product_id }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Description</label>
                <textarea name="description" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ $bundle->description }}</textarea>
            </div>

            <div class="md:col-span-2 flex justify-end">
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold px-6 py-2.5 rounded-lg text-sm transition">
                    Save Settings
                </button>
            </div>
        </form>
    </div>

    {{-- Bundle Components list & Add Component form --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Add Component Product Form --}}
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm h-fit">
            <h2 class="text-lg font-bold text-slate-800 mb-6">Add Component Product</h2>
            <form action="/admin/bundles/{{ $bundle->id }}/items" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Select Product</label>
                    <select name="product_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}">{{ $prod->title }} ({{ $prod->handle }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Quantity</label>
                    <input type="number" name="quantity" value="1" min="1" max="10" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>

                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 rounded-lg text-sm transition">
                    Add Product Component
                </button>
            </form>
        </div>

        {{-- Existing Components list --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                <h2 class="text-lg font-bold text-slate-800">Component Products</h2>
            </div>

            <div class="divide-y divide-slate-200">
                @forelse($bundle->items as $item)
                    <div class="p-6 flex justify-between items-center gap-4">
                        <div class="flex items-center gap-3">
                            @if($item->product && $item->product->featured_image)
                                <img src="{{ $item->product->featured_image }}" class="w-12 h-12 object-cover rounded-lg border border-slate-200">
                            @else
                                <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center text-slate-400">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </div>
                            @endif
                            <div>
                                <strong class="block text-slate-900 text-base">
                                    {{ $item->product ? $item->product->title : 'Deleted Product' }}
                                </strong>
                                <span class="text-sm text-slate-500">
                                    Quantity: {{ $item->quantity }} | Price: ${{ number_format(($item->product ? $item->product->price : 0), 2) }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <form action="/admin/bundles/{{ $bundle->id }}/items/{{ $item->id }}/delete" method="POST" onsubmit="return confirm('Are you sure you want to remove this product component?');">
                                @csrf
                                <button type="submit" class="text-rose-600 hover:text-rose-800 text-sm font-bold">
                                    Remove
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-slate-400">No product components added yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
