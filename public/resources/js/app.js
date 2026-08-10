/**
 * Dainely — Main JavaScript Entry
 * Lightweight: Alpine.js + minimal utilities only
 */

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import registerCheckoutForm from './checkout';

// Make Alpine available globally (for Blade-inline usage)
window.Alpine = Alpine;

// Register plugins
Alpine.plugin(collapse);
registerCheckoutForm(Alpine);

Alpine.store('cartDrawer', {
  open: false,
  message: '',
  itemCount: 0,
  checkoutUrl: '',
  timer: null,
  show(payload = {}) {
    this.message = payload.message || '';
    this.itemCount = payload.item_count || 0;
    this.checkoutUrl = payload.checkout_url || '';
    this.open = true;
    clearTimeout(this.timer);
    this.timer = setTimeout(() => this.dismiss(), 8000);
    this._updateBadges(this.itemCount);
  },
  dismiss() {
    this.open = false;
    clearTimeout(this.timer);
  },
  _updateBadges(count) {
    document.querySelectorAll('[data-cart-count]').forEach((el) => {
      el.textContent = count > 99 ? '99+' : String(count);
      const link = el.closest('[data-testid="header-cart-link"]');
      if (link) {
        link.setAttribute(
          'aria-label',
          count > 0 ? `Cart, ${count} items` : 'Cart',
        );
      }
    });
    document.querySelectorAll('[data-cart-count-wrap]').forEach((el) => {
      el.classList.toggle('hidden', count <= 0);
      el.classList.toggle('flex', count > 0);
    });
  },
});

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

// Product image gallery (standard + premium landings)
Alpine.data('productGallery', (images = []) => ({
  images: Array.isArray(images) ? images.filter(Boolean) : [],
  active: 0,
  setActive(index) { this.active = index; },
  prev() { this.active = (this.active - 1 + this.images.length) % this.images.length; },
  next() { this.active = (this.active + 1) % this.images.length; },
}));

