/**
 * Dainely — Main JavaScript Entry
 * Lightweight: Alpine.js + minimal utilities only
 */

import Alpine from 'alpinejs';

// Make Alpine available globally (for Blade-inline usage)
window.Alpine = Alpine;

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

// Product detail — require option selection before purchase actions
Alpine.data('productPurchase', (requiresOption = false, cartProduct = {}, cartAddUrl = '') => ({
  requiresOption: Boolean(requiresOption),
  cartProduct: cartProduct || {},
  cartAddUrl: cartAddUrl || '',
  selectedOption: null,
  qty: 1,
  get canPurchase() {
    return !this.requiresOption || this.selectedOption !== null;
  },
  get selectedVariant() {
    if (this.selectedOption !== null) {
      const variants = this.cartProduct.variants || [];
      const match = variants.find((variant) => {
        const key = variant.index ?? variant.title ?? variant.id;
        return key === this.selectedOption;
      });
      if (match) {
        return match;
      }
    }

    const variants = this.cartProduct.variants || [];
    if (variants.length === 1) {
      return variants[0];
    }

    return null;
  },
  get activeVariant() {
    return this.selectedVariant;
  },
  get unitPrice() {
    const variantPrice = this.selectedVariant?.price;
    if (variantPrice !== undefined && variantPrice !== null && variantPrice !== '') {
      return parseFloat(variantPrice);
    }

    return parseFloat(this.cartProduct.price || 0);
  },
  selectOption(value) {
    this.selectedOption = value;
  },
  incrementQty() {
    this.qty++;
  },
  decrementQty() {
    if (this.qty > 1) {
      this.qty--;
    }
  },
  optionClasses(value) {
    return this.selectedOption === value
      ? 'border-navy-600 bg-navy-50 text-navy-700'
      : 'border-slate-200 text-slate-600 hover:border-navy-400';
  },
  purchaseLinkClasses() {
    return this.canPurchase ? '' : 'opacity-50 cursor-not-allowed pointer-events-none';
  },
  handlePurchaseClick(event) {
    if (!this.canPurchase) {
      event.preventDefault();
    }
  },
  goToCheckout(event) {
    if (!this.canPurchase) {
      event.preventDefault();
      return;
    }

    event.preventDefault();

    const form = this.$refs.checkoutForm;
    if (!form || !this.cartAddUrl) {
      return;
    }

    const variant = this.activeVariant;
    const set = (name, value) => {
      const input = form.querySelector(`[name="${name}"]`);
      if (input) {
        input.value = value ?? '';
      }
    };

    set('product_id', this.cartProduct.id);
    set('title', this.cartProduct.title);
    set('subtitle', this.cartProduct.subtitle || '');
    set('image', this.cartProduct.image);
    set('price', this.unitPrice.toFixed(2));
    set('compare_at_price', variant?.compare_at_price ?? this.cartProduct.compare_at_price ?? '');
    set('quantity', this.qty);
    set('option_label', variant?.title ?? '');
    set('option_value', variant ? String(variant.id ?? this.selectedOption ?? '') : '');
    set('variant_id', variant?.id ? String(variant.id) : '');
    set('source', this.cartProduct.source || 'shopify');

    form.submit();
  },
}));

// Toast notifications
Alpine.data('toast', () => ({
  show: false,
  message: '',
  type: 'success', // success, error, info
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

// Currency display (reads from session/cookie)
Alpine.data('currencyDisplay', (basePrice, currency, symbol) => ({
  price: basePrice,
  currency,
  symbol,
  formatted() {
    return this.symbol + parseFloat(this.price).toFixed(2);
  },
}));

// Homepage Shopify product slider (3 columns per slide)
Alpine.data('productSlider', (totalSlides = 1) => ({
  current: 0,
  total: Math.max(1, totalSlides),
  prev() {
    this.current = (this.current - 1 + this.total) % this.total;
  },
  next() {
    this.current = (this.current + 1) % this.total;
  },
  goTo(index) {
    if (index >= 0 && index < this.total) {
      this.current = index;
    }
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

  let lastScroll = 0;
  window.addEventListener('scroll', () => {
    const current = window.scrollY;
    if (current > 20) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
    lastScroll = current;
  }, { passive: true });
});

// ── Lazy load images fallback ─────────────────────────
if ('loading' in HTMLImageElement.prototype) {
  // Native lazy loading supported — nothing extra needed
} else {
  // Polyfill for older browsers
  const images = document.querySelectorAll('img[loading="lazy"]');
  images.forEach(img => {
    img.src = img.dataset.src || img.src;
  });
}
