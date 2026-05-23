@extends('layouts.app')
@section('title', 'Secure Checkout | Dainely')
@section('meta_description', 'Complete your Dainely order securely. Powered by Square Payments.')

{{-- Load Square Web Payments SDK in <head> --}}
@push('head_scripts')
@if($squareEnv === 'sandbox')
<script src="https://sandbox.web.squarecdn.com/v1/square.js"></script>
@else
<script src="https://web.squarecdn.com/v1/square.js"></script>
@endif
@endpush

@section('content')
<div class="min-h-screen bg-slate-50">

  {{-- Checkout header --}}
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
    <div class="grid lg:grid-cols-[1fr_400px] gap-10 items-start" x-data="checkoutForm()">

      {{-- LEFT: Multi-step form --}}
      <div class="space-y-6">

        {{-- Step indicator --}}
        <div class="flex items-center mb-2">
          <div class="flex items-center gap-2">
            <div class="step-indicator" :class="step >= 1 ? 'active' : ''">1</div>
            <span class="text-sm font-medium" :class="step >= 1 ? 'text-navy-700' : 'text-slate-400'">Contact</span>
          </div>
          <div class="flex-1 h-0.5 mx-3" :class="step >= 2 ? 'bg-navy-600' : 'bg-slate-200'"></div>
          <div class="flex items-center gap-2">
            <div class="step-indicator" :class="step >= 2 ? 'active' : ''">2</div>
            <span class="text-sm font-medium" :class="step >= 2 ? 'text-navy-700' : 'text-slate-400'">Shipping</span>
          </div>
          <div class="flex-1 h-0.5 mx-3" :class="step >= 3 ? 'bg-navy-600' : 'bg-slate-200'"></div>
          <div class="flex items-center gap-2">
            <div class="step-indicator" :class="step >= 3 ? 'active' : ''">3</div>
            <span class="text-sm font-medium" :class="step >= 3 ? 'text-navy-700' : 'text-slate-400'">Payment</span>
          </div>
        </div>

        {{-- STEP 1: Contact --}}
        <div class="card p-6" x-show="step === 1" x-transition>
          <h2 class="font-display font-bold text-navy-900 text-xl mb-6">Contact Information</h2>
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="form-label" for="first_name">First Name *</label>
              <input type="text" id="first_name" x-model="form.first_name" class="form-input" placeholder="Sarah" required autocomplete="given-name">
              <p x-show="errors.first_name" class="text-red-500 text-xs mt-1" x-text="errors.first_name"></p>
            </div>
            <div>
              <label class="form-label" for="last_name">Last Name *</label>
              <input type="text" id="last_name" x-model="form.last_name" class="form-input" placeholder="Mitchell" required autocomplete="family-name">
              <p x-show="errors.last_name" class="text-red-500 text-xs mt-1" x-text="errors.last_name"></p>
            </div>
            <div class="sm:col-span-2">
              <label class="form-label" for="email">Email Address *</label>
              <input type="email" id="email" x-model="form.email" class="form-input" placeholder="sarah@example.com" required autocomplete="email">
              <p class="text-slate-400 text-xs mt-1">Order confirmation will be sent here.</p>
              <p x-show="errors.email" class="text-red-500 text-xs mt-1" x-text="errors.email"></p>
            </div>
            <div class="sm:col-span-2">
              <label class="form-label" for="phone">Phone Number (optional)</label>
              <input type="tel" id="phone" x-model="form.phone" class="form-input" placeholder="+1 555 000 0000" autocomplete="tel">
            </div>
          </div>
          <div class="mt-6">
            <button type="button" @click="nextStep()" class="btn-primary-lg w-full justify-center">
              Continue to Shipping
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </button>
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
              <label class="form-label" for="address2">Apartment, Suite (optional)</label>
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
                <select id="country" x-model="form.country" class="form-input" required autocomplete="country">
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
              <label class="flex items-center justify-between p-4 border-2 rounded-xl cursor-pointer transition-colors"
                :class="form.shipping_method === 'standard' ? 'border-navy-600 bg-navy-50' : 'border-slate-200 hover:border-navy-300'">
                <div class="flex items-center gap-3">
                  <input type="radio" x-model="form.shipping_method" value="standard" class="text-navy-600">
                  <div>
                    <p class="font-semibold text-slate-800 text-sm">Standard Shipping</p>
                    <p class="text-slate-400 text-xs">5–8 business days</p>
                  </div>
                </div>
                <span class="font-semibold text-slate-700" x-text="subtotal >= 75 ? 'FREE' : '$9.99'"></span>
              </label>
              <label class="flex items-center justify-between p-4 border-2 rounded-xl cursor-pointer transition-colors"
                :class="form.shipping_method === 'express' ? 'border-navy-600 bg-navy-50' : 'border-slate-200 hover:border-navy-300'">
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
            <button type="button" @click="nextStep()" class="btn-primary-lg w-full justify-center">
              Continue to Payment
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </button>
          </div>
        </div>

        {{-- STEP 3: Payment --}}
        <div class="card p-6" x-show="step === 3" x-transition>
          <div class="flex items-center justify-between mb-6">
            <h2 class="font-display font-bold text-navy-900 text-xl">Payment</h2>
            <button @click="step = 2" class="text-navy-600 text-sm hover:underline">← Edit Shipping</button>
          </div>

          {{-- Card brands --}}
          <div class="flex items-center gap-2 mb-5">
            <span class="text-slate-500 text-xs">We accept:</span>
            @foreach(['VISA', 'MC', 'AMEX', 'DISCOVER'] as $card)
            <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-2 py-1 rounded">{{ $card }}</span>
            @endforeach
            <div class="ml-auto flex items-center gap-1 text-sage-600">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
              <span class="text-xs font-semibold">256-bit SSL</span>
            </div>
          </div>

          {{-- Square card container --}}
          <div class="border-2 border-slate-200 rounded-xl p-5 mb-5 bg-slate-50">
            <div class="flex items-center gap-2 mb-4">
              <svg class="w-8 h-8 text-navy-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
              <div>
                <p class="text-slate-800 font-semibold text-sm">Secure Card Payment</p>
                <p class="text-slate-400 text-xs">Your card details are never stored on our servers</p>
              </div>
            </div>

            {{-- Square Web Payments SDK mounts here --}}
            <div id="card-container" class="min-h-[56px]"></div>
            <div id="payment-status-container" class="text-sm text-slate-500 mt-2 hidden"></div>
          </div>

          {{-- Sandbox test card notice --}}
          @if($squareEnv === 'sandbox')
          <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5 text-sm">
            <p class="font-semibold text-amber-800 mb-1">🧪 Sandbox Mode — Test Card</p>
            <p class="text-amber-700 text-xs">Card: <strong>4111 1111 1111 1111</strong> &nbsp;|&nbsp; Exp: any future date &nbsp;|&nbsp; CVV: 111 &nbsp;|&nbsp; ZIP: 12345</p>
          </div>
          @endif

          {{-- Error / success messages --}}
          <div x-show="paymentError" class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4 text-red-700 text-sm" x-text="paymentError"></div>
          <div x-show="paymentSuccess" class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-4 text-emerald-700 text-sm font-semibold" x-text="paymentSuccess"></div>

          {{-- Place Order button --}}
          <button
            type="button"
            id="place-order-btn"
            @click="submitOrder()"
            :disabled="loading || !cardReady"
            class="btn-gold-lg w-full justify-center disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <span x-show="!loading" class="flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
              Place Order — <span x-text="'$' + total.toFixed(2)"></span>
            </span>
            <span x-show="loading" class="flex items-center gap-2">
              <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
              Processing...
            </span>
          </button>
          <p class="text-slate-400 text-xs text-center mt-3">
            By placing your order you agree to our
            <a href="{{ route('terms', ['locale' => $locale]) }}" class="underline hover:text-navy-700">Terms</a> and
            <a href="{{ route('privacy', ['locale' => $locale]) }}" class="underline hover:text-navy-700">Privacy Policy</a>.
          </p>
        </div>
      </div>

      {{-- RIGHT: Order Summary --}}
      <div class="space-y-4 lg:sticky lg:top-24">
        <div class="card p-6">
          <h3 class="font-display font-bold text-navy-900 text-lg mb-5">Order Summary</h3>

          <div class="space-y-4 mb-5">
            <div class="flex items-center gap-4">
              <div class="relative flex-shrink-0">
                <img src="{{ asset('images/dainely-belt-product.png') }}" alt="Dainely Belt" class="w-16 h-16 rounded-xl object-cover bg-slate-100">
                <span class="absolute -top-2 -right-2 w-5 h-5 bg-navy-600 text-white text-xs rounded-full flex items-center justify-center font-bold" x-text="qty"></span>
              </div>
              <div class="flex-1">
                <p class="font-semibold text-slate-800 text-sm">Dainely Belt</p>
                <p class="text-slate-400 text-xs">Premium Lumbar Support</p>
                <div class="flex items-center gap-2 mt-1">
                  <button @click="qty = Math.max(1, qty - 1)" class="w-6 h-6 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-sm flex items-center justify-center">−</button>
                  <span class="font-semibold text-navy-900 text-sm" x-text="qty"></span>
                  <button @click="qty++" class="w-6 h-6 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-sm flex items-center justify-center">+</button>
                </div>
              </div>
              <p class="font-semibold text-navy-900" x-text="'$' + (89 * qty).toFixed(2)"></p>
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
              <span class="text-slate-700" x-text="'$' + subtotal.toFixed(2)"></span>
            </div>
            <div class="flex justify-between text-sm" x-show="discount > 0">
              <span class="text-sage-600">Discount</span>
              <span class="text-sage-600" x-text="'-$' + discount.toFixed(2)"></span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-slate-500">Shipping</span>
              <span x-text="shipping === 0 ? 'FREE' : '$' + shipping.toFixed(2)"
                    :class="shipping === 0 ? 'text-sage-600 font-semibold' : 'text-slate-700'"></span>
            </div>
            <div class="flex justify-between font-bold text-lg border-t border-slate-200 pt-3 mt-2">
              <span class="text-navy-900">Total</span>
              <span class="text-navy-900" x-text="'$' + total.toFixed(2)"></span>
            </div>
          </div>
        </div>

        {{-- Trust badges --}}
        <div class="card p-5 space-y-3">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-sage-100 rounded-lg flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-sage-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
              <p class="text-slate-800 text-sm font-semibold">30-Day Money-Back Guarantee</p>
              <p class="text-slate-400 text-xs">Full refund, no questions asked</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-navy-100 rounded-lg flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-navy-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div>
              <p class="text-slate-800 text-sm font-semibold">Free Shipping Over $75</p>
              <p class="text-slate-400 text-xs">Your order qualifies!</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <div>
              <p class="text-slate-800 text-sm font-semibold">Secure Payment</p>
              <p class="text-slate-400 text-xs">Powered by Square — 256-bit SSL</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
