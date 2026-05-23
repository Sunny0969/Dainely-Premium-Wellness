@extends('layouts.app')
@section('title', 'Shopify Products — ' . config('app.name'))
@section('meta_description', 'Products synced from Shopify Admin API.')

@section('content')
<section class="section bg-section-alt">
  <div class="container-site">
    <div class="mb-10">
      <p class="eyebrow mb-2">Shopify Admin</p>
      <h1 class="heading-section mb-3">Store Products</h1>
      <p class="text-body text-sm text-slate-600">
        Store: <strong>{{ $meta['shop_domain'] }}</strong>
        · API: <strong>{{ $meta['api_version'] }}</strong>
        · Source: <strong>{{ $meta['source'] ?? '—' }}</strong>
        · Showing <strong>{{ $meta['product_count'] }}</strong> of max {{ $meta['limit'] }}
      </p>
    </div>

    @if($error)
    <div class="rounded-xl border border-red-200 bg-red-50 p-6 mb-8 text-red-800 space-y-4">
      <p class="font-semibold mb-2">Could not load products from Shopify</p>
      <p class="text-sm">{{ $error }}</p>
    </div>
    @endif

    @if(count($products) > 0)
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
      <table class="w-full text-left text-sm">
        <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
          <tr>
            <th class="px-4 py-3 font-medium">Image</th>
            <th class="px-4 py-3 font-medium">Title</th>
            <th class="px-4 py-3 font-medium">Status</th>
            <th class="px-4 py-3 font-medium">Price</th>
            <th class="px-4 py-3 font-medium">Variants</th>
            <th class="px-4 py-3 font-medium">Updated</th>
            <th class="px-4 py-3 font-medium">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($products as $product)
          @php
            $image = $product['image']['src'] ?? ($product['images'][0]['src'] ?? null);
            $variantCount = count($product['variants'] ?? []);
            $updatedAt = !empty($product['updated_at']) ? \Carbon\Carbon::parse($product['updated_at'])->format('M j, Y') : '—';
            $price = $product['variants'][0]['price'] ?? null;
          @endphp
          <tr class="hover:bg-navy-50/60 cursor-pointer transition-colors" onclick="window.location='{{ route('shop.show', $product['id']) }}'">
            <td class="px-4 py-3">
              @if($image)
              <img src="{{ $image }}" alt="" class="h-12 w-12 rounded-lg object-cover bg-slate-100" loading="lazy">
              @else
              <span class="inline-flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100 text-xs text-slate-400">—</span>
              @endif
            </td>
            <td class="px-4 py-3 font-semibold text-navy-900 hover:text-navy-600">
              <a href="{{ route('shop.show', $product['id']) }}" class="hover:underline">{{ $product['title'] ?? 'Untitled' }}</a>
            </td>
            <td class="px-4 py-3">
              <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ ($product['status'] ?? '') === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                {{ ucfirst($product['status'] ?? 'unknown') }}
              </span>
            </td>
            <td class="px-4 py-3 font-semibold text-navy-800">
              @if($price) ${{ number_format((float)$price, 2) }} @else — @endif
            </td>
            <td class="px-4 py-3 text-slate-600">{{ $variantCount }}</td>
            <td class="px-4 py-3 text-slate-600">{{ $updatedAt }}</td>
            <td class="px-4 py-3">
              <a href="{{ route('shop.show', $product['id']) }}" class="inline-flex items-center gap-1.5 bg-navy-600 hover:bg-navy-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                View
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
              </a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @elseif(!$error)
    <p class="text-body text-slate-600">No products returned from Shopify.</p>
    @endif
  </div>
</section>
@endsection
