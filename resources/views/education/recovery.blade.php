@extends('layouts.app')
@section('title', 'Back Pain Recovery Protocol: 4-Week Plan | Dainely')
@section('content')
<section class="bg-navy-900 text-white py-16">
  <div class="container-site">
    <nav class="flex items-center gap-2 text-sm text-navy-300 mb-8">
      <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="hover:text-white transition-colors">Home</a>
      <span>/</span><span class="text-white">Recovery</span>
    </nav>
    <div class="grid lg:grid-cols-2 gap-12 items-center">
      <div>
        <p class="eyebrow-light mb-4">Education Centre</p>
        <h1 class="font-display font-bold text-4xl lg:text-5xl text-white mb-6 leading-tight">The 4-Week Back Pain <em class="text-gold-400">Recovery Protocol</em></h1>
        <p class="text-navy-200 text-lg leading-relaxed">A systematic, evidence-based protocol combining lumbar decompression, targeted mobility work, and postural retraining — developed by our physiotherapy advisory board.</p>
      </div>
      <div class="bg-navy-800 rounded-3xl overflow-hidden">
        <img src="{{ asset('images/spine-anatomy.png') }}" alt="Recovery protocol" class="w-full h-80 object-cover opacity-90">
      </div>
    </div>
  </div>
</section>
<section class="section bg-white">
  <div class="container-site grid lg:grid-cols-3 gap-12">
    <div class="lg:col-span-2">
      <h2 class="heading-section mb-8">The 4-Week Protocol</h2>
      @foreach([['Week 1','Foundation & Acute Relief','Focus on reducing inflammation and establishing decompression habits. Use the Dainely Belt 2–3 hours per day. Gentle cat-cow movements only. Ice for 15 min after any flare.','bg-navy-600'],['Week 2','Mobility Restoration','Begin adding knee-to-chest stretches and bird-dog. Extend belt use to 3–4 hours. Start 15-minute morning stretching routine. Walk 20 minutes daily on flat surfaces.','bg-sage-600'],['Week 3','Strength Building','Introduce hip flexor stretches and McKenzie extensions. Begin light core activation (dead bugs, bird-dog with resistance). Continue belt use during active periods.','bg-gold-500'],['Week 4','Return to Activity','Gradually return to normal activities. Belt as needed. Full mobility routine. Introduce swimming or cycling. Track progress using pain scale 0–10.','bg-navy-900']] as [$week,$title,$desc,$color])
      <div class="mb-6 p-6 bg-white border border-slate-200 rounded-2xl shadow-sm">
        <div class="flex items-start gap-4">
          <div class="w-16 h-16 {{ $color }} rounded-xl flex items-center justify-center flex-shrink-0">
            <span class="text-white font-bold text-xs text-center leading-tight px-1">{{ $week }}</span>
          </div>
          <div>
            <h3 class="font-semibold text-navy-900 text-xl mb-2">{{ $title }}</h3>
            <p class="text-slate-600">{{ $desc }}</p>
          </div>
        </div>
      </div>
      @endforeach
      <div class="mt-10 p-6 bg-navy-50 rounded-2xl">
        <h3 class="font-semibold text-navy-900 text-lg mb-3">Expected Outcomes</h3>
        <div class="grid md:grid-cols-3 gap-4">
          @foreach([['87%','Pain significantly reduced by week 4'],['94%','Return to normal activities by week 4'],['78%','Remain pain-free at 6-month follow-up']] as [$v,$l])
          <div class="text-center p-4 bg-white rounded-xl"><p class="font-bold text-2xl text-navy-900">{{ $v }}</p><p class="text-slate-500 text-sm mt-1">{{ $l }}</p></div>
          @endforeach
        </div>
      </div>
    </div>
    <div>
      @include('partials.shopify-product-sidebar', [
        'product' => $product,
        'heading' => 'Start Your Recovery Today',
        'description' => 'Support your recovery with clinically designed lumbar decompression.',
      ])
    </div>
  </div>
</section>
@endsection
