@extends('layouts.app')
@section('title', 'Contact Dainely — Support, Questions & Feedback')
@section('meta_description', 'Get in touch with the Dainely wellness team. ' . config('company.hours') . ' · ' . config('company.email'))

@section('content')

{{-- HERO --}}
<section class="bg-gradient-to-br from-navy-900 to-navy-800 text-white py-16" aria-label="Contact hero">
  <div class="container-narrow text-center">
    <p class="eyebrow text-gold-400 mb-4">Get in Touch</p>
    <h1 class="font-display font-bold text-4xl text-white mb-4">We're Here to Help</h1>
    <p class="text-navy-200 text-lg">If you aren't 100% happy with your order, we'll do everything in our power to make it right. Get in touch and we'll help you out!</p>
  </div>
</section>

{{-- CONTACT CONTENT --}}
<section class="section bg-white" aria-label="Get in touch">
  <div class="container-site">

    <div class="grid lg:grid-cols-[1fr_400px] gap-12">

      {{-- FORM --}}
      <div>
        <h3 class="heading-card mb-2">Send Us a Message</h3>
        <p class="text-body mb-6">Fill out the form below and we'll respond as soon as possible.</p>

        @if(session('success'))
        <div class="alert-success mb-6">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          {{ session('success') }}
        </div>
        @endif

        <form action="#" method="POST" class="space-y-5" id="contact-form">
          @csrf
          <div class="grid sm:grid-cols-2 gap-5">
            <div>
              <label class="form-label" for="contact-name">Name *</label>
              <input type="text" id="contact-name" name="name" class="form-input" placeholder="Your full name" required value="{{ old('name') }}">
              @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
              <label class="form-label" for="contact-email">Email *</label>
              <input type="email" id="contact-email" name="email" class="form-input" placeholder="you@example.com" required value="{{ old('email') }}">
              @error('email')<p class="form-error">{{ $message }}</p>@enderror
            </div>
          </div>

          <div>
            <label class="form-label" for="contact-message">Message *</label>
            <textarea id="contact-message" name="message" rows="6" class="form-input resize-none" placeholder="Tell us how we can help..." required>{{ old('message') }}</textarea>
            @error('message')<p class="form-error">{{ $message }}</p>@enderror
          </div>

          {{-- hCaptcha widget --}}
          <div class="h-captcha" data-sitekey="YOUR_HCAPTCHA_SITE_KEY"></div>
          @error('h-captcha-response')<p class="form-error">{{ $message }}</p>@enderror

          <button type="submit" class="btn-primary-lg">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            Send Message
          </button>

          <p class="text-xs text-slate-500 leading-relaxed pt-2">
            This site is protected by hCaptcha and the hCaptcha
            <a href="https://hcaptcha.com/privacy" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline hover:text-blue-700">Privacy Policy</a> and
            <a href="https://hcaptcha.com/terms" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline hover:text-blue-700">Terms of Service</a> apply.
          </p>
        </form>
      </div>

      {{-- CONTACT INFO SIDEBAR --}}
      <aside class="space-y-6">
        <div class="card p-6">
          <h3 class="heading-card mb-5">Other Ways to Reach Us</h3>
          <div class="space-y-5">
            @foreach([
              [
                'title'   => 'Email Support',
                'contact' => config('company.email'),
                'link'    => 'mailto:' . config('company.email'),
                'hours'   => config('company.hours'),
                'color'   => 'navy',
                'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>',
              ],
              [
                'title'   => 'Phone Support',
                'contact' => config('company.phone_display'),
                'link'    => 'tel:' . config('company.phone_tel'),
                'hours'   => config('company.hours'),
                'color'   => 'gold',
                'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
              ],
              [
                'title'   => 'Our Office',
                'contact' => config('company.legal_name'),
                'link'    => null,
                'hours'   => config('company.address'),
                'color'   => 'sage',
                'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>',
              ],
            ] as $info)
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 bg-{{ $info['color'] }}-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <div class="w-5 h-5 text-{{ $info['color'] }}-600">{!! $info['icon'] !!}</div>
              </div>
              <div class="min-w-0 flex-1">
                <p class="font-semibold text-slate-800 text-sm">{{ $info['title'] }}</p>
                @if($info['link'])
                  <p class="text-navy-600 text-sm break-words">
                    <a href="{{ $info['link'] }}" class="hover:text-blue-600">{{ $info['contact'] }}</a>
                  </p>
                @else
                  <p class="text-navy-600 text-sm font-medium">{{ $info['contact'] }}</p>
                @endif
                <p class="text-slate-400 text-xs mt-0.5 leading-relaxed">{{ $info['hours'] }}</p>
              </div>
            </div>
            @endforeach
          </div>
        </div>

        {{-- 100% Happy Guarantee card --}}
        <div class="bg-sage-50 border-2 border-sage-200 rounded-2xl p-5 flex items-start gap-4">
          <svg class="w-8 h-8 text-sage-600 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
          </svg>
          <div>
            <p class="font-semibold text-sage-800 text-sm">100% Happy Guarantee</p>
            <p class="text-sage-700 text-xs mt-1 leading-relaxed">If you aren't completely satisfied with your order, we'll do everything in our power to make it right.</p>
          </div>
        </div>
      </aside>
    </div>
  </div>
</section>
@endsection
