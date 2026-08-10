@extends('layouts.app')
@section('title', 'Back Pain: Causes, Diagnosis & Evidence-Based Relief | Dainely')
@section('meta_description', 'Comprehensive medical guide to understanding chronic back pain — causes, risk factors, and clinically proven treatment approaches from our physiotherapy team.')
@section('og_image', asset('images/lifestyle-everyday-movement.webp'))

@section('content')

{{-- HERO --}}
<section class="relative overflow-hidden bg-gradient-to-br from-navy-950 to-navy-800 text-white py-20" aria-label="Back pain education hero">
  <div class="container-site relative z-10">
    <div class="grid lg:grid-cols-2 gap-12 items-center">
      <div>
        <nav class="flex items-center gap-2 text-navy-400 text-sm mb-6">
          <a href="#" class="hover:text-white">Education</a>
          <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          <span class="text-white">Back Pain</span>
        </nav>
        <p class="eyebrow text-gold-400 mb-4">Medical Education Service</p>
        <h1 class="font-display font-bold text-white mb-6" style="font-size:clamp(2rem,4vw,3.25rem);line-height:1.1">
          Understanding Chronic Back Pain
        </h1>
        <p class="text-navy-200 text-lg leading-relaxed mb-8">A comprehensive clinical guide covering the root causes, risk factors, and evidence-based approaches to lasting back pain relief.</p>
        <div class="flex items-center gap-4">
          <img src="{{ asset('images/trust-doctor.png') }}" alt="Dr. M. Reinholt" class="w-12 h-12 rounded-full object-cover ring-2 ring-white/20">
          <div>
            <p class="text-white font-semibold text-sm">Dr. M. Reinholt</p>
            <p class="text-navy-300 text-xs">Physiotherapy Consultant · 12 min read</p>
          </div>
        </div>
      </div>
      <div class="relative hidden lg:block">
        <div class="absolute inset-0 bg-gold-400/10 blur-3xl rounded-full"></div>
        <img
          src="{{ asset('images/lifestyle-everyday-movement.webp') }}"
          alt="Back pain anatomy illustration showing affected areas"
          class="relative z-10 w-full max-w-md mx-auto rounded-3xl shadow-gold"
          loading="eager"
          width="500" height="450"
        >
      </div>
    </div>
  </div>
</section>

{{-- KEY STATS --}}
<section class="bg-white border-b border-slate-100" aria-label="Back pain statistics">
  <div class="container-site py-10">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
      @foreach([
        ['80%', 'of adults will experience significant back pain in their lifetime'],
        ['#1', 'cause of disability worldwide, affecting 619 million people'],
        ['6 weeks', 'average time before chronic pain classification sets in'],
        ['3×', 'more effective — decompression vs. standard pain medication alone'],
      ] as [$stat, $label])
      <div class="text-center">
        <p class="font-display font-bold text-4xl text-navy-900 mb-2">{{ $stat }}</p>
        <p class="text-slate-500 text-sm">{{ $label }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ARTICLE CONTENT --}}
