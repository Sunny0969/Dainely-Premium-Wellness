@extends('layouts.app')
@section('title', 'Shipping Policy | Dainely')
@section('meta_description', 'Dainely shipping policy — free shipping over $75, delivery times by region, and order tracking information.')
@section('content')
<section class="bg-navy-900 text-white py-12">
  <div class="container-narrow text-center">
    <h1 class="font-display font-bold text-3xl text-white mb-2">Shipping Policy</h1>
    <p class="text-navy-300">Last updated: January 1, 2025</p>
  </div>
</section>
<section class="section bg-white">
  <div class="container-narrow">
    <div class="grid md:grid-cols-2 gap-6 mb-12">
      @foreach([['Free Standard Shipping','On all orders over $75 USD','Orders under $75: $9.99 flat rate','sage'],['Express Shipping','2–3 business days','$24.99 flat rate worldwide','gold'],['Order Processing','1–2 business days','Monday–Friday, 9am–5pm EST','navy'],['Tracking','All orders tracked','Email confirmation with tracking link','navy']] as [$title,$sub,$detail,$color])
      <div class="card p-6">
        <div class="w-10 h-10 bg-{{ $color }}-100 rounded-xl flex items-center justify-center mb-4">
          <svg class="w-5 h-5 text-{{ $color }}-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </div>
        <h3 class="font-semibold text-navy-900 text-lg mb-1">{{ $title }}</h3>
        <p class="text-sage-600 font-medium text-sm mb-1">{{ $sub }}</p>
        <p class="text-slate-500 text-sm">{{ $detail }}</p>
      </div>
      @endforeach
    </div>
    <div class="prose prose-slate max-w-none">
      <h2>Delivery Times by Region</h2>
      <table>
        <thead><tr><th>Region</th><th>Standard</th><th>Express</th></tr></thead>
        <tbody>
          <tr><td>USA & Canada</td><td>3–5 business days</td><td>1–2 business days</td></tr>
          <tr><td>Europe</td><td>5–8 business days</td><td>2–4 business days</td></tr>
          <tr><td>UK & Australia</td><td>6–10 business days</td><td>3–5 business days</td></tr>
          <tr><td>Rest of World</td><td>7–14 business days</td><td>4–7 business days</td></tr>
        </tbody>
      </table>
      <h2>International Orders & Customs</h2>
      <p>International orders may be subject to customs duties and import taxes. These charges are the responsibility of the recipient and vary by country. Dainely is not responsible for customs delays.</p>
      <h2>Order Tracking</h2>
      <p>Once your order ships, you'll receive an email with a tracking number. You can track your order at any time via the carrier's website or by contacting our support team at <a href="mailto:support@dainely.com">support@dainely.com</a>.</p>
    </div>
  </div>
</section>
@endsection
