@extends('layouts.app')
@section('title', 'Contact Dainely — Support, Questions & Feedback')
@section('meta_description', 'Get in touch with the Dainely wellness team. We typically respond within 4 hours during business hours. Monday–Friday 9am–6pm EST.')

@section('content')

{{-- HERO --}}
<section class="bg-gradient-to-br from-navy-900 to-navy-800 text-white py-16" aria-label="Contact hero">
  <div class="container-narrow text-center">
    <p class="eyebrow text-gold-400 mb-4">We're Here to Help</p>
    <h1 class="font-display font-bold text-4xl text-white mb-4">Contact Dainely</h1>
    <p class="text-navy-200 text-lg">Our wellness specialists are available Monday–Friday, 9am–6pm EST. We typically respond within 4 hours.</p>
  </div>
</section>

{{-- CONTACT CONTENT --}}
<section class="section bg-white" aria-label="Contact form and info">
  <div class="container-site">
    <div class="grid lg:grid-cols-[1fr_400px] gap-12">

      {{-- FORM --}}
      <div>
        <h2 class="heading-section mb-2">Send Us a Message</h2>
        <p class="text-body mb-8">Fill out the form below and we will get back to you as soon as possible.</p>

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
              <label class="form-label" for="contact-name">Full Name *</label>
              <input type="text" id="contact-name" name="name" class="form-input" placeholder="Sarah Mitchell" required value="{{ old('name') }}">
              @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
              <label class="form-label" for="contact-email">Email Address *</label>
              <input type="email" id="contact-email" name="email" class="form-input" placeholder="sarah@example.com" required value="{{ old('email') }}">
              @error('email')<p class="form-error">{{ $message }}</p>@enderror
            </div>
          </div>
          <div>
            <label class="form-label" for="contact-subject">Subject *</label>
            <select id="contact-subject" name="subject" class="form-input" required>
              <option value="">Select a topic...</option>
              <option value="order">Order Status / Tracking</option>
              <option value="return">Returns & Refunds</option>
              <option value="product">Product Questions</option>
              <option value="sizing">Sizing & Fit Help</option>
              <option value="medical">Medical / Clinical Questions</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div>
            <label class="form-label" for="contact-order">Order Number (if applicable)</label>
            <input type="text" id="contact-order" name="order_number" class="form-input" placeholder="DAI-2025-XXXXX" value="{{ old('order_number') }}">
          </div>
          <div>
            <label class="form-label" for="contact-message">Message *</label>
            <textarea id="contact-message" name="message" rows="6" class="form-input resize-none" placeholder="Tell us how we can help..." required>{{ old('message') }}</textarea>
            @error('message')<p class="form-error">{{ $message }}</p>@enderror
          </div>
          <button type="submit" class="btn-primary-lg">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            Send Message
          </button>
        </form>
      </div>

      {{-- CONTACT INFO --}}
      <aside class="space-y-6">
        <div class="card p-6">
          <h3 class="heading-card mb-5">Other Ways to Reach Us</h3>
          <div class="space-y-5">
            @foreach([
              ['Email Support', 'support@dainely.com', 'Response within 4 hours', 'M20 3H4a1 1 0 00-1 1v14a1 1 0 001 1h16a1 1 0 001-1V4a1 1 0 00-1-1zm-5 6H9M7 7h10', 'navy'],
              ['Live Chat', 'Available on site', 'Mon–Fri, 9am–6pm EST', 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z', 'sage'],
              ['Shipping Questions', 'Track your order', 'Via email with order number', 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'gold'],
            ] as [$title, $contact, $hours, $icon, $color])
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 bg-{{ $color }}-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-{{ $color }}-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
              </div>
              <div>
                <p class="font-semibold text-slate-800 text-sm">{{ $title }}</p>
                <p class="text-navy-600 text-sm">{{ $contact }}</p>
                <p class="text-slate-400 text-xs">{{ $hours }}</p>
              </div>
            </div>
            @endforeach
          </div>
        </div>

        {{-- Doctor image + message --}}
        <div class="card overflow-hidden">
          <img src="{{ asset('images/trust-doctor.png') }}" alt="Dainely medical support" class="w-full h-48 object-cover object-top" loading="lazy">
          <div class="p-5">
            <p class="font-semibold text-navy-900 text-sm mb-1">Medical Questions?</p>
            <p class="text-slate-500 text-xs leading-relaxed">Our clinical team can answer product-related medical questions. For personal medical advice, please consult your physician.</p>
          </div>
        </div>

        {{-- Response promise --}}
        <div class="bg-sage-50 border-2 border-sage-200 rounded-2xl p-5 flex items-start gap-4">
          <svg class="w-8 h-8 text-sage-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <div>
            <p class="font-semibold text-sage-800 text-sm">Our Response Promise</p>
            <p class="text-sage-600 text-xs mt-1">We commit to responding to every message within 4 business hours, Monday through Friday. Weekend messages are answered first thing Monday morning.</p>
          </div>
        </div>
      </aside>
    </div>
  </div>
</section>

@endsection