<section class="section bg-white" aria-label="Article content">
  <div class="container-site">
    <div class="grid lg:grid-cols-[1fr_340px] gap-12">

      {{-- Main content --}}
      <article class="prose prose-slate max-w-none" style="font-size:1rem;line-height:1.8">

        <h2 style="font-size:1.75rem;font-weight:700;color:#0f172a;margin-bottom:1rem">What Is Chronic Back Pain?</h2>
        <p>Back pain becomes chronic when it persists for more than 12 weeks — even after the initial cause has been treated. At this point, the nervous system has often adapted to a pain state, making recovery more complex than simply addressing the original injury.</p>
        <p>The lumbar spine — the lower five vertebrae (L1–L5) — bears the majority of the body's mechanical load. When disc integrity, vertebral alignment, or surrounding musculature is compromised, the cascade of effects can extend far beyond local pain, causing referred pain down the legs (sciatica), reduced mobility, and significant quality-of-life impact.</p>

        <div class="not-prose bg-navy-50 rounded-2xl p-6 my-8">
          <div class="flex gap-4">
            <img src="{{ asset('images/lifestyle-everyday-movement.webp') }}" alt="Back pain illustration" class="w-32 h-32 object-cover rounded-xl flex-shrink-0">
            <div>
              <h3 class="font-display font-bold text-navy-900 text-lg mb-2">The Root Cause Distinction</h3>
              <p class="text-slate-600 text-sm leading-relaxed">Most back pain treatments target peripheral symptoms (muscle spasm, inflammation) rather than the underlying structural issue. Without addressing disc compression, vertebral misalignment, or muscular imbalance, pain consistently returns within weeks of treatment ending.</p>
            </div>
          </div>
        </div>

        <h2 style="font-size:1.75rem;font-weight:700;color:#0f172a;margin-bottom:1rem">Primary Causes of Lumbar Back Pain</h2>
        <p>Back pain rarely has a single cause. Most chronic cases involve a combination of structural, muscular, and lifestyle factors that compound over time:</p>

        @foreach([
          ['Intervertebral Disc Degeneration', 'The discs between vertebrae act as shock absorbers. When they lose water content and height with age or overuse, vertebrae compress closer together — reducing space for spinal nerves and increasing pain signals.'],
          ['Disc Herniation', 'When a disc\'s outer ring cracks, the softer inner nucleus can protrude and press directly on spinal nerves. This is a primary cause of sciatica — the shooting leg pain many back pain sufferers experience.'],
          ['Muscular Imbalance & Weakness', 'Modern sedentary lifestyles create predictable patterns of muscular imbalance: tight hip flexors, weak glutes, and overstressed lumbar erectors. This alters spinal loading in ways that accelerate disc degeneration.'],
          ['Spinal Stenosis', 'A narrowing of the spinal canal that compresses nerve roots, causing pain, numbness, and weakness — especially in older adults. Often presents alongside degenerative disc disease.'],
          ['Poor Postural Habits', 'Extended periods of flexion-dominant posture (desk work, driving, phone use) create sustained compressive forces on lumbar discs and habituate the spinal extensors into a weakened, lengthened state.'],
        ] as [$cause, $desc])
        <div class="not-prose flex gap-4 my-4 p-4 bg-slate-50 rounded-xl">
          <div class="w-2 bg-navy-600 rounded-full flex-shrink-0"></div>
          <div>
            <h4 class="font-semibold text-navy-900 text-base mb-1">{{ $cause }}</h4>
            <p class="text-slate-600 text-sm leading-relaxed">{{ $desc }}</p>
          </div>
        </div>
        @endforeach

        <h2 style="font-size:1.75rem;font-weight:700;color:#0f172a;margin:2rem 0 1rem">Evidence-Based Treatment Approaches</h2>
        <p>The research is clear: multi-modal approaches consistently outperform single interventions. The most effective protocols combine decompression, targeted exercise, and postural retraining.</p>

        <div class="not-prose grid sm:grid-cols-2 gap-4 my-6">
          @foreach([
            ['Lumbar Decompression', 'Directly addresses disc compression — the root cause in most cases. Clinical studies show 60–70% pain reduction in 4 weeks when applied consistently.', 'sage'],
            ['Targeted Physiotherapy', 'Progressive loading of the lumbar stabilisers — multifidus, transverse abdominis — rebuilds the muscular support structure around the spine.', 'navy'],
            ['Postural Retraining', 'Correcting habitual posture reduces the sustained compressive forces that perpetuate disc degeneration. Essential for long-term results.', 'gold'],
            ['Anti-inflammatory Nutrition', 'Omega-3 fatty acids, curcumin, and reduced processed food intake measurably reduce systemic inflammation that amplifies pain signalling.', 'sage'],
          ] as [$treatment, $desc, $color])
          <div class="card p-5">
            <div class="w-8 h-8 bg-{{ $color }}-100 rounded-lg flex items-center justify-center mb-3">
              <svg class="w-4 h-4 text-{{ $color }}-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l3-3z" clip-rule="evenodd"/></svg>
            </div>
            <h4 class="font-semibold text-navy-900 text-sm mb-2">{{ $treatment }}</h4>
            <p class="text-slate-500 text-xs leading-relaxed">{{ $desc }}</p>
          </div>
          @endforeach
        </div>

      </article>

      {{-- Sidebar --}}
      <aside class="space-y-6 sticky top-24">
        @include('partials.shopify-product-sidebar', [
          'product' => $product,
          'heading' => 'Recommended for Back Pain Relief',
          'description' => 'Clinical-grade lumbar decompression from our live Shopify catalog.',
        ])

        {{-- Doctor quote --}}
        <div class="card p-5">
          <div class="flex items-start gap-3 mb-3">
            <img src="{{ asset('images/trust-doctor.png') }}" alt="Dr. M. Reinholt" class="w-12 h-12 rounded-xl object-cover flex-shrink-0" loading="lazy">
            <div>
              <p class="font-bold text-navy-900 text-sm">Dr. M. Reinholt</p>
              <p class="text-slate-400 text-xs">Physiotherapy Consultant</p>
            </div>
          </div>
          <p class="text-slate-600 text-sm italic leading-relaxed">"In 20 years of clinical practice, the combination of lumbar decompression and targeted exercise has consistently outperformed pharmaceutical approaches for chronic non-specific back pain."</p>
        </div>

        {{-- Related topics --}}
        <div class="card p-5">
          <h4 class="font-semibold text-navy-900 mb-3">Related Topics</h4>
          <div class="space-y-2">
            @foreach(['Sciatica Relief', 'Posture Correction', 'Neck Pain', 'Lumbar Decompression', 'Recovery Protocol'] as $topic)
            <a href="#" class="flex items-center justify-between py-2 text-sm text-slate-600 hover:text-navy-700 border-b border-slate-50 last:border-0 transition-colors">
              {{ $topic }}
              <svg class="w-4 h-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endforeach
          </div>
        </div>
      </aside>
    </div>
  </div>
</section>

{{-- CTA BANNER --}}
<section class="section bg-gradient-to-br from-navy-900 to-navy-950 text-white" aria-label="Product CTA">
  <div class="container-narrow text-center">
    <img src="{{ asset('images/spine-anatomy.png') }}" alt="Dainely spine" class="w-32 h-32 object-cover rounded-full mx-auto mb-6 ring-4 ring-white/20" loading="lazy">
    <h2 class="heading-section text-white mb-4">Ready to Address the Root Cause?</h2>
    <p class="text-navy-300 mb-8">The Dainely Belt was built specifically for the biomechanical issues described in this article. Join 50,000+ customers who chose science over symptom masking.</p>
    <a href="#" class="btn-gold-lg">Shop Dainely Belt — $89</a>
    <p class="text-navy-400 text-sm mt-4">30-day guarantee · Free shipping over {{ app(\App\Services\CurrencyService::class)->formatForLocale(app(\App\Services\CurrencyService::class)->freeShippingThresholdUsd(), app()->getLocale()) }}</p>
  </div>
</section>

@include('partials.education-cms')

@include('components.related-content', [
  'title' => __('Related Resources'),
  'links' => $relatedLinks ?? [],
])

@endsection
