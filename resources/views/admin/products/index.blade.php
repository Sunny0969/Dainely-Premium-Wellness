@extends('layouts.admin')

@section('admin_title', 'Product Local Overlays')

@section('admin_content')
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
        <h2 class="text-lg font-bold text-slate-800">Synced Products Catalog</h2>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-100 text-slate-600 text-sm font-semibold border-b border-slate-200">
                    <th class="px-6 py-3">Product Title</th>
                    <th class="px-6 py-3">Handle</th>
                    <th class="px-6 py-3">SKU</th>
                    <th class="px-6 py-3">Price</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-slate-700 text-sm">
                @forelse($products as $prod)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4 flex items-center gap-3 font-semibold text-slate-900">
                            @if($prod->featured_image)
                                <img src="{{ $prod->featured_image }}" class="w-10 h-10 object-cover rounded-lg border border-slate-200">
                            @else
                                <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center text-slate-400">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                            <span>{{ $prod->title }}</span>
                        </td>
                        <td class="px-6 py-4 font-mono text-xs">{{ $prod->handle }}</td>
                        <td class="px-6 py-4 text-xs font-mono">{{ $prod->sku ?: '—' }}</td>
                        <td class="px-6 py-4 font-semibold">${{ number_format($prod->price ?: 0, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider
                                {{ $prod->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                {{ $prod->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="/admin/products/{{ $prod->id }}/edit" class="inline-block bg-navy-600 hover:bg-navy-700 text-white text-xs font-bold px-3 py-2 rounded transition whitespace-nowrap">
                                Edit Local Overlays
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-400">No synced products available. Webhooks will automatically populate this from Shopify.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
