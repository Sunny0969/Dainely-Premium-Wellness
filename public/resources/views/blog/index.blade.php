@extends('layouts.app')
@section('title', 'Wellness Blog — Back Pain, Sciatica & Posture Guides | Dainely')
@section('meta_description', 'Expert articles on back pain relief, sciatica treatment, posture correction, and lumbar health from the Dainely medical team.')
@section('og_image', asset('images/blog-hero-back-pain.jpg'))

@section('content')

{{-- HERO --}}
<section class="relative overflow-hidden bg-gradient-to-br from-navy-900 to-navy-800 text-white py-20" aria-label="Blog hero">
  <div class="absolute inset-0 opacity-15">
    <img src="{{ asset('images/blog-hero-back-pain.jpg') }}" alt="" class="w-full h-full object-cover" aria-hidden="true">
  </div>
  <div class="absolute inset-0 bg-navy-900/70"></div>
  <div class="container-narrow relative z-10 text-center">
    <p class="eyebrow text-gold-400 mb-4">Expert Knowledge</p>
    <h1 class="font-display font-bold text-white mb-6" style="font-size:clamp(2rem,4vw,3rem);line-height:1.1">The Dainely Wellness Journal</h1>
    <p class="text-navy-200 text-lg max-w-xl mx-auto">Clinically accurate guides on back pain, sciatica, posture, and recovery — written by our medical advisory team.</p>
  </div>
</section>

{{-- CATEGORIES --}}
<section class="bg-white border-b border-slate-100" aria-label="Blog categories">
  <div class="container-site">
    <div class="flex items-center gap-2 overflow-x-auto scrollbar-hide py-4">
      @foreach(['All Articles', 'Back Pain', 'Sciatica', 'Posture', 'Neck Pain', 'Mobility', 'Recovery', 'Product Guides'] as $cat)
      <button class="whitespace-nowrap px-5 py-2 rounded-full text-sm font-semibold transition-all duration-200 {{ $loop->first ? 'bg-navy-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-navy-100 hover:text-navy-800' }}">
        {{ $cat }}
      </button>
      @endforeach
    </div>
  </div>
</section>

{{-- FEATURED ARTICLE --}}
<section class="section bg-white" aria-label="Featured article">
  <div class="container-site">
    <div class="card overflow-hidden group">
      <div class="grid lg:grid-cols-[1.5fr_1fr] gap-0">
        <div class="relative overflow-hidden max-h-96 lg:max-h-full">
          <img
            src="{{ asset('images/blog-hero-back-pain.jpg') }}"
            alt="The Root Cause of Chronic Back Pain"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
            loading="eager"
            width="760" height="480"
          >
          <div class="absolute inset-0 bg-gradient-to-r from-transparent to-white/0"></div>
          <div class="absolute top-5 left-5">
            <span class="product-badge bg-navy-700">Featured</span>
          </div>
        </div>
        <div class="p-8 lg:p-12 flex flex-col justify-center">
          <div class="flex items-center gap-2 mb-4">
            <span class="trust-badge bg-sage-50 border-sage-200 text-sage-700 text-xs">Back Pain</span>
            <span class="text-slate-400 text-xs">8 min read</span>
          </div>
          <h2 class="font-display font-bold text-navy-900 text-2xl lg:text-3xl mb-4 leading-tight">The Root Cause of Chronic Back Pain Most Doctors Miss</h2>
          <p class="text-slate-600 text-sm leading-relaxed mb-6">Over 80% of adults experience significant back pain at some point in their lives. Yet most treatments address only the symptoms — not the underlying biomechanical dysfunctions that keep pain coming back. Our physiotherapy consultants explain the real root causes and what actually works.</p>
          <div class="flex items-center gap-4 mb-6">
            <img src="{{ asset('images/trust-doctor.png') }}" alt="Dr. M. Reinholt" class="w-10 h-10 rounded-full object-cover">
            <div>
              <p class="text-slate-800 font-semibold text-sm">Dr. M. Reinholt</p>
              <p class="text-slate-400 text-xs">Physiotherapy Consultant · May 15, 2025</p>
            </div>
          </div>
          <a href="#" class="btn-primary self-start">Read Article</a>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ARTICLE GRID --}}
