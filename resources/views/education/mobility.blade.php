@extends('layouts.app')
@section('title', 'Mobility & Back Pain: Move Better, Hurt Less | Dainely')
@section('content')
<section class="bg-navy-900 text-white py-16">
  <div class="container-site">
    <nav class="flex items-center gap-2 text-sm text-navy-300 mb-8">
      <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="hover:text-white transition-colors">Home</a>
      <span>/</span><span class="text-white">Mobility</span>
    </nav>
    <div class="grid lg:grid-cols-2 gap-12 items-center">
      <div>
        <p class="eyebrow-light mb-4">Education Centre</p>
        <h1 class="font-display font-bold text-4xl lg:text-5xl text-white mb-6 leading-tight">Lumbar Mobility: Why <em class="text-gold-400">Moving More</em> Is the Best Medicine</h1>
        <p class="text-navy-200 text-lg leading-relaxed">Contrary to what many believe, rest is often the worst treatment for back pain. Evidence shows controlled movement and mobility work accelerates recovery and prevents recurrence.</p>
      </div>
      <div class="bg-navy-800 rounded-3xl overflow-hidden">
        <img src="{{ asset('images/mobility-edu.png') }}" alt="Back mobility exercises" class="w-full h-80 object-cover opacity-90">
      </div>
    </div>
  </div>
</section>
<section class="section bg-white">
  <div class="container-site grid lg:grid-cols-3 gap-12">
    <div class="lg:col-span-2 prose prose-slate max-w-none">
      <h2>The Movement Paradox in Back Pain</h2>
      <p>When back pain strikes, the instinct is to rest and protect. But research consistently shows that early controlled movement — within 24–48 hours of a pain episode — leads to significantly faster recovery than bed rest.</p>
      <p>The key is <em>controlled</em> movement: mobilising the spine through safe ranges of motion rather than either complete rest or aggressive loading.</p>
      <h2>5 Evidence-Based Mobility Exercises</h2>
      @foreach([['Cat-Cow','4 sets of 10 reps, morning and evening. Gently alternates lumbar flexion and extension, mobilising each intervertebral segment and pumping synovial fluid into the facet joints.'],['Knee-to-Chest','Hold 30 seconds each side. Decompresses the lower lumbar segments and stretches the piriformis and gluteal muscles.'],['Bird-Dog','3 sets of 8 each side. Activates deep core stabilisers (multifidus and transversus abdominis) without loading the spine.'],['McKenzie Extension','5 press-ups per set, 3 sets. Clinically proven to reduce disc herniation symptoms by centralising pain.'],['Hip Flexor Stretch','60 seconds each side. Releases iliopsoas tension which pulls the lumbar spine into anterior tilt, increasing disc compression.']] as [$name,$desc])
      <div class="not-prose mb-5 p-5 bg-sage-50 rounded-2xl border border-sage-200">
        <h3 class="font-semibold text-sage-800 mb-2">{{ $name }}</h3>
        <p class="text-slate-600 text-sm">{{ $desc }}</p>
      </div>
      @endforeach
    </div>
    <div>
      @if($product)
      <div class="card p-6 sticky top-24">
        <img src="{{ asset($product->main_image) }}" alt="Dainely Belt" class="w-full h-48 object-cover rounded-xl mb-5">
        <h3 class="font-semibold text-navy-900 text-lg mb-2">Accelerate Your Recovery</h3>
        <p class="text-sm text-slate-600 mb-4">Use the Dainely Belt during mobility sessions to support the spine and allow deeper, safer movement ranges.</p>
        <div class="flex items-center gap-2 mb-5">
          <span class="font-bold text-2xl text-navy-900">${{ number_format($product->price_usd, 2) }}</span>
          @if($product->compare_price_usd)<span class="text-slate-400 line-through">${{ number_format($product->compare_price_usd, 2) }}</span>@endif
        </div>
        <a href="{{ route('checkout.index', ['locale' => app()->getLocale()]) }}" class="btn-gold-lg w-full justify-center">Buy Now — Free Shipping</a>
      </div>
      @endif
    </div>
  </div>
</section>
@endsection
