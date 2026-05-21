@extends('layouts.app')
@section('title', 'Order Confirmed! | Dainely')
@section('meta_description', 'Your Dainely order has been confirmed. Thank you for your purchase!')

@section('content')
<div class="min-h-screen bg-slate-50 flex items-center">
  <div class="container-narrow py-16 text-center">

    {{-- Success icon --}}
    <div class="w-24 h-24 bg-sage-100 rounded-full flex items-center justify-center mx-auto mb-8 animate-float">
      <svg class="w-12 h-12 text-sage-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </div>

    <h1 class="font-display font-bold text-4xl text-navy-900 mb-4">Order Confirmed! 🎉</h1>
    <p class="text-lead mb-2">Thank you, <strong>{{ $order->customer_first_name }}</strong>. Your Dainely order is confirmed.</p>
    <p class="text-slate-500 mb-8">A confirmation email has been sent to <strong>{{ $order->customer_email }}</strong>.</p>

    {{-- Order summary card --}}
    <div class="card p-8 text-left max-w-lg mx-auto mb-8">
      <div class="flex items-center justify-between mb-6">
        <div>
          <p class="text-slate-400 text-sm">Order Number</p>
          <p class="font-display font-bold text-navy-900 text-xl">#{{ $order->order_number }}</p>
        </div>
        <span class="trust-badge bg-sage-50 border-sage-200 text-sage-700">✓ Confirmed</span>
      </div>

      <div class="space-y-3 mb-6">
        @foreach($order->items as $item)
        <div class="flex items-center gap-4">
          <img src="{{ asset('images/dainely-belt-product.png') }}" alt="{{ $item->product_name }}" class="w-14 h-14 rounded-xl object-cover bg-slate-100">
          <div class="flex-1">
            <p class="font-semibold text-slate-800 text-sm">{{ $item->product_name }}</p>
            <p class="text-slate-400 text-xs">Qty: {{ $item->quantity }}</p>
          </div>
          <p class="font-semibold text-navy-900">${{ number_format($item->total_price_usd, 2) }}</p>
        </div>
        @endforeach
      </div>

      <div class="border-t border-slate-100 pt-4 space-y-2">
        <div class="flex justify-between text-sm">
          <span class="text-slate-500">Subtotal</span>
          <span>${{ number_format($order->subtotal_usd, 2) }}</span>
        </div>
        @if($order->discount_amount_usd > 0)
        <div class="flex justify-between text-sm text-sage-600">
          <span>Discount ({{ $order->discount_code }})</span>
          <span>-${{ number_format($order->discount_amount_usd, 2) }}</span>
        </div>
        @endif
        <div class="flex justify-between text-sm">
          <span class="text-slate-500">Shipping</span>
          <span>{{ $order->shipping_usd == 0 ? 'FREE' : '$' . number_format($order->shipping_usd, 2) }}</span>
        </div>
        <div class="flex justify-between font-bold text-lg pt-2 border-t border-slate-200">
          <span class="text-navy-900">Total Paid</span>
          <span class="text-navy-900">${{ number_format($order->total_usd, 2) }}</span>
        </div>
      </div>
    </div>

    {{-- Delivery info --}}
    <div class="bg-navy-50 rounded-2xl p-6 text-left max-w-lg mx-auto mb-8">
      <h3 class="font-semibold text-navy-900 mb-3 flex items-center gap-2">
        <svg class="w-5 h-5 text-navy-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        Delivery Information
      </h3>
      <p class="text-slate-600 text-sm">Delivering to: <strong>{{ $order->shipping_address1 }}, {{ $order->shipping_city }}, {{ $order->shipping_country }}</strong></p>
      <p class="text-slate-500 text-sm mt-1">Estimated delivery: <strong>5–8 business days</strong></p>
      <p class="text-slate-400 text-xs mt-3">You will receive a shipping confirmation with tracking details once your order is dispatched — usually within 24 hours.</p>
    </div>

    <div class="flex flex-wrap justify-center gap-4">
      <a href="#" class="btn-primary">Continue Shopping</a>
      <a href="mailto:support@dainely.com" class="btn-outline">Contact Support</a>
    </div>
  </div>
</div>
@endsection
