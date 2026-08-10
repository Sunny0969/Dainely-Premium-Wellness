@extends('layouts.app')
@section('title', 'FAQ — Frequently Asked Questions | Dainely')
@section('meta_description', 'Find answers to common questions about the Dainely Belt, shipping, returns, sizing, and how our products work to relieve back pain and sciatica.')

@section('content')

{{-- HERO --}}
<section class="bg-gradient-to-br from-navy-900 to-navy-800 text-white py-16" aria-label="FAQ hero">
  <div class="container-narrow text-center">
    <p class="eyebrow text-gold-400 mb-4">Support Centre</p>
    <h1 class="font-display font-bold text-4xl text-white mb-4">Frequently Asked Questions</h1>
    <p class="text-navy-200 text-lg">Everything you need to know about Dainely products, shipping, and returns.</p>
  </div>
</section>

{{-- FAQ CONTENT --}}
<section class="section bg-white" aria-label="FAQ list">
  <div class="container-narrow">

    {{-- Search bar --}}
    <div class="relative mb-12">
      <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      <input type="text" id="faq-search" placeholder="Search questions..." class="form-input pl-12 w-full" oninput="filterFAQ(this.value)">
    </div>

@inject('jsonLd', 'App\Services\JsonLdBuilder')

@php
$freeShipFaqAmount = app(\App\Services\CurrencyService::class)->formatForLocale(
    app(\App\Services\CurrencyService::class)->freeShippingThresholdUsd(),
    app()->getLocale()
);
$faqs = [
  'Product & Science' => [
    ['How does the Dainely Belt relieve back pain?', 'The Dainely Belt uses targeted lumbar decompression — inflatable air cells gently separate the vertebrae, reducing disc pressure and sciatic nerve compression. This addresses the root cause rather than masking symptoms.'],
    ['How quickly will I see results?', 'Most customers report meaningful pain reduction within 7–14 days of consistent daily use (2–3 hours per day). 87% of users report measurable improvement within 4 weeks.'],
    ['Is the Dainely Belt clinically validated?', 'Yes. The belt was co-developed with board-certified physiotherapists over 3 years. Our design is based on peer-reviewed research on lumbar support mechanics and sciatica nerve decompression.'],
    ['Can I wear it while working at a desk?', 'Absolutely. The belt is designed for extended wear — the breathable fabric and adjustable compression make it comfortable for 2–4 hours of seated use. Many customers wear it during their work day.'],
    ['Does it work for sciatica specifically?', 'Yes. By reducing lumbar disc pressure, the belt directly alleviates the nerve compression that causes sciatic pain. Many customers with diagnosed sciatica report significant relief within 2 weeks.'],
  ],
  'Sizing & Fit' => [
    ['How do I choose my size?', 'Measure your waist circumference at the belly button level. S/M fits 28"–36", L/XL fits 37"–44", 2XL fits 45"–52", 3XL fits 53"+. When in doubt, size up for comfort.'],
    ['What if the size doesn\'t fit?', 'We offer free exchanges within 60 days of purchase. Simply contact support with your order number and preferred size — we handle the rest.'],
    ['Can I wash the Dainely Belt?', 'Yes. Hand wash in cold water with mild detergent and air dry. Do not machine wash or tumble dry, as this may affect the air cell integrity.'],
  ],
  'Shipping & Delivery' => [
    ['Where do you ship to?', 'We ship worldwide. Free standard shipping is available on all orders over '.$freeShipFaqAmount.'. Express and tracked options are available at checkout.'],
    ['How long does delivery take?', 'USA & Canada: 3–5 business days. Europe: 5–8 business days. Rest of World: 7–14 business days. Expedited options available at checkout.'],
    ['Do you ship to my country?', 'We ship to 80+ countries. All available shipping destinations are shown at checkout. If yours is not listed, contact support — we can usually arrange delivery.'],
  ],
  'Returns & Guarantee' => [
    ['What is your return policy?', 'We offer a full 30-day money-back guarantee. If you are not completely satisfied, contact our support team within 60 days of delivery for a full refund — no questions asked.'],
    ['How do I start a return?', 'Email ' . config('company.email') . ' with your order number and reason (optional). We will send a prepaid return label within 24 hours and process your refund within 3–5 business days of receiving the item.'],
    ['Are there any conditions on the guarantee?', 'The only requirement is that the product is returned in resalable condition (original packaging). We do not require proof of defect or explanation for the refund.'],
  ],
];

// Flatten FAQs array for JSON-LD generation
$flatFaqs = collect();
foreach ($faqs as $category => $items) {
    foreach ($items as $item) {
        $flatFaqs->push((object)[
            'question' => $item[0],
            'answer' => $item[1]
        ]);
    }
}
$faqJsonLd = $jsonLd->buildFaqSchema($flatFaqs);
@endphp

@push('json-ld')
<script type="application/ld+json">
{!! $faqJsonLd !!}
</script>
@endpush

@foreach($faqs as $category => $items)
<div class="mb-10">
  <h2 class="font-display font-bold text-navy-900 text-2xl mb-5 flex items-center gap-3">
    <span class="w-8 h-0.5 bg-gold-400 inline-block"></span>
    {{ $category }}
  </h2>
  <div class="space-y-3">
    @foreach($items as $index => [$question, $answer])
    <details class="faq-item group border border-slate-100 rounded-xl overflow-hidden bg-slate-50 transition-all duration-200" id="faq-{{ Str::slug($question) }}">
      <summary class="faq-trigger w-full flex items-center justify-between gap-4 p-5 cursor-pointer list-none select-none font-semibold text-slate-800 text-left text-base">
        <span>{{ $question }}</span>
        <svg class="w-5 h-5 text-navy-600 flex-shrink-0 transition-transform duration-300 group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
      </summary>
      <div class="faq-content p-5 pt-0 bg-slate-50 text-slate-600 text-sm leading-relaxed">
        <p>{{ $answer }}</p>
      </div>
    </details>
    @endforeach
  </div>
</div>
@endforeach

    {{-- Still have questions CTA --}}
    <div class="mt-12 bg-navy-50 rounded-3xl p-8 text-center">
      <svg class="w-12 h-12 text-navy-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
      <h3 class="heading-card mb-2">Still Have Questions?</h3>
      <p class="text-body mb-6">Our wellness specialists are available Monday–Friday, 9am–6pm EST.</p>
      <div class="flex flex-wrap justify-center gap-4">
        <a href="#" class="btn-primary">Chat with Us</a>
        <a href="mailto:{{ config('company.email') }}" class="btn-outline">Email Support</a>
      </div>
    </div>
  </div>
</section>

@push('scripts')
<script>
function filterFAQ(query) {
  query = query.toLowerCase();
  document.querySelectorAll('.faq-item').forEach(item => {
    const text = item.querySelector('button span').textContent.toLowerCase();
    item.style.display = text.includes(query) ? 'block' : 'none';
  });
}
</script>
@endpush

@endsection
