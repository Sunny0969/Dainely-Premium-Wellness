@extends('layouts.app')
@section('title', 'About Dainely — Our Mission, Science & Story')
@section('meta_description', 'Learn how Dainely was founded by wellness professionals to solve chronic back pain with medical-grade, clinically developed solutions trusted by 50,000+ customers.')
@section('og_image', asset('images/about-team.jpg'))

@section('content')

{{-- HERO --}}
<section class="relative overflow-hidden bg-gradient-to-br from-navy-950 to-navy-800 text-white py-24" aria-label="About hero">
  <div class="absolute inset-0 opacity-20">
    <img src="{{ asset('images/about-mission.png') }}" alt="" class="w-full h-full object-cover" aria-hidden="true">
  </div>
  <div class="absolute inset-0 bg-navy-950/60"></div>
  <div class="container-narrow relative z-10 text-center">
    <p class="eyebrow text-gold-400 mb-4">Our Story</p>
    <h1 class="font-display font-bold text-white mb-6" style="font-size:clamp(2.5rem,5vw,4rem);line-height:1.1">Built by People Who Know Back Pain</h1>
    <p class="text-navy-200 text-lg leading-relaxed max-w-2xl mx-auto">Dainely was founded when our team couldn't find a product that addressed the root cause of chronic back pain — only ones that masked it. So we built one.</p>
  </div>
</section>

{{-- MISSION --}}
<section class="section bg-white" aria-label="Mission">
  <div class="container-site">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <div>
        <p class="eyebrow mb-4">Our Mission</p>
        <h2 class="heading-section mb-6">Wellness Rooted in Medical Science</h2>
        <p class="text-body mb-5">Most back pain products are designed by marketers, not medical professionals. Dainely is different. Every product in our range is co-developed with physiotherapists, spine specialists, and orthopedic consultants who brought their clinical experience to the design process.</p>
        <p class="text-body mb-8">We believe long-term relief only comes from addressing root causes — disc compression, nerve inflammation, postural imbalance — not just numbing the pain temporarily.</p>
        <div class="grid grid-cols-2 gap-4">
          @foreach([['50,000+','Customers Helped'],['4.8★','Trustpilot Rating'],['30 Day','Money-Back Guarantee'],['3 Years','Clinical Development']] as [$val,$label])
          <div class="bg-navy-50 rounded-2xl p-5">
            <p class="font-display font-bold text-3xl text-navy-900">{{ $val }}</p>
            <p class="text-slate-500 text-sm mt-1">{{ $label }}</p>
          </div>
          @endforeach
        </div>
      </div>
      <div class="relative">
        <div class="absolute -inset-4 bg-gold-400/10 blur-3xl rounded-3xl"></div>
        <img src="{{ asset('images/about-mission.png') }}" alt="Dainely wellness studio — clean modern clinical environment" class="relative z-10 w-full rounded-3xl shadow-strong" loading="lazy" width="640" height="480">
      </div>
    </div>
  </div>
</section>

{{-- TEAM --}}
<section class="section bg-section-alt" aria-label="Our team">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow mb-3">The Team</p>
      <h2 class="heading-section mb-4">Medical Expertise Meets Wellness Innovation</h2>
      <p class="text-lead max-w-xl mx-auto">Our advisory team includes board-certified physiotherapists, orthopedic specialists, and wellness researchers from across Europe and North America.</p>
    </div>
    <div class="grid md:grid-cols-2 gap-8 items-center">
      <div class="relative">
        <img src="{{ asset('images/about-team.jpg') }}" alt="Dainely medical advisory team in collaborative session" class="w-full rounded-3xl shadow-medium" loading="lazy" width="640" height="420">
        <div class="absolute bottom-5 left-5 right-5 bg-white/95 backdrop-blur-sm rounded-2xl p-4">
          <p class="text-navy-900 font-semibold text-sm mb-1">Our Medical Advisory Board</p>
          <p class="text-slate-500 text-xs">Board-certified physiotherapists and orthopedic consultants from 6 countries guide every product development decision.</p>
        </div>
      </div>
      <div class="space-y-6">
        @foreach([
          ['Dr. M. Reinholt', 'Lead Physiotherapy Consultant', 'MSc Physiotherapy · 20 years clinical experience in lumbar rehabilitation'],
          ['Dr. S. Laroche', 'Orthopedic Specialist', 'MD Orthopedics · Specializing in non-surgical sciatica treatment'],
          ['Dr. A. Müller', 'Biomechanics Researcher', 'PhD Biomechanics · Published researcher in spinal load distribution'],
        ] as [$name, $role, $bio])
        <div class="flex items-start gap-4 card p-5">
          <img src="{{ asset('images/trust-doctor.png') }}" alt="{{ $name }}" class="w-14 h-14 rounded-2xl object-cover flex-shrink-0" loading="lazy" width="56" height="56">
          <div>
            <p class="font-display font-bold text-navy-900 text-base">{{ $name }}</p>
            <p class="text-gold-600 text-sm font-semibold mb-1">{{ $role }}</p>
            <p class="text-slate-500 text-xs leading-relaxed">{{ $bio }}</p>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</section>

{{-- VALUES --}}
<section class="section bg-gradient-to-br from-navy-900 to-navy-950 text-white" aria-label="Our values">
  <div class="container-site">
    <div class="text-center mb-12">
      <p class="eyebrow text-gold-400 mb-3">What We Stand For</p>
      <h2 class="heading-section text-white mb-4">Our Core Values</h2>
    </div>
    <div class="grid md:grid-cols-3 gap-6">
      @foreach([
        ['Science First','Every claim we make is backed by clinical research and validated by our medical advisory board. No pseudoscience, no gimmicks.','M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ['Radical Transparency','We publish our clinical development process, our materials, and our real customer data — including the negative reviews.','M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'],
        ['Long-Term Results','We measure success by customer outcomes at 6 weeks, 3 months, and 1 year — not just initial sales. Our guarantee reflects our confidence.','M13 10V3L4 14h7v7l9-11h-7z'],
      ] as [$title, $desc, $icon])
      <div class="card-glass rounded-2xl p-8">
        <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center mb-5">
          <svg class="w-6 h-6 text-gold-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
        </div>
        <h3 class="font-display font-bold text-white text-xl mb-3">{{ $title }}</h3>
        <p class="text-navy-300 text-sm leading-relaxed">{{ $desc }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- CTA --}}
<section class="section bg-white text-center" aria-label="About CTA">
  <div class="container-narrow">
    <h2 class="heading-section mb-4">Ready to Experience the Difference?</h2>
    <p class="text-lead mb-8">Join 50,000+ people who chose science-backed wellness over quick fixes.</p>
    <a href="#" class="btn-primary-lg">Shop Dainely Belt — $89</a>
    <p class="text-slate-400 text-sm mt-4">30-day money-back guarantee · Free shipping over $75</p>
  </div>
</section>

@endsection
