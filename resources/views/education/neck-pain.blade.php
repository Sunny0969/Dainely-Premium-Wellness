@extends('layouts.app')
@section('title', 'Neck Pain & Cervical Spine Guide | Dainely')
@section('content')
<section class="bg-navy-900 text-white py-16">
  <div class="container-site">
    <nav class="flex items-center gap-2 text-sm text-navy-300 mb-8">
      <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="hover:text-white transition-colors">Home</a>
      <span>/</span><span class="text-white">Neck Pain</span>
    </nav>
    <div class="grid lg:grid-cols-2 gap-12 items-center">
      <div>
        <p class="eyebrow-light mb-4">Education Centre</p>
        <h1 class="font-display font-bold text-4xl lg:text-5xl text-white mb-6 leading-tight">Neck Pain & Upper Back Tension: The Hidden <em class="text-gold-400">Spinal Connection</em></h1>
        <p class="text-navy-200 text-lg leading-relaxed">Neck pain and lower back pain are treated separately by most practitioners — but your spine is one connected structure. What happens at the bottom affects the top, and vice versa.</p>
      </div>
      <div class="bg-navy-800 rounded-3xl overflow-hidden">
        <img src="{{ asset('images/neck-pain-edu.png') }}" alt="Neck pain anatomy" class="w-full h-80 object-cover opacity-90">
      </div>
    </div>
  </div>
</section>
<section class="section bg-white">
  <div class="container-site grid lg:grid-cols-3 gap-12">
    <div class="lg:col-span-2 prose prose-slate max-w-none">
      <h2>The Cervical Spine: Your Neck's 7 Vertebrae</h2>
      <p>The cervical spine consists of 7 vertebrae (C1–C7) supporting the weight of your head — approximately 5–6kg. When your head is in a neutral position over your shoulders, this is manageable. But forward head posture (increasingly common from screen use) dramatically increases this load.</p>
      <h2>Why Neck Pain and Lower Back Pain Are Connected</h2>
      <p>The thoracolumbar fascia — a dense connective tissue sheet — connects your lower back muscles to your upper back and neck. Tension or misalignment in the lower spine transmits directly through this fascial network to the cervical region. This is why many patients experience both lower back pain and chronic neck tension simultaneously.</p>
      <h2>Common Causes of Cervical Pain</h2>
      @foreach([['Tech Neck (Forward Head Posture)','Every inch of forward head displacement adds 4.5kg of cervical load. Looking at a phone with your head tilted 45° creates 22kg of effective pressure.'],['Cervical Disc Herniation','Similar to lumbar herniation, cervical discs can bulge and compress nerves, causing neck pain that radiates into the arm (cervicobrachialgia).'],['Upper Crossed Syndrome','Muscle imbalance pattern: tight pectorals and upper traps, weak deep neck flexors and lower traps. Creates the classic forward-rounded shoulder, head-forward posture.']] as [$t,$d])
      <div class="not-prose mb-5 p-6 bg-navy-50 border-l-4 border-navy-500 rounded-r-2xl">
        <h3 class="font-semibold text-navy-900 mb-2">{{ $t }}</h3>
        <p class="text-slate-600">{{ $d }}</p>
      </div>
      @endforeach
    </div>
    <div class="space-y-6">
      @include('partials.shopify-product-sidebar', [
        'product' => $product,
        'heading' => 'Address the Root Cause',
        'description' => 'Restoring lumbar alignment often relieves cervical tension throughout the entire spinal chain.',
      ])
    </div>
  </div>
</section>
@endsection
