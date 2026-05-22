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

// Checkout form state
Alpine.data('checkoutForm', () => ({
  step: 1,
  loading: false,
  paymentError: null,
  country: '',
  isEuCountry() {
    const euCountries = ['AT','BE','BG','CY','CZ','DE','DK','EE','ES','FI','FR','GR','HR','HU','IE','IT','LT','LU','LV','MT','NL','PL','PT','RO','SE','SI','SK'];
    return euCountries.includes(this.country);
  },
  nextStep() { if (this.step < 3) this.step++; },
  prevStep() { if (this.step > 1) this.step--; },
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
