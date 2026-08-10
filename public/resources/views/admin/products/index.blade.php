@extends('layouts.admin')

@section('admin_title', 'Product Local Overlays')

@section('admin_content')
<style>
  .admin-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.2;
    padding: 8px 12px;
    border-radius: 8px;
    border: 0;
    color: #fff !important;
    text-decoration: none;
    white-space: nowrap;
    cursor: pointer;
  }
  .admin-action-btn:hover { opacity: 0.92; }
  .admin-action-edit { background: #1e3a5f; }
  .admin-action-unpublish { background: #d97706; }
  .admin-action-publish { background: #059669; }
  .admin-action-delete { background: #e11d48; }
  .admin-actions-cell { min-width: 320px; }
  .admin-actions-row { display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-end; align-items: center; }
  .admin-actions-row form { display: inline; margin: 0; }
</style>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
        <h2 class="text-lg font-bold text-slate-800">Synced Products Catalog</h2>
        <p class="text-xs text-slate-500 mt-1">Unpublish hides a product on the live site. Delete removes it from this CMS catalog (Shopify store is unchanged).</p>
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
                    @php $isUnpublished = $prod->status === \App\Support\ProductVisibility::STATUS_UNPUBLISHED; @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3 font-semibold text-slate-900">
                                @if($prod->featured_image)
                                    <img src="{{ $prod->featured_image }}" alt="" class="w-10 h-10 object-cover rounded-lg border border-slate-200 {{ $isUnpublished ? 'opacity-50' : '' }}">
                                @else
                                    <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center text-slate-400">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                                <span class="{{ $isUnpublished ? 'text-slate-500' : '' }}">{{ $prod->title }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-mono text-xs">{{ $prod->handle }}</td>
                        <td class="px-6 py-4 text-xs font-mono">{{ $prod->sku ?: '—' }}</td>
                        <td class="px-6 py-4 font-semibold">${{ number_format($prod->price ?: 0, 2) }}</td>
                        <td class="px-6 py-4">
                            @if($prod->status === 'active')
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700">active</span>
                            @elseif($isUnpublished)
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider" style="background:#fffbeb;color:#92400e;">unpublished</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-slate-100 text-slate-700">{{ $prod->status }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 admin-actions-cell">
                            <div class="admin-actions-row">
                                <a href="/{{ $adminBase }}/products/{{ $prod->id }}/edit" class="admin-action-btn admin-action-edit">
                                    Edit Overlays
                                </a>

                                @if($isUnpublished)
                                    <form action="/{{ $adminBase }}/products/{{ $prod->id }}/publish" method="POST">
                                        @csrf
                                        <button type="submit" class="admin-action-btn admin-action-publish">Publish</button>
                                    </form>
                                @else
                                    <form action="/{{ $adminBase }}/products/{{ $prod->id }}/unpublish" method="POST" onsubmit="return confirm('Unpublish this product? It will be hidden from the live website.');">
                                        @csrf
                                        <button type="submit" class="admin-action-btn admin-action-unpublish">Unpublish</button>
                                    </form>
                                @endif

                                <form action="/{{ $adminBase }}/products/{{ $prod->id }}/delete" method="POST" onsubmit="return confirm('Delete this product from the admin catalog? This cannot be undone. Shopify product is not deleted.');">
                                    @csrf
                                    <button type="submit" class="admin-action-btn admin-action-delete">Delete</button>
                                </form>
                            </div>
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
