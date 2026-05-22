@extends('layouts.app')
@section('title', 'Shopify Connection')
@section('content')
<section class="section bg-section-alt">
  <div class="container-narrow text-center">
    @if($success)
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-8 mb-6">
      <h1 class="heading-section text-emerald-900 mb-3">Shopify Connected</h1>
      <p class="text-body text-emerald-800 mb-4">{{ $message }}</p>
      <p class="text-sm text-emerald-700">Store: <strong>{{ $shop }}</strong> · Token: <code>{{ $token_prefix }}</code></p>
    </div>
    <div class="flex flex-wrap gap-4 justify-center">
      <a href="{{ url('/en') }}" class="btn-primary">View Homepage</a>
      <a href="{{ route('shop.index') }}" class="btn-outline">View /shop</a>
    </div>
    <p class="text-slate-500 text-sm mt-6">Optional: copy the token from <code>storage/app/shopify_access_token</code> into <code>SHOPIFY_ADMIN_ACCESS_TOKEN</code> in .env.</p>
    @else
    <div class="rounded-2xl border border-red-200 bg-red-50 p-8 mb-6">
      <h1 class="heading-section text-red-900 mb-3">Connection Failed</h1>
      <p class="text-body text-red-800">{{ $message }}</p>
    </div>
    <a href="{{ route('shopify.install') }}" class="btn-primary">Try Again</a>
    @endif
  </div>
</section>
@endsection
