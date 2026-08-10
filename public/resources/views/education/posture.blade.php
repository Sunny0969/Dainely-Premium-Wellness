@extends('layouts.app')
@section('title', 'Posture Correction: Fix Bad Posture & Reverse Spinal Damage | Dainely')
@section('meta_description', 'Understand how poor posture causes structural spinal changes and how to reverse them. Evidence-based posture correction guide by Dainely physiotherapists.')
@section('content')
<section class="bg-navy-900 text-white py-16">
  <div class="container-site">
    <nav class="flex items-center gap-2 text-sm text-navy-300 mb-8">
      <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="hover:text-white transition-colors">Home</a>
      <span>/</span><span class="text-white">Posture Education</span>
    </nav>
    <div class="grid lg:grid-cols-2 gap-12 items-center">
      <div>
        <p class="eyebrow-light mb-4">Education Centre</p>
        <h1 class="font-display font-bold text-4xl lg:text-5xl text-white mb-6 leading-tight">5 Posture Mistakes Silently <em class="text-gold-400">Destroying</em> Your Spine</h1>
        <p class="text-navy-200 text-lg leading-relaxed">Poor posture is not just aesthetic — it causes measurable structural changes to your spine over time. The good news: most of the damage is reversible with the right approach.</p>
      </div>
      <div class="bg-navy-800 rounded-3xl overflow-hidden">
        <img src="{{ asset('images/posture-edu.png') }}" alt="Posture correction guide" class="w-full h-80 object-cover opacity-90">
      </div>
    </div>
  </div>
</section>
<section class="section bg-white">
  <div class="container-site grid lg:grid-cols-3 gap-12">
    <div class="lg:col-span-2 prose prose-slate max-w-none">
      <h2>Why Posture Matters More Than You Think</h2>
      <p>Every centimetre your head moves forward relative to your shoulders adds approximately 4.5kg of effective load on your cervical spine. Over years, this changes the curvature of your spine, compresses discs, and can lead to chronic pain, nerve compression, and even reduced lung capacity.</p>
      <h2>The 5 Most Damaging Posture Habits</h2>
      @foreach([['Forward Head Position','Looking down at your phone. For every inch your head moves forward, neck and upper back muscles must work 3x harder to support it. Over years, this flattens the natural cervical curve.'],['Desk Slouch','Sitting with a rounded lower back flattens the lumbar curve and increases disc compression by 40% compared to standing. 8 hours a day of this causes structural changes within months.'],['Crossed Legs','Creates pelvic tilt which cascades up the spine, causing compensatory curvature in the lumbar and thoracic regions. Also compresses hip flexors and piriformis.'],['Flat-Footed Walking','Lack of arch support changes the biomechanics of your entire kinetic chain, transmitting excess impact force directly to your lumbar discs.'],['Stomach Sleeping','Forced neck rotation and eliminated lumbar curve. This position causes sustained muscle tension and disc compression throughout the night.']] as [$title,$desc])
      <div class="not-prose mb-6 p-6 bg-slate-50 border-l-4 border-gold-400 rounded-r-2xl">
        <h3 class="font-semibold text-navy-900 text-lg mb-2">{{ $title }}</h3>
        <p class="text-slate-600">{{ $desc }}</p>
      </div>
      @endforeach
      <h2>Evidence-Based Posture Correction</h2>
      <p>The most effective posture correction strategies combine: lumbar support to restore natural curves, targeted strengthening of deep core stabilisers, regular decompression to reverse disc compression, and habit reformation protocols.</p>
    </div>
    <div class="space-y-6">
      @include('partials.shopify-product-sidebar', [
        'product' => $product,
        'heading' => 'Correct Your Posture',
        'description' => 'Restore natural lumbar alignment — the foundation of correct spinal posture.',
      ])
    </div>
  </div>
</section>
@include('partials.education-cms')

@include('components.related-content', [
  'title' => __('Related Resources'),
  'links' => $relatedLinks ?? [],
])

@endsection
