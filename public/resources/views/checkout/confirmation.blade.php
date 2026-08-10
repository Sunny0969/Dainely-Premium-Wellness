@extends('layouts.app')
@section('title', __('checkout.confirmed_title') . ' | Dainely')
@section('meta_description', __('checkout.confirmed_thanks', ['name' => $order->customer_first_name]))

@section('content')
@php
  $sym = $order->currency_symbol ?? '$';
  $fmt = fn ($amount) => $sym . number_format((float) $amount, 2);
@endphp
<div class="min-h-screen bg-slate-50 flex items-center">
  <div class="container-narrow py-16 text-center">

    <div class="w-24 h-24 bg-sage-100 rounded-full flex items-center justify-center mx-auto mb-8 animate-float">
      <svg class="w-12 h-12 text-sage-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </div>

    <h1 class="font-display font-bold text-4xl text-navy-900 mb-4">{{ __('checkout.confirmed_title') }} 🎉</h1>
    <p class="text-lead mb-2">{!! __('checkout.confirmed_thanks', ['name' => '<strong>' . e($order->customer_first_name) . '</strong>']) !!}</p>
    <p class="text-slate-500 mb-8">{!! __('checkout.confirmed_email', ['email' => '<strong>' . e($order->customer_email) . '</strong>']) !!}</p>

    <div class="card p-8 text-left max-w-lg mx-auto mb-8">
      <div class="flex items-center justify-between mb-6">
        <div>
          <p class="text-slate-400 text-sm">{{ __('checkout.order_number') }}</p>
          <p class="font-display font-bold text-navy-900 text-xl">#{{ $order->order_number }}</p>
          @if(!empty($order->shopify_order_name))
          <p class="text-sage-600 text-xs mt-1">✓ {!! __('checkout.shopify_synced', ['name' => '<strong>' . e($order->shopify_order_name) . '</strong>']) !!}</p>
          @elseif(!empty($order->shopify_sync_failed))
          <p class="text-amber-600 text-xs mt-1">{{ __('checkout.shopify_processing') }}</p>
          @endif
        </div>
        <span class="trust-badge bg-sage-50 border-sage-200 text-sage-700">✓ {{ __('checkout.confirmed_badge') }}</span>
      </div>

      <div class="space-y-3 mb-6">
        @foreach($order->items as $item)
        <div class="flex items-center gap-4">
          <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" class="w-14 h-14 rounded-xl object-cover bg-slate-100">
          <div class="flex-1">
            <p class="font-semibold text-slate-800 text-sm">{{ $item->product_name }}</p>
            <p class="text-slate-400 text-xs">{{ __('checkout.qty') }}: {{ $item->quantity }}</p>
          </div>
          <p class="font-semibold text-navy-900">{{ $fmt($item->total_price ?? $item->total_price_usd) }}</p>
        </div>
        @endforeach
      </div>

      <div class="border-t border-slate-100 pt-4 space-y-2">
        <div class="flex justify-between text-sm">
          <span class="text-slate-500">{{ __('checkout.subtotal') }}</span>
          <span>{{ $fmt($order->subtotal ?? $order->subtotal_usd) }}</span>
        </div>
        @if(($order->discount_amount ?? $order->discount_amount_usd ?? 0) > 0)
        <div class="flex justify-between text-sm text-sage-600">
          <span>{{ __('checkout.discount') }} ({{ $order->discount_code }})</span>
          <span>-{{ $fmt($order->discount_amount ?? $order->discount_amount_usd) }}</span>
        </div>
        @endif
        <div class="flex justify-between text-sm">
          <span class="text-slate-500">{{ __('checkout.shipping') }}</span>
          <span>{{ ($order->shipping ?? $order->shipping_usd) == 0 ? __('checkout.free') : $fmt($order->shipping ?? $order->shipping_usd) }}</span>
        </div>
        @if(($order->tax ?? $order->tax_usd ?? 0) > 0)
        <div class="flex justify-between text-sm">
          <span class="text-slate-500">{{ __('checkout.tax') }}</span>
          <span>{{ $fmt($order->tax ?? $order->tax_usd) }}</span>
        </div>
        @endif
        <div class="flex justify-between font-bold text-lg pt-2 border-t border-slate-200">
          <span class="text-navy-900">{{ __('checkout.total') }} ({{ $order->currency_code ?? 'USD' }})</span>
          <span class="text-navy-900">{{ $fmt($order->total ?? $order->total_usd) }}</span>
        </div>
      </div>
    </div>

    <div class="bg-navy-50 rounded-2xl p-6 text-left max-w-lg mx-auto mb-8">
      <h3 class="font-semibold text-navy-900 mb-3 flex items-center gap-2">
        <svg class="w-5 h-5 text-navy-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        {{ __('checkout.delivery_info') }}
      </h3>
      <p class="text-slate-600 text-sm">{{ __('checkout.delivering_to') }}: <strong>{{ $order->shipping_address1 }}, {{ $order->shipping_city }}, {{ $order->shipping_country }}</strong></p>
      <p class="text-slate-500 text-sm mt-1">{{ __('checkout.estimated_delivery') }}: <strong>{{ $order->shipping_method === 'express' ? __('checkout.express_delivery') : __('checkout.standard_delivery') }}</strong></p>
    </div>

    <div class="flex flex-wrap justify-center gap-4">
      <a href="{{ route('products.index', ['locale' => $locale]) }}" class="btn-primary">{{ __('checkout.continue_shopping') }}</a>
      <a href="{{ route('contact', ['locale' => $locale]) }}" class="btn-outline">{{ __('checkout.contact_support') }}</a>
    </div>
  </div>
</div>
@endsection
