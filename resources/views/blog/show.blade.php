@extends('layouts.app')
@section('title', ($article['title'] ?? 'Blog') . ' | Dainely')
@section('meta_description', $article['excerpt'] ?? '')
@section('content')
{{-- Hero --}}
<section class="bg-navy-900 text-white py-14">
  <div class="container-narrow">
    <nav class="flex items-center gap-2 text-sm text-navy-300 mb-8">
      <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="hover:text-white transition-colors">Home</a>
      <span>/</span>
      <a href="{{ route('blog.index', ['locale' => app()->getLocale()]) }}" class="hover:text-white transition-colors">Blog</a>
      <span>/</span>
      <span class="text-white truncate max-w-xs">{{ $article['title'] }}</span>
    </nav>
    <span class="inline-block bg-navy-700 text-navy-200 text-xs font-bold uppercase tracking-widest px-3 py-1.5 rounded-full mb-5">{{ $article['category'] }}</span>
    <h1 class="font-display font-bold text-3xl lg:text-5xl text-white leading-tight mb-6">{{ $article['title'] }}</h1>
    <div class="flex flex-wrap items-center gap-6 text-navy-300 text-sm">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 bg-navy-700 rounded-full flex items-center justify-center">
          <svg class="w-4 h-4 text-navy-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
        </div>
        <span>{{ $article['author'] }}</span>
      </div>
      <div class="flex items-center gap-1.5">
        <svg class="w-4 h-4 text-navy-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
        <span>{{ $article['date'] }}</span>
      </div>
      <div class="flex items-center gap-1.5">
        <svg class="w-4 h-4 text-navy-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
        <span>{{ $article['readtime'] }}</span>
      </div>
    </div>
  </div>
</section>

{{-- Featured image --}}
<div class="container-narrow -mt-8 mb-0 relative z-10">
  <div class="rounded-3xl overflow-hidden shadow-xl">
    <img src="{{ asset('images/' . $article['image']) }}" alt="{{ $article['title'] }}" class="w-full h-72 md:h-96 object-cover">
  </div>
</div>

{{-- Article content --}}
<section class="section bg-white">
  <div class="container-narrow">
    <div class="grid lg:grid-cols-3 gap-12">
      {{-- Main article --}}
      <article class="lg:col-span-2 prose prose-slate prose-lg max-w-none">
        <p class="lead">{{ $article['excerpt'] }}</p>
        <h2>Understanding the Root Cause</h2>
        <p>Most conventional treatments for {{ strtolower($article['category']) }} address symptoms rather than the underlying biomechanical dysfunction. This is why so many patients find temporary relief followed by recurrence — the structural issue causing the problem has never been properly addressed.</p>
        <p>Our physiotherapy advisory board, with over 60 years of combined clinical experience, has identified that the vast majority of chronic spinal pain cases share three root biomechanical factors: intervertebral compression, muscular imbalance, and habitual postural dysfunction.</p>
        <h2>Evidence-Based Treatment Principles</h2>
        <p>The most effective evidence-based treatment protocols consistently combine: targeted decompression to reduce nerve pressure, progressive mobilisation to restore range of motion, specific strengthening of spinal stabilisers, and systematic habit reformation to address causative postural patterns.</p>
        <div class="not-prose my-8 p-6 bg-navy-50 rounded-2xl border-l-4 border-navy-500">
          <p class="font-semibold text-navy-900 mb-2">Clinical Evidence</p>
          <p class="text-slate-600">A 2024 randomised controlled trial published in the Journal of Orthopaedic & Sports Physical Therapy found that lumbar decompression combined with targeted mobility protocols reduced chronic back pain scores by 73% over 6 weeks — significantly outperforming pharmacological intervention alone.</p>
        </div>
        <h2>Implementing a Recovery Protocol</h2>
        <p>Consistent, graduated implementation is the key to lasting results. Begin with 2–3 hours of lumbar support per day, add targeted mobility work daily, and progressively increase activity tolerance over 4–6 weeks.</p>
        <p>Most patients following this systematic protocol report meaningful improvement within 2 weeks and substantial pain reduction by week 4.</p>
      </article>
      {{-- Sidebar --}}
      <aside class="space-y-6">
        @include('partials.shopify-product-sidebar', [
          'product' => $featuredShopifyProduct ?? null,
          'heading' => 'Clinically Recommended',
          'description' => 'Shop our recommended wellness product from the live catalog.',
        ])
        @if(!empty($related))
        <div class="card p-6">
          <h3 class="font-semibold text-navy-900 mb-4">Related Articles</h3>
          <div class="space-y-4">
            @foreach($related as $rel)
            <a href="{{ route('blog.show', ['locale' => app()->getLocale(), 'slug' => $rel['slug']]) }}" class="flex gap-3 group">
              <img src="{{ asset('images/' . $rel['image']) }}" alt="{{ $rel['title'] }}" class="w-16 h-16 object-cover rounded-lg flex-shrink-0">
              <div>
                <p class="text-sm font-medium text-slate-800 group-hover:text-navy-700 transition-colors leading-tight">{{ $rel['title'] }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ $rel['readtime'] }}</p>
              </div>
            </a>
            @endforeach
          </div>
        </div>
        @endif
      </aside>
    </div>
  </div>
</section>
@endsection
