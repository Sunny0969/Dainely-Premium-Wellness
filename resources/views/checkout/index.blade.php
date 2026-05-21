@extends('layouts.app')
@section('title', 'Secure Checkout | Dainely')
@section('meta_description', 'Complete your Dainely order securely. We accept all major credit cards via Square Payments. Free shipping on orders over $75.')

@section('content')
<div class="min-h-screen bg-slate-50">

  {{-- Checkout header strip --}}
  <div class="bg-white border-b border-slate-100 py-4">
    <div class="container-site flex items-center justify-between">
      <div class="flex items-center gap-3">
        <img src="{{ asset('images/logo-icon.png') }}" alt="Dainely" class="h-8 w-8 object-contain">
        <span class="font-display font-bold text-navy-900 text-xl">Dainely</span>
        <span class="text-slate-300 hidden sm:inline">|</span>
        <span class="text-slate-500 text-sm hidden sm:inline">Secure Checkout</span>
      </div>
      <div class="flex items-center gap-2 text-slate-500 text-sm">
        <svg class="w-4 h-4 text-sage-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        <span class="hidden sm:inline">256-bit SSL Secured</span>
      </div>
    </div>
  </div>

  <div class="container-site py-10">
    <div class="grid lg:grid-cols-[1fr_420px] gap-10 items-start" x-data="checkoutForm()">

      {{-- LEFT: Form --}}
      <div class="space-y-6">

        {{-- Step indicator --}}
        <div class="flex items-center gap-0 mb-2">
          <div class="flex items-center gap-2">
            <div class="step-indicator" :class="step >= 1 ? 'active' : ''">1</div>
            <span class="text-sm font-medium" :class="step >= 1 ? 'text-navy-700' : 'text-slate-400'">Contact</span>
          </div>
          <div class="flex-1 h-0.5 mx-3" :class="step >= 2 ? 'bg-navy-600' : 'bg-slate-200'"></div>
          <div class="flex items-center gap-2">
            <div class="step-indicator" :class="step >= 2 ? 'active' : (step === 1 ? '' : '')">2</div>
            <span class="text-sm font-medium" :class="step >= 2 ? 'text-navy-700' : 'text-slate-400'">Shipping</span>
          </div>
          <div class="flex-1 h-0.5 mx-3" :class="step >= 3 ? 'bg-navy-600' : 'bg-slate-200'"></div>
          <div class="flex items-center gap-2">
            <div class="step-indicator" :class="step >= 3 ? 'active' : ''">3</div>
            <span class="text-sm font-medium" :class="step >= 3 ? 'text-navy-700' : 'text-slate-400'">Payment</span>
          </div>
        </div>

        {{-- STEP 1: Contact Info --}}
        <div class="card p-6" x-show="step === 1" x-transition>
          <h2 class="font-display font-bold text-navy-900 text-xl mb-6">Contact Information</h2>
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="form-label" for="first_name">First Name *</label>
              <input type="text" id="first_name" name="first_name" x-model="form.first_name" class="form-input" placeholder="Sarah" required autocomplete="given-name">
            </div>
            <div>
              <label class="form-label" for="last_name">Last Name *</label>
              <input type="text" id="last_name" name="last_name" x-model="form.last_name" class="form-input" placeholder="Mitchell" required autocomplete="family-name">
            </div>
            <div class="sm:col-span-2">
              <label class="form-label" for="email">Email Address *</label>
              <input type="email" id="email" name="email" x-model="form.email" class="form-input" placeholder="sarah@example.com" required autocomplete="email">
              <p class="text-slate-400 text-xs mt-1">Your order confirmation will be sent here.</p>
            </div>
            <div class="sm:col-span-2">
              <label class="form-label" for="phone">Phone Number (optional)</label>
              <input type="tel" id="phone" name="phone" x-model="form.phone" class="form-input" placeholder="+1 555 000 0000" autocomplete="tel">
            </div>
          </div>

          {{-- EU GDPR consent --}}
          <div class="mt-5 p-4 bg-slate-50 rounded-xl" x-show="isEuCountry">
            <label class="flex items-start gap-3 cursor-pointer">
              <input type="checkbox" x-model="form.gdpr_consent" id="gdpr-consent" class="mt-0.5 w-4 h-4 text-navy-600 rounded border-slate-300 focus:ring-navy-500">
              <span class="text-slate-600 text-sm">I consent to Dainely storing and processing my data for order fulfilment and to receive wellness updates. <a href="#" class="text-navy-600 underline">Privacy Policy</a>.</span>
            </label>
          </div>

          <div class="mt-6">
            <button type="button" @click="nextStep()" class="btn-primary-lg w-full justify-center">Continue to Shipping</button>
          </div>
        </div>

        {{-- STEP 2: Shipping --}}
        <div class="card p-6" x-show="step === 2" x-transition>
          <div class="flex items-center justify-between mb-6">
            <h2 class="font-display font-bold text-navy-900 text-xl">Shipping Address</h2>
            <button @click="step = 1" class="text-navy-600 text-sm hover:underline">← Edit Contact</button>
          </div>
          <div class="space-y-4">
            <div>
              <label class="form-label" for="address1">Street Address *</label>
              <input type="text" id="address1" x-model="form.address1" class="form-input" placeholder="123 Main Street" required autocomplete="address-line1">
            </div>
            <div>
              <label class="form-label" for="address2">Apartment, Suite, etc. (optional)</label>
              <input type="text" id="address2" x-model="form.address2" class="form-input" placeholder="Apt 4B" autocomplete="address-line2">
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="form-label" for="city">City *</label>
                <input type="text" id="city" x-model="form.city" class="form-input" placeholder="New York" required autocomplete="address-level2">
              </div>
              <div>
                <label class="form-label" for="state">State / Province</label>
                <input type="text" id="state" x-model="form.state" class="form-input" placeholder="NY" autocomplete="address-level1">
              </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="form-label" for="zip">Postal Code *</label>
                <input type="text" id="zip" x-model="form.zip" class="form-input" placeholder="10001" required autocomplete="postal-code">
              </div>
              <div>
                <label class="form-label" for="country">Country *</label>
                <select id="country" x-model="form.country" @change="checkEuCountry()" class="form-input" required autocomplete="country">
                  <option value="">Select country...</option>
                  <option value="US">United States</option>
                  <option value="GB">United Kingdom</option>
                  <option value="CA">Canada</option>
                  <option value="AU">Australia</option>
                  <option value="FR">France</option>
                  <option value="DE">Germany</option>
                  <option value="NL">Netherlands</option>
                  <option value="BE">Belgium</option>
                  <option value="ES">Spain</option>
                  <option value="IT">Italy</option>
                  <option value="SE">Sweden</option>
                  <option value="NO">Norway</option>
                  <option value="DK">Denmark</option>
                  <option value="CH">Switzerland</option>
                  <option value="AT">Austria</option>
                  <option value="PL">Poland</option>
                  <option value="PT">Portugal</option>
                  <option value="IE">Ireland</option>
                  <option value="NZ">New Zealand</option>
                  <option value="ZA">South Africa</option>
                </select>
              </div>
            </div>
          </div>

          {{-- Shipping method --}}
          <div class="mt-6">
            <p class="form-label mb-3">Shipping Method</p>
            <div class="space-y-2">
              <label class="flex items-center justify-between p-4 border-2 rounded-xl cursor-pointer transition-colors" :class="form.shipping_method === 'standard' ? 'border-navy-600 bg-navy-50' : 'border-slate-200 hover:border-navy-300'">
                <div class="flex items-center gap-3">
                  <input type="radio" x-model="form.shipping_method" value="standard" class="text-navy-600">
                  <div>
                    <p class="font-semibold text-slate-800 text-sm">Standard Shipping</p>
                    <p class="text-slate-400 text-xs">5–8 business days</p>
                  </div>
                </div>
                <span class="font-semibold text-slate-700" x-text="subtotal >= 75 ? 'FREE' : '$9.99'"></span>
              </label>
              <label class="flex items-center justify-between p-4 border-2 rounded-xl cursor-pointer transition-colors" :class="form.shipping_method === 'express' ? 'border-navy-600 bg-navy-50' : 'border-slate-200 hover:border-navy-300'">
                <div class="flex items-center gap-3">
                  <input type="radio" x-model="form.shipping_method" value="express" class="text-navy-600">
                  <div>
                    <p class="font-semibold text-slate-800 text-sm">Express Shipping</p>
                    <p class="text-slate-400 text-xs">2–3 business days</p>
                  </div>
                </div>
                <span class="font-semibold text-slate-700">$24.99</span>
              </label>
            </div>
          </div>

          <div class="mt-6">
            <button type="button" @click="nextStep()" class="btn-primary-lg w-full justify-center">Continue to Payment</button>
          </div>
        </div>

        {{-- STEP 3: Payment --}}
        <div class="card p-6" x-show="step === 3" x-transition>
          <div class="flex items-center justify-between mb-6">
            <h2 class="font-display font-bold text-navy-900 text-xl">Payment</h2>
            <button @click="step = 2" class="text-navy-600 text-sm hover:underline">← Edit Shipping</button>
          </div>

          {{-- Accepted cards --}}
          <div class="flex items-center gap-2 mb-5">
            <span class="text-slate-500 text-xs">We accept:</span>
            @foreach(['VISA', 'MC', 'AMEX', 'PAYPAL'] as $card)
            <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-2 py-1 rounded">{{ $card }}</span>
            @endforeach
            <div class="ml-auto flex items-center gap-1 text-sage-600">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
              <span class="text-xs font-semibold">256-bit SSL</span>
            </div>
          </div>

          {{-- Square Payment Form placeholder --}}
          <div class="border-2 border-slate-200 rounded-xl p-5 mb-5 bg-slate-50">
            <div class="flex items-center gap-2 mb-4">
              <img src="{{ asset('images/checkout-secure.png') }}" alt="Secure payment" class="h-10 w-10 object-contain">
              <div>
                <p class="text-slate-800 font-semibold text-sm">Secure Payment via Square</p>
                <p class="text-slate-400 text-xs">Your card details are never stored on our servers</p>
              </div>
            </div>
            {{-- Square Web Payments SDK mounts here --}}
            <div id="card-container" class="min-h-[80px] bg-white border border-slate-200 rounded-xl p-4">
              <p class="text-slate-400 text-sm text-center py-4">Loading secure payment form...</p>
            </div>
          </div>

          {{-- Error message --}}
          <div x-show="paymentError" class="alert-error mb-4" x-text="paymentError"></div>

          {{-- Place order --}}
          <button
            type="button"
            @click="submitOrder()"
            :disabled="loading"
            id="place-order-btn"
            class="btn-gold-lg w-full justify-center disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <span x-show="!loading" class="flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
              Place Order — <span x-text="'$' + total.toFixed(2)"></span>
            </span>
            <span x-show="loading" class="flex items-center gap-2">
              <svg class="spinner w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
              Processing...
            </span>
          </button>
          <p class="text-slate-400 text-xs text-center mt-3">By placing your order you agree to our <a href="#" class="underline hover:text-navy-700">Terms of Service</a> and <a href="#" class="underline hover:text-navy-700">Privacy Policy</a>.</p>
        </div>
      </div>

      {{-- RIGHT: Order Summary --}}
      <div class="space-y-4">
        <div class="card p-6">
          <h3 class="font-display font-bold text-navy-900 text-lg mb-5">Order Summary</h3>

          {{-- Cart items --}}
          <div class="space-y-4 mb-5">
            <div class="flex items-center gap-4">
              <div class="relative flex-shrink-0">
                <img src="{{ asset('images/dainely-belt-product.png') }}" alt="Dainely Belt" class="w-16 h-16 rounded-xl object-cover bg-slate-100">
                <span class="absolute -top-2 -right-2 w-5 h-5 bg-navy-600 text-white text-xs rounded-full flex items-center justify-center font-bold">1</span>
              </div>
              <div class="flex-1">
                <p class="font-semibold text-slate-800 text-sm">Dainely Belt</p>
                <p class="text-slate-400 text-xs">Size: L/XL</p>
              </div>
              <p class="font-semibold text-navy-900">$89.00</p>
            </div>
          </div>

          {{-- Discount code --}}
          <div class="border-t border-slate-100 pt-4 mb-4">
            <label class="form-label mb-2" for="discount-code">Discount Code</label>
            <div class="flex gap-2">
              <input type="text" id="discount-code" x-model="discountCode" class="form-input flex-1" placeholder="Enter code" @keydown.enter="applyDiscount()">
              <button @click="applyDiscount()" class="btn-outline px-4 text-sm whitespace-nowrap">Apply</button>
            </div>
            <p x-show="discountMessage" class="text-sm mt-2" :class="discountValid ? 'text-sage-600' : 'text-red-600'" x-text="discountMessage"></p>
          </div>

          {{-- Totals --}}
          <div class="space-y-2 border-t border-slate-100 pt-4">
            <div class="flex justify-between text-sm">
              <span class="text-slate-500">Subtotal</span>
              <span class="text-slate-700" x-text="'$' + subtotal.toFixed(2)">$89.00</span>
            </div>
            <div class="flex justify-between text-sm" x-show="discount > 0">
              <span class="text-sage-600">Discount</span>
              <span class="text-sage-600" x-text="'-$' + discount.toFixed(2)"></span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-slate-500">Shipping</span>
              <span x-text="shipping === 0 ? 'FREE' : '$' + shipping.toFixed(2)" :class="shipping === 0 ? 'text-sage-600 font-semibold' : 'text-slate-700'">FREE</span>
            </div>
            <div class="flex justify-between font-bold text-lg border-t border-slate-200 pt-3 mt-2">
              <span class="text-navy-900">Total</span>
              <span class="text-navy-900" x-text="'$' + total.toFixed(2)">$89.00</span>
            </div>
          </div>
        </div>

        {{-- Trust elements --}}
        <div class="card p-5 space-y-3">
          @foreach([
            ['30-Day Money-Back Guarantee', 'Full refund, no questions asked', 'sage'],
            ['Free Shipping Over $75', 'Your order qualifies!', 'navy'],
            ['Secure Payment', '256-bit SSL encryption', 'gold'],
          ] as [$title, $desc, $color])
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-{{ $color }}-100 rounded-lg flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-{{ $color }}-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
              <p class="text-slate-800 text-sm font-semibold">{{ $title }}</p>
              <p class="text-slate-400 text-xs">{{ $desc }}</p>
            </div>
          </div>
          @endforeach
        </div>

        {{-- Doctor quote --}}
        <div class="card p-5">
          <div class="flex items-start gap-3">
            <img src="{{ asset('images/trust-doctor.png') }}" alt="Medical Advisor" class="w-12 h-12 rounded-xl object-cover flex-shrink-0" loading="lazy">
            <div>
              <p class="text-slate-700 text-xs leading-relaxed italic">"The Dainely Belt's decompression mechanism is clinically sound. I recommend it to patients with chronic lumbar pain."</p>
              <p class="text-navy-700 text-xs font-semibold mt-2">Dr. M. Reinholt — Physiotherapy Consultant</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
