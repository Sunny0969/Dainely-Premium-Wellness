{{-- Dainely Site Footer --}}
<footer class="bg-navy-900 text-white" aria-label="Site footer">

  {{-- Main footer columns --}}
  <div class="container-site py-16">
    
    <div class="flex flex-row flex-wrap md:flex-nowrap justify-between gap-6 lg:gap-12 items-start w-full">

      {{-- Brand column --}}
      <div class="flex-1 w-full min-w-[200px]">
        <div class="mb-4">
          <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="flex items-center" aria-label="Dainely Home">
            <img src="{{ asset('images/Dainelycut.png') }}" alt="Dainely" class="h-10 w-auto brightness-0 invert">
          </a>
        </div>
        <p class="text-navy-300 text-sm leading-relaxed mb-6">{{ config('company.address') }}</p>
      </div>

      {{-- Learn --}}
      <div class="flex-1 w-full min-w-[150px]">
        <h3 class="text-sm font-bold uppercase tracking-widest text-navy-400 mb-4">{{ __('footer.learn') }}</h3>
        <ul class="space-y-2.5">
          <li><a href="{{ route('education.back-pain', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('nav.back_pain') }}</a></li>
          <li><a href="{{ route('education.sciatica', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('nav.sciatica') }}</a></li>
          <li><a href="{{ route('education.posture', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('nav.posture') }}</a></li>
          <li><a href="{{ route('education.mobility', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('nav.mobility') }}</a></li>
          <li><a href="{{ route('blog.index', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('nav.blog') }}</a></li>
          <li><a href="{{ route('products.index', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('footer.all_products') }}</a></li>
          @php $footerCartCount = (int) ($cartItemCount ?? \App\Support\CheckoutCart::itemCount()); @endphp
          <li>
            <a href="{{ route('checkout.index', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors inline-flex items-center gap-2">
              {{ __('nav.cart') }}
              @if($footerCartCount > 0)
                <span class="min-w-[1.125rem] h-[1.125rem] px-1 bg-white/20 text-white text-[10px] font-bold rounded-full inline-flex items-center justify-center">{{ $footerCartCount > 99 ? '99+' : $footerCartCount }}</span>
              @endif
            </a>
          </li>
        </ul>
      </div>

      {{-- Company --}}
      <div class="flex-1 w-full min-w-[150px]">
        <h3 class="text-sm font-bold uppercase tracking-widest text-navy-400 mb-4">{{ __('footer.company') }}</h3>
        <ul class="space-y-2.5">
          <li><a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('nav.about') }}</a></li>
          <li><a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('nav.contact') }}</a></li>
          <li><a href="{{ route('faq', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('footer.faq') }}</a></li>
          <li><a href="{{ route('shipping', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('footer.shipping_policy') }}</a></li>
          <li><a href="{{ route('refund', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('footer.refund_policy') }}</a></li>
          <li><a href="{{ route('privacy', ['locale' => app()->getLocale()]) }}" class="text-navy-300 hover:text-white text-sm transition-colors">{{ __('footer.privacy_policy') }}</a></li>
          <li><a href="/admin/dashboard" class="text-navy-300 hover:text-white text-sm transition-colors">Admin Portal</a></li>
        </ul>
      </div>

      {{-- Need help column --}}
      <div class="flex-1 w-full min-w-[200px]">
        <h3 class="text-sm font-bold uppercase tracking-widest text-navy-400 mb-4">{{ __('footer.need_help_title') }}</h3>
        <p class="text-navy-300 text-sm leading-relaxed mb-4">{{ __('footer.need_help_copy') }}</p>

        <div class="space-y-4 text-sm">
          <div>
            <p class="text-navy-400 font-bold text-[11px] uppercase tracking-wider mb-1">{{ __('footer.hours_label') }}</p>
            <p class="text-navy-300">{{ config('company.hours') }}</p>
          </div>

          <div>
            <p class="text-navy-400 font-bold text-[11px] uppercase tracking-wider mb-1">{{ __('footer.email_label') }}</p>
            <a href="mailto:{{ config('company.email') }}" class="text-navy-300 hover:text-white transition-colors">{{ config('company.email') }}</a>
          </div>

          <div>
            <p class="text-navy-400 font-bold text-[11px] uppercase tracking-wider mb-1">{{ __('footer.phone_label') }}</p>
            <a href="tel:{{ config('company.phone_tel') }}" class="text-navy-300 hover:text-white transition-colors">{{ config('company.phone_display') }}</a>
          </div>
        </div>
      </div>

      {{-- Sign up and save column --}}
      <div class="flex-1 w-full min-w-[250px] lg:flex-[1.2]">
        <h3 class="text-sm font-bold uppercase tracking-widest text-navy-400 mb-4">{{ __('footer.newsletter_title') }}</h3>
        <p class="text-navy-300 text-sm leading-relaxed mb-4">{{ __('footer.newsletter_copy') }}</p>
        <form action="#" method="POST" class="relative flex items-center w-full mb-6">
          @csrf
          <input 
            type="email" 
            name="email" 
            placeholder="{{ __('footer.newsletter_placeholder') }}" 
            required 
            class="w-full bg-white/5 border border-white/10 rounded-lg pl-4 pr-12 py-2.5 text-sm text-white placeholder-navy-400 focus:outline-none focus:border-navy-500 focus:ring-1 focus:ring-navy-500 transition-colors"
          >
          <button 
            type="submit" 
            class="absolute right-1 top-1 bottom-1 px-3 bg-navy-600 hover:bg-navy-700 text-white rounded-md transition-colors flex items-center justify-center"
            aria-label="{{ __('footer.subscribe') }}"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
          </button>
        </form>

        {{-- Social links --}}
        <div class="flex flex-wrap gap-4 text-navy-300">
          <a href="#" class="hover:text-white transition-colors" aria-label="Instagram">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
          </a>
          <a href="#" class="hover:text-white transition-colors" aria-label="Facebook">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c4.56-.93 8-4.96 8-9.75z"/></svg>
          </a>
          <a href="#" class="hover:text-white transition-colors" aria-label="YouTube">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.163a3.003 3.003 0 0 0-2.11-2.107C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.388.511a3.002 3.002 0 0 0-2.11 2.107C0 8.021 0 12 0 12s0 3.979.502 5.837a3.001 3.001 0 0 0 2.11 2.107C4.495 20.455 12 20.455 12 20.455s7.505 0 9.388-.511a3.002 3.002 0 0 0 2.11-2.107C24 15.979 24 12 24 12s0-3.979-.502-5.837zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
          </a>
          <a href="#" class="hover:text-white transition-colors" aria-label="Pinterest">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.41 7.61 11.162-.102-.947-.195-2.404.04-3.443.214-.937 1.38-5.845 1.38-5.845s-.351-.703-.351-1.744c0-1.633.946-2.853 2.128-2.853 1.002 0 1.486.753 1.486 1.657 0 1.008-.644 2.514-.975 3.91-.277 1.17.589 2.126 1.74 2.126 2.086 0 3.693-2.202 3.693-5.382 0-2.812-2.02-4.78-4.9-4.78-3.342 0-5.3 2.505-5.3 5.093 0 1.01.389 2.093.876 2.686a.276.276 0 0 1 .064.265c-.097.399-.309 1.258-.352 1.433-.057.23-.19.278-.438.163-1.63-.758-2.65-3.136-2.65-5.044 0-4.108 2.986-7.881 8.602-7.881 4.515 0 8.022 3.217 8.022 7.518 0 4.49-2.827 8.1-6.754 8.1-1.32 0-2.56-.686-2.984-1.496l-.815 3.107c-.294 1.121-1.092 2.528-1.626 3.398 1.125.347 2.316.536 3.552.536 6.62 0 11.988-5.37 11.988-11.986C24.004 5.367 18.636 0 12.017 0z"/></svg>
          </a>
          <a href="#" class="hover:text-white transition-colors" aria-label="TikTok">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.02 1.59 4.23.85.95 2 1.63 3.25 1.95v3.65c-1.34-.07-2.64-.54-3.74-1.33-.23-.17-.45-.35-.66-.54v7.71c.04 2.27-1.12 4.45-3.08 5.61-2.02 1.19-4.62 1.34-6.78.39-2.22-.98-3.73-3.21-3.76-5.64-.08-2.84 1.99-5.36 4.79-5.89 1.19-.22 2.42-.02 3.5.55v3.69c-.74-.4-1.59-.55-2.42-.43-1.15.17-2.12.98-2.47 2.09-.45 1.39.23 2.92 1.57 3.48 1.2.5 2.65.17 3.49-.78.58-.66.86-1.52.84-2.39-.01-2.99 0-5.97-.01-8.96z"/></svg>
          </a>
          <a href="#" class="hover:text-white transition-colors" aria-label="LinkedIn">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.779-1.75-1.75s.784-1.75 1.75-1.75 1.75.779 1.75 1.75-.784 1.75-1.75 1.75zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
          </a>
        </div>
      </div>

    </div>
  </div>

  <div class="border-t border-navy-800">
    <div class="container-site py-6 flex flex-col md:flex-row items-center justify-between gap-4 text-navy-400 text-xs">
      {{-- Left Side: Copyright & Legal Links --}}
      <div class="flex flex-col sm:flex-row items-center gap-4 text-center sm:text-left">
        <p>{{ __('footer.copyright', ['year' => date('Y')]) }}</p>
        <span class="hidden sm:inline text-navy-700">·</span>
        <div class="flex items-center gap-3">
          <a href="{{ route('privacy', ['locale' => app()->getLocale()]) }}" class="hover:text-white transition-colors">{{ __('footer.privacy_policy') }}</a>
          <span class="text-navy-700">·</span>
          <a href="{{ route('terms', ['locale' => app()->getLocale()]) }}" class="hover:text-white transition-colors">{{ __('footer.terms') }}</a>
        </div>
      </div>

      {{-- Right Side: Payment Methods & SSL Badge --}}
      <div class="flex flex-col sm:flex-row items-center gap-4">
        <div class="flex items-center gap-2">
          <span class="bg-white/10 rounded px-2 py-0.5 text-[10px] font-bold text-white">VISA</span>
          <span class="bg-white/10 rounded px-2 py-0.5 text-[10px] font-bold text-white">MC</span>
          <span class="bg-white/10 rounded px-2 py-0.5 text-[10px] font-bold text-white">AMEX</span>
          <span class="bg-white/10 rounded px-2 py-0.5 text-[10px] font-bold text-white">PAYPAL</span>
        </div>
        <span class="hidden sm:inline text-navy-700">|</span>
        <div class="flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-sage-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
          {{ __('footer.ssl_secured') }}
        </div>
      </div>
    </div>
  </div>
</footer>
