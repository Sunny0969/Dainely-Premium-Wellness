@extends('layouts.app')
@section('title', 'Understanding Sciatica: Causes, Symptoms & Relief | Dainely')
@section('meta_description', 'Complete guide to sciatica: why leg pain comes from your spine, the 3 root causes, and clinically proven decompression relief methods.')
@section('content')
{{-- Hero --}}
<section class="bg-navy-900 text-white py-16">
  <div class="container-site">
    <nav class="flex items-center gap-2 text-sm text-navy-300 mb-8">
      <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="hover:text-white transition-colors">Home</a>
      <span>/</span><a href="{{ route('education.back-pain', ['locale' => app()->getLocale()]) }}" class="hover:text-white transition-colors">Education</a>
      <span>/</span><span class="text-white">Sciatica</span>
    </nav>
    <div class="grid lg:grid-cols-2 gap-12 items-center">
      <div>
        <p class="eyebrow-light mb-4">Education Centre</p>
        <h1 class="font-display font-bold text-4xl lg:text-5xl text-white mb-6 leading-tight">The Science of Sciatica: Why Your <em class="text-gold-400">Leg</em> Hurts When Your Back Is the Problem</h1>
        <p class="text-navy-200 text-lg leading-relaxed mb-8">Sciatica affects 40% of people at some point in their lives, yet it remains widely misunderstood. Many patients treat the symptom — leg pain — without addressing the spinal compression causing it.</p>
        <div class="flex flex-wrap gap-4">
          <div class="flex items-center gap-2 text-navy-300 text-sm"><svg class="w-4 h-4 text-sage-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/></svg> 7 min read</div>
          <div class="flex items-center gap-2 text-navy-300 text-sm"><svg class="w-4 h-4 text-sage-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg> Dr. S. Laroche, DPT</div>
        </div>
      </div>
      <div class="relative">
        <div class="bg-navy-800 rounded-3xl overflow-hidden">
          <img src="{{ asset('images/sciatica-edu.png') }}" alt="Sciatic nerve anatomy" class="w-full h-80 object-cover opacity-90">
        </div>
      </div>
    </div>
  </div>
</section>
{{-- Stats --}}
<section class="bg-white py-10 border-b border-slate-100">
  <div class="container-site grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
    @foreach([['40%','Adults affected by sciatica'],['90%','Cases resolve with proper treatment'],['L4-S1','Most affected spinal levels'],['2-4 wk','Average recovery with decompression']] as [$v,$l])
    <div><p class="font-display font-bold text-3xl text-navy-900">{{ $v }}</p><p class="text-slate-500 text-sm mt-1">{{ $l }}</p></div>
    @endforeach
  </div>
</section>
{{-- Content --}}
<section class="section bg-white">
  <div class="container-site grid lg:grid-cols-3 gap-12">
    <div class="lg:col-span-2 prose prose-slate max-w-none">
      <h2>What Is Sciatica?</h2>
      <p>Sciatica is not a diagnosis — it's a symptom. The term describes pain, tingling, or numbness that travels along the path of the sciatic nerve, which runs from your lower back through your hips, buttocks, and down each leg.</p>
      <p>The pain originates in your spine, specifically when something compresses the sciatic nerve roots at lumbar levels L4 to S1. Understanding this distinction is crucial: treating only the leg where you feel pain ignores the spinal source of the problem.</p>
      <h2>The 3 Root Causes of Sciatica</h2>
      @foreach([['Herniated Disc (Most Common)','When the soft centre of a spinal disc bulges out and presses against sciatic nerve roots. Often triggered by sudden movements, heavy lifting, or prolonged poor posture.'],['Lumbar Spinal Stenosis','Narrowing of the spinal canal, often due to age-related bone spurs or thickened ligaments, which compresses the nerve roots.'],['Piriformis Syndrome','Spasm or tightening of the piriformis muscle in the buttock, which sits directly over the sciatic nerve. Often misdiagnosed as disc herniation.']] as [$title, $desc])
      <div class="not-prose mb-6 p-6 bg-navy-50 border-l-4 border-navy-500 rounded-r-2xl">
        <h3 class="font-semibold text-navy-900 text-lg mb-2">{{ $title }}</h3>
        <p class="text-slate-600">{{ $desc }}</p>
      </div>
      @endforeach
      <h2>Evidence-Based Treatments</h2>
      <p>The most effective treatments address the underlying spinal compression rather than just managing pain:</p>
      <ul>
        <li><strong>Lumbar decompression</strong> — traction-based relief that creates space between vertebrae</li>
        <li><strong>Targeted physiotherapy</strong> — McKenzie method and nerve mobilisation exercises</li>
        <li><strong>Anti-inflammatory protocols</strong> — reducing disc swelling naturally</li>
        <li><strong>Postural correction</strong> — eliminating the daily habits perpetuating the compression</li>
      </ul>
    </div>
    <div class="space-y-6">
      @include('partials.shopify-product-sidebar', [
        'product' => $product,
        'heading' => 'Recommended for Sciatica Relief',
        'description' => 'Clinically designed lumbar support to help relieve sciatic nerve compression at L4–S1.',
      ])
    </div>
  </div>
</section>
@endsection