Alpine.data('productLandingGallery', (images = [], thumbs = []) => ({
  images: Array.isArray(images) ? images.filter(Boolean) : [],
  thumbs: Array.isArray(thumbs) && thumbs.length
    ? thumbs.filter(Boolean)
    : (Array.isArray(images) ? images.filter(Boolean) : []),
  active: 0,
  setActive(i) { this.active = i; },
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
 * productPurchase — Add to Cart (stay on page) + Order Now (checkout).
 */
Alpine.data('productPurchase', (requiresOption = false, cartProduct = {}, cartAddUrl = '', checkoutUrl = '') => ({
  requiresOption: Boolean(requiresOption),
  cartProduct: cartProduct || {},
  cartAddUrl: cartAddUrl || '',
  checkoutUrl: checkoutUrl || '',
  selectedOption: null,
  optionError: false,
  optionErrorMessage:
    cartProduct?.messages?.selectOption || 'Please select an option above to continue.',
  qty: 1,
  loading: false,
  addedToCart: false,

  init() {
    const variants = this.cartProduct?.variants || [];
    if (variants.length > 0) {
      const first = variants[0];
      this.selectedOption =
        first.index !== undefined ? first.index : (first.title ?? first.id);
    }
  },

  get canPurchase() {
    return !this.requiresOption || this.selectedOption !== null;
  },

  get selectedVariant() {
    const variants = this.cartProduct.variants || [];

    // Products without a size picker still need the Shopify default variant for checkout.
    if (!this.requiresOption) {
      return variants.length > 0 ? variants[0] : null;
    }

    if (this.selectedOption === null) return null;
    const match = variants.find((v) => {
      const key = v.index !== undefined ? v.index : (v.title ?? v.id);
      return (
        key === this.selectedOption
        || v.title === this.selectedOption
        || String(v.id) === String(this.selectedOption)
      );
    });
    if (match) return match;
    if (typeof this.selectedOption === 'string') {
      return {
        title: this.selectedOption,
        id: this.selectedOption,
        price: this.cartProduct.price,
        compare_at_price: this.cartProduct.compare_at_price,
      };
    }
    return null;
  },

  get unitPrice() {
    const vp = this.selectedVariant?.price;
    if (vp !== undefined && vp !== null && vp !== '') return parseFloat(vp);
    return parseFloat(this.cartProduct.price || 0);
  },

  selectOption(value) {
    this.selectedOption = value;
    this.optionError = false;
    this.addedToCart = false;
  },

  validateOption() {
    if (this.canPurchase) {
      this.optionError = false;
      return true;
    }

    this.optionError = true;
    const block = window.innerWidth < 1024 ? 'center' : 'nearest';
    this.$refs.optionBlock?.scrollIntoView({ behavior: 'smooth', block });
    this.$refs.optionBlock?.focus?.({ preventScroll: true });
    return false;
  },

  incrementQty() {
    this.qty = Math.min(this.qty + 1, 20);
    this.addedToCart = false;
  },
  decrementQty() {
    this.qty = Math.max(this.qty - 1, 1);
    this.addedToCart = false;
  },

  optionClasses(value) {
    return this.selectedOption === value
      ? 'border-navy-600 bg-navy-50 text-navy-700'
      : 'border-slate-200 text-slate-600 hover:border-navy-400';
  },

  purchaseLinkClasses() {
    if (this.loading) return 'opacity-70 cursor-wait pointer-events-none';
    return '';
  },

  _cartPayload(intent = 'add') {
    const variant = this.selectedVariant;
    return {
      product_id: this.cartProduct.id || '',
      title: this.cartProduct.title || '',
      subtitle: this.cartProduct.subtitle || '',
      image: this.cartProduct.image || '',
      price: this.unitPrice.toFixed(2),
      compare_at_price: String(variant?.compare_at_price ?? this.cartProduct.compare_at_price ?? ''),
      quantity: String(this.qty),
      option_label: variant?.title ?? '',
      option_value: variant ? String(variant.id ?? this.selectedOption ?? '') : '',
      variant_id: variant?.id ? String(variant.id) : '',
      handle: this.cartProduct.handle || '',
      source: this.cartProduct.source || 'shopify',
      intent,
    };
  },

  _csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  },

  async _postCart(intent = 'add') {
    const url = this.cartAddUrl || window._cartAddUrl || '';
    if (!url) {
      throw new Error('cartAddUrl is missing');
    }

    const payload = this._cartPayload(intent);
    const body = new URLSearchParams();
    body.append('_token', this._csrfToken());
    Object.entries(payload).forEach(([key, value]) => {
      if (value !== '' && value !== null && value !== undefined) {
        body.append(key, String(value));
      }
    });
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
        'X-CSRF-TOKEN': this._csrfToken(),
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: body.toString(),
    });

    const data = await response.json().catch(() => ({}));
    if (!response.ok || !data.success) {
      throw new Error(data.message || 'Could not add item to cart.');
    }

    // Optimistic localStorage so checkout skeleton can paint instantly
    try {
      const product = this.cartProduct || {};
      const qty = Math.max(1, Number(this.quantity || product.quantity || 1));
      const line = {
        key: String(product.product_id || product.handle || 'item') + ':' + String(product.variant_id || 'default'),
        title: product.title || 'Product',
        image: product.image || '',
        quantity: qty,
        price: Number(product.price || 0),
        line_total: Number(product.price || 0) * qty,
        option_label: product.option_label || product.option_value || null,
      };
      let cached = { cartItems: [] };
      try {
        cached = JSON.parse(localStorage.getItem('dainely_cart_summary') || '{"cartItems":[]}');
      } catch (e) {}
      const items = Array.isArray(cached.cartItems) ? cached.cartItems.slice() : [];
      const idx = items.findIndex((i) => i.key === line.key);
      if (idx >= 0) {
        items[idx].quantity = Math.min(20, Number(items[idx].quantity || 1) + qty);
        items[idx].line_total = Number(items[idx].price || line.price) * items[idx].quantity;
      } else {
        items.push(line);
      }
      localStorage.setItem('dainely_cart_summary', JSON.stringify({
        cartItems: items,
        savedAt: Date.now(),
      }));
    } catch (e) {
      // ignore
    }

    return data;
  },

  async addToCart(event) {
    if (event) event.preventDefault();

    if (!this.validateOption()) return;

    this.loading = true;
    try {
      const data = await this._postCart('add');
      Alpine.store('cartDrawer').show(data);
      this.addedToCart = true;
    } catch (error) {
      console.error('[Dainely] addToCart failed', error);
      alert(error.message || 'Could not add item to cart.');
    } finally {
      this.loading = false;
    }
  },

  /** Order Now — add to cart then go to checkout. */
  async goToCheckout(event) {
    if (event) event.preventDefault();

    if (!this.validateOption()) return;

    this.loading = true;
    try {
      const data = await this._postCart('checkout');
      window.location.href = data.redirect || data.checkout_url || this.checkoutUrl || '/';
    } catch (error) {
      console.error('[Dainely] goToCheckout failed', error);
      this._fillAndSubmitFallback('checkout');
    } finally {
      this.loading = false;
    }
  },

  _fillAndSubmitFallback(intent = 'add') {
    const url = this.cartAddUrl || window._cartAddUrl || '';
    const form = this.$refs.checkoutForm;
    if (!form || !url) return;

    const set = (name, value) => {
      const el = form.querySelector(`[name="${name}"]`);
      if (el) el.value = value === null || value === undefined ? '' : String(value);
    };

    form.action = url;
    const payload = this._cartPayload(intent);
    Object.entries(payload).forEach(([name, value]) => set(name, value));

    let intentInput = form.querySelector('[name="intent"]');
    if (!intentInput) {
      intentInput = document.createElement('input');
      intentInput.type = 'hidden';
      intentInput.name = 'intent';
      form.appendChild(intentInput);
    }
    intentInput.value = intent;
    form.submit();
  },

  /** @deprecated Use _fillAndSubmitFallback */
  _fillAndSubmit(form, url) {
    this.cartAddUrl = url;
    this._fillAndSubmitFallback('checkout');
  },

  /** @deprecated Use _fillAndSubmitFallback */
  _submitDynamicForm(url) {
    this.cartAddUrl = url;
    this._fillAndSubmitFallback('checkout');
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

// Live-updating hero review count (Judge.me cache may be cold on first paint)
Alpine.data('productReviewHeader', (initial = {}, labelTemplate = ':count verified reviews') => ({
  average: Number(initial.average_rating ?? 0) || 0,
  total: Number(initial.total_reviews ?? 0) || 0,
  labelTemplate: labelTemplate || ':count verified reviews',
  get label() {
    try {
      return this.labelTemplate.replace(':count', Number(this.total || 0).toLocaleString());
    } catch (e) {
      return `${this.total} verified reviews`;
    }
  },
  init() {
    window.addEventListener('dainely-reviews-stats', (event) => {
      const stats = event.detail || {};
      if (typeof stats.average_rating !== 'undefined') {
        this.average = Number(stats.average_rating) || 0;
      }
      if (typeof stats.total_reviews !== 'undefined') {
        this.total = Number(stats.total_reviews) || 0;
      }
    });
  },
}));

// Judge.me reviews: load only when #reviews enters (or nears) the viewport
Alpine.data('lazyReviews', (url) => ({
  loading: false,
  loaded: false,
  error: false,
  html: '',
  observer: null,
  init() {
    if (!url || typeof IntersectionObserver === 'undefined') {
      // Older browsers: still defer past first paint
      requestAnimationFrame(() => this.load());
      return;
    }

    this.observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            this.observer?.disconnect();
            this.observer = null;
            this.load();
          }
        });
      },
      {
        // Start fetch slightly before the section is fully visible
        root: null,
        rootMargin: '200px 0px',
        threshold: 0.01,
      }
    );

    this.observer.observe(this.$el);
  },
  applyPayload(data) {
    this.html = data?.html ?? '';
    this.loaded = true;
    this.$nextTick(() => {
      if (this.$refs.content) {
        Alpine.initTree(this.$refs.content);
      }
      if (data?.stats) {
        window.dispatchEvent(new CustomEvent('dainely-reviews-stats', { detail: data.stats }));
      }
    });
  },
  async load() {
    if (this.loaded || this.loading || !url) {
      return;
    }

    this.loading = true;
    this.error = false;

    try {
      const response = await fetch(url, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const data = await response.json();
      this.applyPayload(data);
    } catch (e) {
      this.error = true;
      console.error('Failed to load reviews', e);
    } finally {
      this.loading = false;
    }
  },
}));

// Start Alpine (wait briefly on checkout if config is still loading on CDN hosts)
function bootAlpine() {
  Alpine.start();
}

if (document.getElementById('checkout-order-summary')) {
  const start = () => {
    const cfg = window.__CHECKOUT__ || {};
    const ready = (Array.isArray(cfg.cartItems) && cfg.cartItems.length > 0)
      || Number(cfg.summarySubtotal ?? cfg.pricing?.subtotal ?? 0) > 0;
    if (ready) {
      bootAlpine();
      return;
    }
    let attempts = 0;
    const timer = setInterval(() => {
      const next = window.__CHECKOUT__ || {};
      const ok = (Array.isArray(next.cartItems) && next.cartItems.length > 0)
        || Number(next.summarySubtotal ?? next.pricing?.subtotal ?? 0) > 0
        || ++attempts > 40;
      if (ok) {
        clearInterval(timer);
        bootAlpine();
      }
    }, 50);
  };
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
} else {
  bootAlpine();
}

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
