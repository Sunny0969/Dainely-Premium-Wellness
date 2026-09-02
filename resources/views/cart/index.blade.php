@extends('layouts.app')
@section('title', __('nav.cart'))

@section('content')
<div class="min-h-screen bg-slate-50 py-10">
  <div class="container-site">
    <h1 class="font-display font-bold text-navy-900 text-2xl mb-8">{{ __('nav.cart') }}</h1>

    @if(empty($cartItems))
      <div class="card p-10 text-center">
        <p class="text-slate-500 mb-6">{{ __('checkout.cart_empty') }}</p>
        <a href="{{ route('products.index', ['locale' => app()->getLocale()]) }}" class="btn-primary">
          {{ __('nav.continue_shopping') }}
        </a>
      </div>
    @else
      <div class="grid lg:grid-cols-[1fr_350px] gap-8">
        <div class="card p-6">
          <form action="{{ route('cart.update', ['locale' => app()->getLocale()]) }}" method="POST">
            @csrf
            <div class="space-y-6">
              @foreach($cartItems as $item)
                <div class="flex items-start gap-4 pb-6 border-b border-slate-100 last:border-0 last:pb-0">
                  <div class="w-20 h-20 bg-slate-100 rounded-xl flex-shrink-0">
                    <img src="{{ $item['image'] ?? '' }}" alt="{{ $item['title'] ?? '' }}" class="w-full h-full object-cover rounded-xl">
                  </div>
                  <div class="flex-1 min-w-0">
                    <a href="#" class="font-semibold text-slate-800 text-base truncate block hover:text-navy-600">{{ $item['title'] ?? '' }}</a>
                    @if(!empty($item['option_label']))
                      <p class="text-slate-500 text-sm mt-1">{{ __('checkout.size_label') }}: {{ $item['option_label'] }}</p>
                    @endif
                    <div class="flex items-center gap-4 mt-3">
                      <div class="flex items-center">
                        <input type="number" name="line_quantities[{{ $item['key'] }}]" value="{{ $item['quantity'] ?? 1 }}" min="1" max="20" class="w-16 form-input px-2 py-1 text-center" onchange="this.form.submit()">
                      </div>
                      <button type="submit" name="remove_key" value="{{ $item['key'] }}" class="text-slate-400 hover:text-red-600 text-sm transition-colors">
                        {{ __('checkout.remove_item') }}
                      </button>
                    </div>
                  </div>
                  <div class="text-right">
                    <p class="font-semibold text-navy-900">{{ $pricing['currency_symbol'] }}{{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 2) }}</p>
                  </div>
                </div>
              @endforeach
            </div>
            
            <noscript>
              <div class="mt-4 flex justify-end">
                <button type="submit" class="btn-outline">{{ __('checkout.update_cart') }}</button>
              </div>
            </noscript>
          </form>
        </div>

        <div class="card p-6 h-fit sticky top-24">
          <h2 class="font-display font-bold text-navy-900 text-lg mb-4">{{ __('checkout.order_summary') }}</h2>
          <div class="space-y-3 mb-6 text-sm">
            <div class="flex justify-between text-slate-600">
              <span>{{ __('checkout.subtotal') }}</span>
              <span class="font-medium text-slate-900">{{ $pricing['currency_symbol'] }}{{ number_format($pricing['subtotal'] ?? 0, 2) }}</span>
            </div>
            <div class="flex justify-between text-slate-600">
              <span>{{ __('checkout.shipping') }}</span>
              <span>{{ __('checkout.tax_calculating') }}</span>
            </div>
          </div>
          
          <div class="border-t border-slate-100 pt-4 mb-6">
            <div class="flex justify-between text-base font-bold text-navy-900">
              <span>{{ __('checkout.total') }}</span>
              <span>{{ $pricing['currency_symbol'] }}{{ number_format($pricing['subtotal'] ?? 0, 2) }}</span>
            </div>
          </div>

          <a href="{{ route('checkout.index', ['locale' => app()->getLocale()]) }}" class="btn-gold-lg w-full justify-center">
            {{ __('products.view_cart_checkout') }}
          </a>
          <div class="mt-4 text-center">
            <a href="{{ route('products.index', ['locale' => app()->getLocale()]) }}" class="text-navy-600 text-sm font-medium hover:underline">
              {{ __('nav.continue_shopping') }}
            </a>
          </div>
        </div>
      </div>
    @endif
  </div>
</div>
@endsection
