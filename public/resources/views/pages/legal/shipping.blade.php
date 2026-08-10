@extends('layouts.app')
@php
  $freeShipLegal = app(\App\Services\CurrencyService::class)->formatForLocale(
      app(\App\Services\CurrencyService::class)->freeShippingThresholdUsd(),
      app()->getLocale()
  );
@endphp
@section('title', 'Shipping Policy | Dainely')
@section('meta_description', 'Dainely shipping policy — free shipping over '.$freeShipLegal.', delivery times by region, and order tracking information.')
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
      @foreach([
        [
          'title'  => 'Free Shipping Threshold',
          'sub'    => 'Orders over '.$freeShipLegal,
          'detail' => 'Reach '.$freeShipLegal.' in cart total and standard shipping is free. Below that, shipping is calculated at checkout.',
          'color'  => 'sage',
          'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 12 20 22 4 22 4 12"/><rect width="20" height="5" x="2" y="7"/><line x1="12" x2="12" y1="22" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>',
        ],
        [
          'title'  => 'Order Processing',
          'sub'    => '1–2 business days',
          'detail' => 'Monday to Saturday. Your order leaves our warehouse fast.',
          'color'  => 'gold',
          'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
        ],
        [
          'title'  => 'Delivery Time',
          'sub'    => '6–12 business days',
          'detail' => 'Standard worldwide delivery (remote islands may take up to 15 days).',
          'color'  => 'navy',
          'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>',
        ],
        [
          'title'  => 'Order Tracking',
          'sub'    => 'Tracking on every order',
          'detail' => 'Shipment confirmation email with tracking number — active within 24 hours.',
          'color'  => 'navy',
          'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>',
        ],
      ] as $item)
      <div class="card p-6">
        <div class="w-10 h-10 bg-{{ $item['color'] }}-100 rounded-xl flex items-center justify-center mb-4">
          <div class="w-5 h-5 text-{{ $item['color'] }}-600">{!! $item['icon'] !!}</div>
        </div>
        <h3 class="font-semibold text-navy-900 text-lg mb-1">{{ $item['title'] }}</h3>
        <p class="text-sage-600 font-medium text-sm mb-1">{{ $item['sub'] }}</p>
        <p class="text-slate-500 text-sm">{{ $item['detail'] }}</p>
      </div>
      @endforeach
    </div>

    <div class="prose prose-slate max-w-none prose-a:text-blue-600 prose-a:underline hover:prose-a:text-blue-700">

      <h2>Delivery Times</h2>
      <p>We currently ship worldwide. Standard delivery times are listed below — please note that delivery to remote islands may take longer due to limited carrier service.</p>
      <table>
        <thead>
          <tr>
            <th>Destination</th>
            <th>Delivery Time</th>
          </tr>
        </thead>
        <tbody>
          <tr><td>All countries (standard)</td><td>6–12 business days (Mon–Sat)</td></tr>
          <tr><td>Remote islands</td><td>Up to 15 business days</td></tr>
        </tbody>
      </table>

      <h2>Order Changes</h2>
      <p>Once your order has been confirmed, it cannot be modified or cancelled. If you wish to return a product, please start the returns procedure once you've received the item.</p>

      <h2>Shipment Confirmation & Order Tracking</h2>
      <p>You will receive a shipment confirmation email once your order has shipped, containing your tracking number(s). The tracking number will be active within 24 hours of dispatch.</p>

      <h2>Item Not Received</h2>
      <p>If you've successfully placed an order and haven't received it yet but the tracking status shows it as delivered, please contact the carrier first to locate your package. Once the item is in the carrier's possession, we no longer have control over it. If the issue persists, contact us at <a href="mailto:contact@dainely.com">contact@dainely.com</a> and we will do our best to assist and resolve the issue as quickly as possible.</p>
      <p>Sometimes tracking information may not be available due to technical issues, preventing updates from showing on the app. If you're still unable to locate your item in this situation, please contact us at <a href="mailto:contact@dainely.com">contact@dainely.com</a> and we'll assist you at our earliest convenience.</p>

    </div>

    {{-- Contact Card --}}
    <div class="mt-12 bg-navy-50 rounded-3xl p-6 md:p-8">
      <h2 class="font-display font-bold text-2xl text-navy-900 mb-2">Contact Us</h2>
      <p class="text-slate-600 text-sm mb-6">For shipping questions, order issues, or anything else — we're here to help.</p>

      <div class="grid sm:grid-cols-2 gap-5">
        @foreach([
          [
            'label' => 'Support Hours',
            'value' => config('company.hours'),
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
          ],
          [
            'label' => 'Phone',
            'value' => '<a href="tel:' . config('company.phone_tel') . '" class="text-blue-600 underline hover:text-blue-700">' . e(config('company.phone_display')) . '</a>',
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
          ],
          [
            'label' => 'Email',
            'value' => '<a href="mailto:' . config('company.email') . '" class="text-blue-600 underline hover:text-blue-700">' . e(config('company.email')) . '</a>',
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>',
          ],
          [
            'label' => 'Address',
            'value' => e(config('company.address_lines.0')) . '<br>' . e(config('company.address_lines.1')),
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>',
          ],
        ] as $info)
        <div class="flex items-start gap-4">
          <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center flex-shrink-0">
            <div class="w-5 h-5 text-gold-600">{!! $info['icon'] !!}</div>
          </div>
          <div>
            <p class="font-semibold text-navy-900 text-sm mb-1">{{ $info['label'] }}</p>
            <p class="text-slate-600 text-sm leading-relaxed">{!! $info['value'] !!}</p>
          </div>
        </div>
        @endforeach
      </div>
    </div>

  </div>
</section>
@endsection
