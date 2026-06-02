/**
 * Dainely — Main JavaScript Entry
 * Lightweight: Alpine.js + minimal utilities only
 */

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

// Make Alpine available globally (for Blade-inline usage)
window.Alpine = Alpine;

// Register plugins
Alpine.plugin(collapse);

// ── Alpine Components ────────────────────────────────

// Mobile navigation
Alpine.data('mobileNav', () => ({
  open: false,
  toggle() { this.open = !this.open; },
  close() { this.open = false; },
}));

// Language switcher
Alpine.data('langSwitcher', () => ({
  open: false,
  toggle() { this.open = !this.open; },
  close() { this.open = false; },
}));

// FAQ accordion — supports multiple open items
Alpine.data('faqAccordion', () => ({
  openItems: [],
  toggle(id) {
    if (this.openItems.includes(id)) {
      this.openItems = this.openItems.filter(i => i !== id);
    } else {
      this.openItems.push(id);
    }
  },
  isOpen(id) {
    return this.openItems.includes(id);
  },
}));

// Product image gallery
Alpine.data('productGallery', (images = []) => ({
  images,
  active: 0,
  setActive(index) { this.active = index; },
  prev() { this.active = (this.active - 1 + this.images.length) % this.images.length; },
  next() { this.active = (this.active + 1) % this.images.length; },
}));

// Cart quantity selector
Alpine.data('quantitySelector', (initial = 1, min = 1, max = 10) => ({
  qty: initial,
  min,
  max,
  increment() { if (this.qty < this.max) this.qty++; },
  decrement() { if (this.qty > this.min) this.qty--; },
}));

/**
 * productPurchase — handles Add to Cart / Order Now on:
 *   - Product detail page (show.blade.php)
 *   - Product catalog cards (pages/products/index.blade.php)
 *   - Home page slider (shopify-products-slider.blade.php)
 *
 * Flow: goToCheckout() → fills hidden <form x-ref="checkoutForm"> → submit
 *   → POST /en/cart/add → CartController::store() → session
 *   → redirect → /en/checkout → Square payment
 */
