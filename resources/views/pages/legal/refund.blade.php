@extends('layouts.app')
@section('title', 'Refund Policy | Dainely')
@section('meta_description', 'Dainely 30-day money-back guarantee and refund policy. Full refund, no questions asked.')
@section('content')
<section class="bg-navy-900 text-white py-12">
  <div class="container-narrow text-center">
    <h1 class="font-display font-bold text-3xl text-white mb-2">Refund Policy</h1>
    <p class="text-navy-300">30-Day Money-Back Guarantee</p>
  </div>
</section>
<section class="section bg-white">
  <div class="container-narrow">
    <div class="bg-sage-50 border-2 border-sage-200 rounded-3xl p-8 text-center mb-12">
      <div class="w-16 h-16 bg-sage-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-sage-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
      </div>
      <h2 class="font-display font-bold text-2xl text-sage-800 mb-3">30-Day Money-Back Guarantee</h2>
      <p class="text-sage-700 text-lg">We stand behind every product we sell. If you're not completely satisfied within 30 days of delivery, we'll give you a full refund — no questions asked.</p>
    </div>
    <div class="prose prose-slate max-w-none">
      <h2>How to Request a Refund</h2>
      <ol>
        <li>Email <a href="mailto:support@dainely.com">support@dainely.com</a> with your order number</li>
        <li>We'll send a prepaid return label within 24 hours</li>
        <li>Return the item in its original packaging</li>
        <li>Your refund will be processed within 3–5 business days of us receiving the item</li>
      </ol>
      <h2>Refund Conditions</h2>
      <ul>
        <li>Request must be made within 30 days of delivery date</li>
        <li>Product must be returned in resalable condition</li>
        <li>Original packaging preferred but not required</li>
        <li>No explanation required — your satisfaction is our priority</li>
      </ul>
      <h2>Exchanges</h2>
      <p>Need a different size? We offer free exchanges within 30 days. Contact support and we'll arrange the exchange at no additional cost.</p>
      <h2>Damaged or Defective Items</h2>
      <p>If your product arrives damaged or defective, contact us immediately and we'll send a replacement at no charge. No return required for defective items.</p>
      <h2>Refund Timeline</h2>
      <p>Refunds are credited to your original payment method within 5–10 business days, depending on your bank.</p>
    </div>
  </div>
</section>
@endsection