<section class="section bg-section-alt" aria-label="All articles">
  <div class="container-site">
    <div class="flex items-center justify-between mb-10">
      <h2 class="heading-section">Latest Articles</h2>
      <span class="text-slate-400 text-sm">Showing 6 of 24 articles</span>
    </div>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
      @foreach([
        ['The Science of Sciatica: Why Your Leg Hurts When Your Back Is the Problem', 'sciatica-edu.png', 'Sciatica', 'Dr. S. Laroche', '6 min read', 'Sciatica is notoriously misunderstood. Many patients treat their leg pain without addressing the spinal compression triggering it. Here is what the research actually shows.'],
        ['5 Posture Mistakes That Are Silently Destroying Your Spine', 'posture-edu.png', 'Posture', 'Dr. A. Müller', '5 min read', 'Poor posture is not just about how you look — it causes structural changes to your spine over time. These are the five most damaging habits our biomechanics team has identified.'],
        ['How Lumbar Decompression Belts Work: The Biomechanics Explained', 'dainely-belt-product.png', 'Product Guide', 'Dr. M. Reinholt', '7 min read', 'Not all back braces work the same way. This deep dive explains exactly how decompression belts differ from compression braces and why it matters for your recovery.'],
        ['Neck Pain & Upper Back Tension: The Hidden Spinal Connection', 'neck-pain-edu.png', 'Neck Pain', 'Dr. S. Laroche', '4 min read', 'Neck pain and lower back pain are often treated separately — but our spine is one connected structure. Understanding this changes everything about how to treat both.'],
        ['The 4-Week Back Pain Recovery Protocol: A Step-by-Step Guide', 'mobility-edu.png', 'Recovery', 'Dr. M. Reinholt', '10 min read', 'A systematic four-week protocol combining decompression therapy, targeted stretching, and postural retraining — developed by our clinical team for consistent results.'],
        ['Sciatica vs. Piriformis Syndrome: How to Tell the Difference', 'spine-anatomy.png', 'Sciatica', 'Dr. A. Müller', '6 min read', 'Both conditions cause leg pain, but they require different treatments. Misdiagnosis is common. Here is how to identify which condition you likely have.'],
      ] as [$title, $img, $category, $author, $readtime, $excerpt])
      <article class="card overflow-hidden group animate-on-scroll">
        <div class="overflow-hidden">
          <img
            src="{{ asset('images/' . $img) }}"
            alt="{{ $title }}"
            class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-500"
            loading="lazy"
            width="440" height="192"
          >
        </div>
        <div class="p-6">
          <div class="flex items-center gap-2 mb-3">
            <span class="trust-badge text-[10px] bg-navy-50 border-navy-100 text-navy-600">{{ $category }}</span>
            <span class="text-slate-400 text-xs">{{ $readtime }}</span>
          </div>
          <h3 class="font-display font-bold text-navy-900 text-lg mb-3 leading-snug group-hover:text-navy-700 transition-colors">{{ $title }}</h3>
          <p class="text-slate-500 text-sm leading-relaxed mb-4 line-clamp-3">{{ $excerpt }}</p>
          <div class="flex items-center justify-between pt-3 border-t border-slate-100">
            <div class="flex items-center gap-2">
              <img src="{{ asset('images/trust-doctor.png') }}" alt="{{ $author }}" class="w-7 h-7 rounded-full object-cover">
              <span class="text-slate-600 text-xs font-medium">{{ $author }}</span>
            </div>
            <a href="#" class="text-navy-600 text-sm font-semibold hover:text-navy-800 transition-colors">Read →</a>
          </div>
        </div>
      </article>
      @endforeach
    </div>

    {{-- Load more --}}
    <div class="text-center mt-12">
      <button class="btn-outline">Load More Articles</button>
    </div>
  </div>
</section>

{{-- Newsletter CTA --}}
<section class="section bg-gradient-to-br from-navy-900 to-navy-950 text-white" aria-label="Newsletter">
  <div class="container-narrow text-center">
    <p class="eyebrow text-gold-400 mb-4">Free Wellness Insights</p>
    <h2 class="heading-section text-white mb-4">Get Expert Back Health Tips Every Week</h2>
    <p class="text-navy-300 mb-8">Join 12,000+ subscribers receiving our weekly clinical insights on back pain, sciatica, and spinal health.</p>
    <form class="flex gap-3 max-w-md mx-auto" onsubmit="return false;">
      <input type="email" placeholder="Enter your email" class="form-input flex-1 bg-white/10 border-white/20 text-white placeholder-navy-400">
      <button type="submit" class="btn-gold-lg whitespace-nowrap">Subscribe Free</button>
    </form>
    <p class="text-navy-400 text-xs mt-4">No spam. Unsubscribe anytime. GDPR compliant.</p>
  </div>
</section>

@endsection
