{{-- Dainely Site Footer --}}
<footer class="bg-navy-900 text-white" aria-label="Site footer">

  {{-- Main footer columns --}}
  <div class="container-site py-16">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 lg:gap-12">

      {{-- Brand column --}}
      <div class="lg:col-span-2">
        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="flex items-center gap-3 mb-5">
          <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
          </div>
          <span class="font-display font-bold text-xl tracking-tight">Dainely</span>
        </a>
        <p class="text-navy-300 text-sm leading-relaxed max-w-xs">{{ __('footer.tagline') }}</p>

        {{-- Trust badges --}}
        <div class="flex flex-wrap gap-2 mt-6">
          <span class="flex items-center gap-1.5 bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-xs text-navy-200">
            <svg class="w-3.5 h-3.5 text-sage-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ __('footer.badge_clinically') }}
          </span>
          <span class="flex items-center gap-1.5 bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-xs text-navy-200">
            <svg class="w-3.5 h-3.5 text-gold-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            {{ __('footer.badge_trusted') }}
          </span>
          <span class="flex items-center gap-1.5 bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-xs text-navy-200">
            <svg class="w-3.5 h-3.5 text-navy-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
            {{ __('footer.badge_secure') }}
          </span>
        </div>
      </div>

      {{-- Products --}}
      <div>
        <h3 class="text-sm font-bold uppercase tracking-widest text-navy-400 mb-4">{{ __('footer.products') }}</h3>
        <ul class="space-y-2.5">
          <li><a href="{{ route('products.show', ['locale' => app()->getLocale(), 'slug' => 'dainely-belt']) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('nav.dainely_belt') }}</a></li>
          <li><a href="{{ route('products.show', ['locale' => app()->getLocale(), 'slug' => 'daily-relief-system']) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('nav.daily_relief') }}</a></li>
          <li><a href="{{ route('products.index', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('footer.all_products') }}</a></li>
        </ul>
      </div>

      {{-- Learn --}}
      <div>
        <h3 class="text-sm font-bold uppercase tracking-widest text-navy-400 mb-4">{{ __('footer.learn') }}</h3>
        <ul class="space-y-2.5">
          <li><a href="{{ route('education.back-pain', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('nav.back_pain') }}</a></li>
          <li><a href="{{ route('education.sciatica', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('nav.sciatica') }}</a></li>
          <li><a href="{{ route('education.posture', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('nav.posture') }}</a></li>
          <li><a href="{{ route('education.mobility', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('nav.mobility') }}</a></li>
          <li><a href="{{ route('blog.index', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('nav.blog') }}</a></li>
        </ul>
      </div>

      {{-- Company --}}
      <div>
        <h3 class="text-sm font-bold uppercase tracking-widest text-navy-400 mb-4">{{ __('footer.company') }}</h3>
        <ul class="space-y-2.5">
          <li><a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('nav.about') }}</a></li>
          <li><a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('nav.contact') }}</a></li>
          <li><a href="{{ route('faq', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('footer.faq') }}</a></li>
          <li><a href="{{ route('shipping', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('footer.shipping_policy') }}</a></li>
          <li><a href="{{ route('refund', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('footer.refund_policy') }}</a></li>
          <li><a href="{{ route('privacy', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('footer.privacy_policy') }}</a></li>
          <li><a href="{{ route('terms', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">Terms &amp; Conditions</a></li>
        </ul>
      </div>
    </div>
  </div>

  <div class="border-t border-navy-800">
    <div class="container-site py-6 flex flex-col sm:flex-row items-center justify-between gap-4">
      <p class="text-navy-400 text-xs">{{ __('footer.copyright', ['year' => date('Y')]) }}</p>
      <div class="flex items-center gap-4">
        {{-- Legal links --}}
        <div class="flex items-center gap-3 text-navy-400 text-xs">
          <a href="{{ route('privacy', ['locale' => app()->getLocale()]) }}" class="hover:text-white transition-colors">{{ __('footer.privacy_policy') }}</a>
          <span class="text-navy-700">·</span>
          <a href="{{ route('terms', ['locale' => app()->getLocale()]) }}" class="hover:text-white transition-colors">Terms</a>
        </div>
        <span class="text-navy-700">|</span>
        {{-- Payment icons --}}
        <div class="flex items-center gap-2">
          <span class="bg-white/10 rounded px-2 py-0.5 text-[10px] font-bold text-white">VISA</span>
          <span class="bg-white/10 rounded px-2 py-0.5 text-[10px] font-bold text-white">MC</span>
          <span class="bg-white/10 rounded px-2 py-0.5 text-[10px] font-bold text-white">AMEX</span>
          <span class="bg-white/10 rounded px-2 py-0.5 text-[10px] font-bold text-white">PAYPAL</span>
        </div>
        <span class="text-navy-700">|</span>
        <div class="flex items-center gap-1.5 text-navy-400 text-xs">
          <svg class="w-3.5 h-3.5 text-sage-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
          {{ __('footer.ssl_secured') }}
        </div>
      </div>
    </div>
  </div>
</footer>