Alpine.data('productPurchase', (requiresOption = false, cartProduct = {}, cartAddUrl = '') => ({
  requiresOption: Boolean(requiresOption),
  cartProduct: cartProduct || {},
  cartAddUrl: cartAddUrl || '',
  selectedOption: null,
  qty: 1,
  loading: false,

  init() {
    // Auto-select the first option/variant by default if options are available
    const variants = this.cartProduct?.variants || [];
    if (variants.length > 0) {
      const first = variants[0];
      this.selectedOption = first.index !== undefined ? first.index : (first.title ?? first.id);
    }
  },

  get canPurchase() {
    return !this.requiresOption || this.selectedOption !== null;
  },

  get selectedVariant() {
    if (!this.requiresOption || this.selectedOption === null) return null;
    const variants = this.cartProduct.variants || [];
    return variants.find((v) => {
      const key = v.index !== undefined ? v.index : (v.title ?? v.id);
      return key === this.selectedOption;
    }) ?? null;
  },

  get unitPrice() {
    const vp = this.selectedVariant?.price;
    if (vp !== undefined && vp !== null && vp !== '') return parseFloat(vp);
    return parseFloat(this.cartProduct.price || 0);
  },

  selectOption(value) {
    this.selectedOption = value;
  },

  incrementQty() { this.qty = Math.min(this.qty + 1, 20); },
  decrementQty() { this.qty = Math.max(this.qty - 1, 1); },

  optionClasses(value) {
    return this.selectedOption === value
      ? 'border-navy-600 bg-navy-50 text-navy-700'
      : 'border-slate-200 text-slate-600 hover:border-navy-400';
  },

  purchaseLinkClasses() {
    return this.canPurchase ? '' : 'opacity-70 cursor-not-allowed';
  },

  /**
   * Main action: fills the hidden form and submits it.
   * Called by both "Add to Cart" and "Order Now" buttons.
   */
  goToCheckout(event) {
    if (event) event.preventDefault();

    if (!this.canPurchase) {
      alert('Please select a size/option first.');
      return;
    }

    // Resolve URL — fall back to cart.store URL from data or global
    const url = this.cartAddUrl
      || (window._cartAddUrl || '');

    if (!url) {
      console.error('[Dainely] cartAddUrl is missing on this productPurchase component.');
      return;
    }

    // Find the hidden form scoped to this Alpine component
    const form = this.$refs.checkoutForm;

    if (!form) {
      // Fallback: create and submit a dynamic form
      this._submitDynamicForm(url);
      return;
    }

    this._fillAndSubmit(form, url);
  },

  /** Fill each hidden field and submit the form */
  _fillAndSubmit(form, url) {
    const variant = this.selectedVariant;

    const set = (name, value) => {
      const el = form.querySelector(`[name="${name}"]`);
      if (el) el.value = (value === null || value === undefined) ? '' : String(value);
    };

    // Ensure action points to the right URL
    form.action = url;

    set('product_id',       this.cartProduct.id || '');
    set('title',            this.cartProduct.title || '');
    set('subtitle',         this.cartProduct.subtitle || '');
    set('image',            this.cartProduct.image || '');
    set('price',            this.unitPrice.toFixed(2));
    set('compare_at_price', variant?.compare_at_price ?? this.cartProduct.compare_at_price ?? '');
    set('quantity',         this.qty);
    set('option_label',     variant?.title ?? '');
    set('option_value',     variant ? (variant.id ?? this.selectedOption ?? '') : '');
    set('variant_id',       variant?.id ?? '');
    set('source',           this.cartProduct.source || 'shopify');

    form.submit();
  },

  /** Last-resort fallback: create a temporary form */
  _submitDynamicForm(url) {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';
    const variant = this.selectedVariant;

    const fields = {
      _token:           csrf,
      product_id:       this.cartProduct.id || '',
      title:            this.cartProduct.title || '',
      subtitle:         this.cartProduct.subtitle || '',
      image:            this.cartProduct.image || '',
      price:            this.unitPrice.toFixed(2),
      compare_at_price: variant?.compare_at_price ?? this.cartProduct.compare_at_price ?? '',
      quantity:         this.qty,
      option_label:     variant?.title ?? '',
      option_value:     variant ? (variant.id ?? this.selectedOption ?? '') : '',
      variant_id:       variant?.id ?? '',
      source:           this.cartProduct.source || 'shopify',
    };

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    form.style.display = 'none';

    Object.entries(fields).forEach(([name, value]) => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = name;
      input.value = String(value ?? '');
      form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
  },
}));

// Toast notifications
Alpine.data('toast', () => ({
  show: false,
  message: '',
  type: 'success',
  timer: null,
  trigger(message, type = 'success', duration = 4000) {
    this.message = message;
    this.type = type;
    this.show = true;
    clearTimeout(this.timer);
    this.timer = setTimeout(() => { this.show = false; }, duration);
  },
  dismiss() { this.show = false; },
}));

// Currency display
Alpine.data('currencyDisplay', (basePrice, currency, symbol) => ({
  price: basePrice,
  currency,
  symbol,
  formatted() {
    return this.symbol + parseFloat(this.price).toFixed(2);
  },
}));

// Homepage Shopify product slider
Alpine.data('productSlider', (totalSlides = 1) => ({
  current: 0,
  total: Math.max(1, totalSlides),
  prev() { this.current = (this.current - 1 + this.total) % this.total; },
  next() { this.current = (this.current + 1) % this.total; },
  goTo(index) {
    if (index >= 0 && index < this.total) this.current = index;
  },
}));

// Scroll-to-top button
Alpine.data('scrollTop', () => ({
  visible: false,
  init() {
    window.addEventListener('scroll', () => {
      this.visible = window.scrollY > 400;
    }, { passive: true });
  },
  scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  },
}));

// Start Alpine
Alpine.start();

// ── Intersection Observer for scroll animations ──────
document.addEventListener('DOMContentLoaded', () => {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.1, rootMargin: '0px 0px -40px 0px' }
  );

  document.querySelectorAll('.animate-on-scroll').forEach(el => {
    observer.observe(el);
  });
});

// ── Sticky header shadow on scroll ───────────────────
document.addEventListener('DOMContentLoaded', () => {
  const header = document.getElementById('site-header');
  if (!header) return;

  window.addEventListener('scroll', () => {
    if (window.scrollY > 20) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  }, { passive: true });
});

// ── Lazy load images fallback ─────────────────────────
if (!('loading' in HTMLImageElement.prototype)) {
  const images = document.querySelectorAll('img[loading="lazy"]');
  images.forEach(img => {
    img.src = img.dataset.src || img.src;
  });
}