// ─── Square SDK Config (injected by Laravel) ────────────────────────────
const SQUARE_APP_ID  = @json($squareAppId);
const SQUARE_ENV     = @json($squareEnv);
const CHECKOUT_ROUTE = "{{ route('checkout.process', ['locale' => $locale]) }}";
const CSRF_TOKEN     = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// ─── Alpine.js checkoutForm component ──────────────────────────────────
function checkoutForm() {
  return {
    step: 1,
    loading: false,
    cardReady: false,
    paymentError: '',
    paymentSuccess: '',
    qty: 1,
    discountCode: '',
    discountMessage: '',
    discountValid: false,
    discount: 0,

    form: {
      first_name: '',
      last_name: '',
      email: '',
      phone: '',
      address1: '',
      address2: '',
      city: '',
      state: '',
      zip: '',
      country: 'US',
      shipping_method: 'standard',
    },
    errors: {},

    // Square card instance
    squareCard: null,
    payments: null,

    get subtotal() { return 89 * this.qty; },
    get shipping() {
      if (this.subtotal >= 75) return 0;
      return this.form.shipping_method === 'express' ? 24.99 : 9.99;
    },
    get total() { return Math.max(0, this.subtotal - this.discount + this.shipping); },

    async init() {
      // Initialize Square Web Payments SDK when step 3 is shown
      this.$watch('step', async (val) => {
        if (val === 3 && !this.squareCard) {
          await this.initSquare();
        }
      });
    },

    async initSquare() {
      try {
        if (!window.Square) {
          this.paymentError = 'Payment SDK failed to load. Please refresh the page.';
          return;
        }

        const locationId = @json(config('square.location_id', ''));

        this.payments = window.Square.payments(SQUARE_APP_ID, locationId);
        this.squareCard = await this.payments.card({
          style: {
            '.input-container': { borderColor: '#e2e8f0', borderRadius: '0.75rem' },
            '.input-container.is-focus': { borderColor: '#1e3a8a' },
            input: { color: '#1e293b', fontFamily: 'Inter, sans-serif', fontSize: '14px' },
            'input::placeholder': { color: '#94a3b8' },
          }
        });
        await this.squareCard.attach('#card-container');
        this.cardReady = true;
      } catch (e) {
        console.error('Square init error:', e);
        this.paymentError = 'Could not load payment form: ' + e.message;
      }
    },

    validateStep() {
      this.errors = {};
      if (this.step === 1) {
        if (!this.form.first_name.trim()) this.errors.first_name = 'First name is required';
        if (!this.form.last_name.trim()) this.errors.last_name = 'Last name is required';
        if (!this.form.email.trim() || !this.form.email.includes('@')) this.errors.email = 'Valid email is required';
      }
      if (this.step === 2) {
        if (!this.form.address1.trim()) this.errors.address1 = 'Address is required';
        if (!this.form.city.trim()) this.errors.city = 'City is required';
        if (!this.form.zip.trim()) this.errors.zip = 'Postal code is required';
        if (!this.form.country) this.errors.country = 'Country is required';
      }
      return Object.keys(this.errors).length === 0;
    },

    nextStep() {
      if (this.validateStep()) this.step++;
    },

    async applyDiscount() {
      if (!this.discountCode.trim()) return;
      try {
        const res = await fetch("{{ route('checkout.validate-discount', ['locale' => $locale]) }}", {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
          body: JSON.stringify({ code: this.discountCode, subtotal_usd: this.subtotal }),
        });
        const data = await res.json();
        if (data.valid) {
          this.discountValid = true;
          this.discount = data.discount || 0;
          this.discountMessage = data.message || 'Discount applied!';
        } else {
          this.discountValid = false;
          this.discount = 0;
          this.discountMessage = data.message || 'Invalid code.';
        }
      } catch (e) {
        this.discountMessage = 'Could not validate code.';
      }
    },

    async submitOrder() {
      if (!this.squareCard) { this.paymentError = 'Payment form not ready.'; return; }
      this.loading = true;
      this.paymentError = '';
      this.paymentSuccess = '';

      try {
        // 1. Tokenize card
        const result = await this.squareCard.tokenize();

        if (result.status !== 'OK') {
          this.paymentError = result.errors?.[0]?.message || 'Card tokenization failed.';
          this.loading = false;
          return;
        }

        const sourceId = result.token;

        // 2. POST to Laravel checkout.process
        const res = await fetch(CHECKOUT_ROUTE, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
          body: JSON.stringify({
            source_id:      sourceId,
            first_name:     this.form.first_name,
            last_name:      this.form.last_name,
            email:          this.form.email,
            phone:          this.form.phone,
            address1:       this.form.address1,
            address2:       this.form.address2,
            city:           this.form.city,
            state:          this.form.state,
            zip:            this.form.zip,
            country:        this.form.country,
            shipping_method:this.form.shipping_method,
            qty:            this.qty,
            discount_code:  this.discountCode,
            amount_cents:   Math.round(this.total * 100),
          }),
        });

        const data = await res.json();

        if (data.success) {
          this.paymentSuccess = '✅ Payment successful! Redirecting...';
          setTimeout(() => { window.location.href = data.redirect || '/'; }, 1500);
        } else {
          this.paymentError = data.message || 'Payment failed. Please try again.';
        }
      } catch (e) {
        this.paymentError = 'An unexpected error occurred: ' + e.message;
      } finally {
        this.loading = false;
      }
    },
  };
}
</script>
@endpush

@endsection
