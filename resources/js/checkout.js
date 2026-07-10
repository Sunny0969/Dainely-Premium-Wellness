/**
 * Checkout multi-step form + order summary (Alpine).
 * Config is injected from checkout/index.blade.php via window.__CHECKOUT__.
 */
function checkoutCfg() {
  return window.__CHECKOUT__ || {};
}

function lockedDisplayCurrency(cfg = checkoutCfg()) {
  return {
    code: String(cfg.currencyCode || 'USD').toUpperCase(),
    symbol: String(cfg.currencySymbol || '$'),
    rate: Number(cfg.exchangeRate ?? 1),
    locked: Boolean(cfg.lockDisplayCurrency),
  };
}

export default function registerCheckoutForm(Alpine) {
  Alpine.data('checkoutForm', () => {
    const cfg = checkoutCfg();
    const checkoutI18n = cfg.i18n || {};
    const locked = lockedDisplayCurrency(cfg);

    return {
      step: 1,
      loading: false,
      cardReady: false,
      checkoutReady: false,
      paymentError: '',
      paymentSuccess: '',
      _lockedCurrency: locked,
      _countryCurrencyOverride: null,
      cartItems: [],
      pricing: cfg.pricing || {},
      currencySymbol: locked.symbol,
      currencyCode: locked.code,
      paymentCurrency: String(cfg.paymentCurrency || cfg.currencyCode || locked.code).toUpperCase(),
      paymentCountry: String(cfg.paymentCountry || cfg.defaultCountry || 'US').toUpperCase(),
      chargeCurrency: cfg.chargeCurrency || 'USD',
      usdSymbol: cfg.usdSymbol || '$',
      exchangeRate: locked.rate,
      squareLocale: cfg.squareLocale || 'en-US',
      sizeLabel: cfg.sizeLabel || 'Size',
      checkoutI18n,
      labelFree: cfg.labelFree || 'FREE',
      labelTaxCalculating: cfg.labelTaxCalculating || 'Calculating…',
      freeShipQualifies: cfg.freeShipQualifies || '',
      freeShipRemaining: cfg.freeShipRemaining || '',
      discountCode: '',
      discountMessage: '',
      discountValid: false,
      discount: 0,
      shippingCost: Number(cfg.shippingCost ?? 0),
      taxAmount: Number(cfg.taxAmount ?? 0),
      taxLoading: false,
      taxError: '',
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
        billing_zip: '',
        country: cfg.defaultCountry || 'US',
        shipping_method: 'standard',
      },
      errors: {},
      squareCard: null,
      payments: null,
      formatMoney(amount) {
        this.lockDisplayCurrency();
        return this._lockedCurrency.symbol + Number(amount || 0).toFixed(2);
      },
      formatPaymentAmount(amount) {
        return Number(amount || 0).toFixed(2);
      },
      displaySubtotal() {
        this.lockDisplayCurrency();
        return this.formatMoney(this.subtotal());
      },
      displayTax() {
        this.lockDisplayCurrency();
        if (this.taxLoading) return this.labelTaxCalculating;
        return this.formatMoney(this.taxAmount);
      },
      displayTotal() {
        this.lockDisplayCurrency();
        return this.formatMoney(this.total());
      },
      formatUsd(amount) {
        return this.usdSymbol + Number(amount || 0).toFixed(2);
      },
      itemOptionText(item) {
        if (item?.option_label) {
          return `${this.sizeLabel}: ${item.option_label}`;
        }
        return item?.subtitle || '';
      },
      resolveUnitUsd(item) {
        const unitUsd = Number(item?.unit_price_usd);
        if (unitUsd > 0) return unitUsd;
        const price = Number(item?.price);
        if (price > 0) return price;
        return 0;
      },
      resolveUnitLocal(item) {
        const unitLocal = Number(item?.unit_price);
        if (unitLocal > 0) return unitLocal;
        const lineLocal = Number(item?.line_total);
        const qty = Math.max(1, Number(item?.quantity || 1));
        if (lineLocal > 0) return Math.round((lineLocal / qty) * 100) / 100;
        const unitUsd = this.resolveUnitUsd(item);
        const rate = Number(this._lockedCurrency.rate || 1);
        return this._lockedCurrency.code === this.chargeCurrency
          ? unitUsd
          : Math.round(unitUsd * rate * 100) / 100;
      },
      resolveLineUsd(item) {
        const qty = Math.max(1, Number(item?.quantity || 1));
        const unitUsd = this.resolveUnitUsd(item);
        if (unitUsd > 0) {
          return Math.round(unitUsd * qty * 100) / 100;
        }
        const direct = Number(item?.line_total_usd);
        if (direct > 0) return direct;
        return 0;
      },
      resolveLineLocal(item) {
        const qty = Math.max(1, Number(item?.quantity || 1));
        const unitLocal = this.resolveUnitLocal(item);
        if (unitLocal > 0) {
          return Math.round(unitLocal * qty * 100) / 100;
        }
        const direct = Number(item?.line_total);
        if (direct > 0) return direct;
        return 0;
      },
      normalizeCartItem(item) {
        const qty = Math.max(1, Number(item?.quantity || 1));
        const unitUsd = this.resolveUnitUsd(item);
        const unitLocal = this.resolveUnitLocal({ ...item, unit_price_usd: unitUsd });
        const lineUsd = this.resolveLineUsd({ ...item, quantity: qty, unit_price_usd: unitUsd });
        const lineLocal = this.resolveLineLocal({
          ...item,
          quantity: qty,
          unit_price: unitLocal,
          line_total_usd: lineUsd,
        });

        return {
          ...item,
          quantity: qty,
          unit_price_usd: Math.round(unitUsd * 100) / 100,
          line_total_usd: Math.round(lineUsd * 100) / 100,
          unit_price: Math.round(unitLocal * 100) / 100,
          line_total: Math.round(lineLocal * 100) / 100,
        };
      },
      lineTotalUsd(item) {
        return this.resolveLineUsd(item);
      },
      lineTotal(item) {
        return this.resolveLineLocal(item);
      },
      shippingRate(method = 'standard') {
        const rate = Number(this._lockedCurrency.rate || 1);
        const fallbackUsd = method === 'express' ? 24.99 : 9.99;
        const fallback = this._lockedCurrency.code === this.chargeCurrency
          ? fallbackUsd
          : Math.round(fallbackUsd * rate * 100) / 100;
        const configured = Number(
          method === 'express'
            ? this.pricing.express_shipping_rate
            : this.pricing.standard_shipping_rate,
        );
        return configured > 0 ? configured : fallback;
      },
      cartItemCount() {
        return this.cartItemsList().reduce((sum, item) => sum + Math.max(1, Number(item?.quantity || 1)), 0);
      },
      async waitForCheckoutConfig() {
        for (let attempt = 0; attempt < 40; attempt++) {
          const cfg = checkoutCfg();
          const hasData = (Array.isArray(cfg.cartItems) && cfg.cartItems.length > 0)
            || Number(cfg.summarySubtotal ?? cfg.pricing?.subtotal ?? 0) > 0;
          const onCheckout = Boolean(document.getElementById('checkout-order-summary'));
          if (!onCheckout || hasData) {
            return;
          }
          await new Promise((resolve) => setTimeout(resolve, 50));
        }
      },
      cartItemsList(raw = this.cartItems) {
        if (Array.isArray(raw)) return raw;
        if (raw && typeof raw === 'object') return Object.values(raw);
        return [];
      },
      subtotalUsd() {
        const computed = this.cartItemsList().reduce((sum, item) => {
          const line = this.lineTotalUsd(item);
          return sum + (Number.isFinite(line) ? line : 0);
        }, 0);
        if (computed > 0) return computed;
        const fromPricing = Number(this.pricing?.subtotal_usd ?? 0);
        if (fromPricing > 0) return fromPricing;
        return Number(checkoutCfg().summarySubtotalUsd ?? 0);
      },
      subtotal() {
        const computed = this.cartItemsList().reduce((sum, item) => {
          const line = this.lineTotal(item);
          return sum + (Number.isFinite(line) ? line : 0);
        }, 0);
        if (computed > 0) return computed;
        const fromPricing = Number(this.pricing?.subtotal ?? 0);
        if (fromPricing > 0) return fromPricing;
        return Number(checkoutCfg().summarySubtotal ?? 0);
      },
      total() {
        return Math.max(0, this.subtotal() - this.discount + this.shippingCost + this.taxAmount);
      },
      totalUsd() {
        const rate = Number(this._lockedCurrency.rate || 1);
        if (rate <= 0 || this._lockedCurrency.code === this.chargeCurrency) {
          return this.total();
        }
        return Math.round((this.total() / rate) * 100) / 100;
      },
      lockDisplayCurrency() {
        if (this._lockedCurrency?.locked && !this._countryCurrencyOverride) {
          this.currencyCode = this._lockedCurrency.code;
          this.currencySymbol = this._lockedCurrency.symbol;
          this.exchangeRate = this._lockedCurrency.rate;
          this.paymentCurrency = this._lockedCurrency.code;
          return;
        }
        if (this._countryCurrencyOverride) {
          this.currencyCode = this._countryCurrencyOverride.code;
          this.currencySymbol = this._countryCurrencyOverride.symbol;
          this.exchangeRate = this._countryCurrencyOverride.rate;
          this.paymentCurrency = this._countryCurrencyOverride.code;
          return;
        }
        const next = lockedDisplayCurrency(checkoutCfg());
        this._lockedCurrency = { ...next };
        this.currencyCode = next.code;
        this.currencySymbol = next.symbol;
        this.exchangeRate = next.rate;
        this.paymentCurrency = next.code;
      },
      zipPlaceholder() {
        const cfg = checkoutCfg();
        const country = String(this.form.country || cfg.defaultCountry || 'US').toUpperCase();
        return (cfg.postalPlaceholders || {})[country] || '10001';
      },
      zipInvalidMessage(country = null) {
        const cfg = checkoutCfg();
        const code = String(country || this.form.country || cfg.defaultCountry || 'US').toUpperCase();
        const example = (cfg.postalPlaceholders || {})[code] || '10001';
        const template = checkoutI18n.err_zip_invalid
          || 'Enter a valid postal code for the selected country (e.g. :example).';
        return template.replace(/:example/g, example);
      },
      normalizePostal(country, postal) {
        const code = String(country || 'US').toUpperCase();
        let value = String(postal || '').trim().replace(/\s+/g, ' ');
        const upperList = (checkoutCfg().postalUppercase || ['GB', 'UK', 'CA', 'IE', 'NL'])
          .map((entry) => String(entry).toUpperCase());
        if (upperList.includes(code)) {
          value = value.toUpperCase();
        }
        if ((code === 'GB' || code === 'UK') && !value.includes(' ') && value.length > 3) {
          value = `${value.slice(0, -3)} ${value.slice(-3)}`;
        }
        return value;
      },
      syncBillingZipFromShipping() {
        this.form.billing_zip = this.form.zip;
      },
      squareCardPostalPrefill() {
        const code = String(this.form.country || 'US').toUpperCase();
        const value = String(this.form.zip || '').trim();
        if (!value || !this.isValidPostalCode(code, value)) {
          return null;
        }

        const normalized = this.normalizePostal(code, value);
        if (/[A-Za-z]/.test(normalized)) {
          return null;
        }

        const skip = (checkoutCfg().squareSkipPrefillCountries || ['AU', 'NZ'])
          .map((entry) => String(entry).toUpperCase());
        if (skip.includes(code)) {
          return null;
        }

        return normalized;
      },
      validateBillingPostal() {
        this.syncBillingZipFromShipping();
        if (this.isBlank(this.form.zip)) {
          this.paymentError = checkoutI18n.err_zip;
          return false;
        }
        if (!this.isValidPostalCode(this.form.country, this.form.zip)) {
          this.paymentError = this.zipInvalidMessage(this.form.country);
          return false;
        }
        if (this.paymentError && /postal|zip|postcode|match/i.test(this.paymentError)) {
          this.paymentError = '';
        }
        return true;
      },
      isValidPostalCode(country, postal) {
        const value = String(postal || '').trim();
        if (!value) return false;

        const code = String(country || 'US').toUpperCase();
        const cfg = checkoutCfg();
        const patterns = cfg.postalPatterns || {};
        const upperList = (cfg.postalUppercase || ['GB', 'UK', 'CA', 'IE', 'NL']).map((c) => String(c).toUpperCase());
        const normalized = upperList.includes(code) ? value.toUpperCase() : value;
        const patternBody = patterns[code] || patterns.default || '^\\d[\\d\\s\\-]{2,10}$';

        try {
          return new RegExp(patternBody, 'i').test(normalized);
        } catch (error) {
          console.error('[Dainely] invalid postal pattern', code, patternBody, error);
          return /^\d[\d\s\-]{2,10}$/.test(normalized);
        }
      },
      squareLocaleForCountry(country) {
        const code = String(country || '').toUpperCase();
        const map = checkoutCfg().squareCountryLocales || {};
        return map[code] || checkoutCfg().squareLocale || this.squareLocale || 'en-US';
      },
      scheduleSquareRefresh() {
        if (this.step !== 3) {
          return;
        }
        if (this._squareRefreshTimer) {
          clearTimeout(this._squareRefreshTimer);
        }
        this._squareRefreshTimer = setTimeout(() => {
          this._squareRefreshTimer = null;
          this.initSquare();
        }, 300);
      },
      syncCurrencyFromCountry() {
        const cfg = checkoutCfg();
        const country = String(this.form.country || '').toUpperCase();
        if (!country) return;
        const map = cfg.countryCurrency || {};
        const code = map[country];
        if (!code) return;
        const meta = (cfg.currencyMeta || {})[code];
        if (!meta) return;

        if (this._lockedCurrency?.locked && code === this._lockedCurrency.code) {
          this._countryCurrencyOverride = null;
          this.lockDisplayCurrency();
          return;
        }

        this._countryCurrencyOverride = {
          code,
          symbol: meta.symbol || '$',
          rate: Number(meta.rate || 1),
        };
        this.currencyCode = code;
        this.currencySymbol = meta.symbol || '$';
        this.exchangeRate = Number(meta.rate || 1);
        this.paymentCurrency = code;
      },
      onCountryChange() {
        this.syncCurrencyFromCountry();
        if (this.errors.zip) {
          delete this.errors.zip;
        }
        this.applyFallbackTax();
        this.scheduleSquareRefresh();
      },
      calcShipping() {
        const threshold = Number(this.pricing.free_shipping_threshold ?? 75);
        if (this.subtotal() >= threshold) return 0;
        return this.shippingRate(this.form.shipping_method);
      },
      syncTotalsFromServer(totals) {
        if (totals && typeof totals === 'object') {
          if (totals.subtotal !== undefined && totals.subtotal !== null) {
            this.pricing = { ...this.pricing, subtotal: Number(totals.subtotal) };
          }
          if (totals.subtotal_usd !== undefined && totals.subtotal_usd !== null) {
            this.pricing = { ...this.pricing, subtotal_usd: Number(totals.subtotal_usd) };
          }
          if (totals.shipping !== undefined && totals.shipping !== null) {
            this.shippingCost = Number(totals.shipping);
          } else {
            this.shippingCost = this.calcShipping();
          }
          if (totals.tax !== undefined && totals.tax !== null) {
            this.taxAmount = Number(totals.tax);
          }
          if (totals.discount !== undefined && totals.discount !== null) {
            this.discount = Number(totals.discount);
            if (this.discount > 0) {
              this.discountValid = true;
            }
          }
        } else {
          this.shippingCost = this.calcShipping();
        }
        this.lockDisplayCurrency();
      },
      squareCredentials() {
        const cfg = checkoutCfg();
        const el = document.getElementById('card-container');
        const appId = String(cfg.squareAppId || el?.dataset?.squareAppId || '').trim();
        const locationId = String(cfg.squareLocationId || el?.dataset?.squareLocationId || '').trim();
        const locale = String(
          cfg.squareLocale || el?.dataset?.squareLocale || this.squareLocale || 'en-US',
        ).trim();
        return { appId, locationId, locale };
      },
      buildSquareVerificationDetails() {
        this.lockDisplayCurrency();
        const paymentCurrency = String(
          this.paymentCurrency || this._lockedCurrency.code || 'USD',
        ).toUpperCase();
        const countryCode = String(this.form.country || this.paymentCountry || 'US').toUpperCase();
        const addressLines = [this.form.address1, this.form.address2]
          .map((line) => String(line || '').trim())
          .filter(Boolean);

        return {
          amount: this.formatPaymentAmount(this.total()),
          currencyCode: paymentCurrency,
          intent: 'CHARGE',
          customerInitiated: true,
          sellerKeyedIn: false,
          billingContact: {
            givenName: this.form.first_name || undefined,
            familyName: this.form.last_name || undefined,
            email: this.form.email || undefined,
            phone: this.form.phone || undefined,
            addressLines: addressLines.length ? addressLines : undefined,
            city: this.form.city || undefined,
            state: this.form.state || undefined,
            countryCode,
            postalCode: this.form.zip || undefined,
          },
        };
      },
      applyClientConfig() {
        const fresh = checkoutCfg();
        this.pricing = fresh.pricing || this.pricing || {};
        this.chargeCurrency = fresh.chargeCurrency || this.chargeCurrency;
        this.usdSymbol = fresh.usdSymbol || this.usdSymbol;
        this.squareLocale = fresh.squareLocale || this.squareLocale;
        this.sizeLabel = fresh.sizeLabel || this.sizeLabel;
        this.labelFree = fresh.labelFree || this.labelFree;
        this.labelTaxCalculating = fresh.labelTaxCalculating || this.labelTaxCalculating;
        this.freeShipQualifies = fresh.freeShipQualifies || this.freeShipQualifies;
        this.freeShipRemaining = fresh.freeShipRemaining || this.freeShipRemaining;
        if (!this.form.country) {
          this.form.country = fresh.defaultCountry || fresh.paymentCountry || 'US';
        }
        if (!this._lockedCurrency?.locked) {
          this.paymentCountry = String(
            fresh.paymentCountry || fresh.defaultCountry || this.paymentCountry || 'US',
          ).toUpperCase();
        }
        this.lockDisplayCurrency();
      },
      applyFallbackTax() {
        const cfg = checkoutCfg();
        const country = String(this.form.country || cfg.defaultCountry || 'US').toUpperCase();
        const state = String(this.form.state || '').toUpperCase();
        const table = cfg.taxFallback || {};
        const rates = table[country] || table.US || { default: 0.07 };
        const rate = Number(rates.states?.[state] ?? rates.default ?? 0);
        const taxUsd = Math.round(this.subtotalUsd() * rate * 100) / 100;
        const fx = Number(this._lockedCurrency.rate || 1);
        this.taxAmount = this._lockedCurrency.code === this.chargeCurrency
          ? taxUsd
          : Math.round(taxUsd * fx * 100) / 100;
        this.shippingCost = this.calcShipping();
        this.lockDisplayCurrency();
        if (this.taxAmount > 0) {
          this.taxError = '';
        }
      },
      updateQty(key, delta) {
        const item = this.cartItems.find((i) => i.key === key);
        if (!item) return;
        if (delta < 0 && item.quantity <= 1) {
          this.removeItem(key);
          return;
        }
        item.quantity = Math.max(1, Math.min(20, item.quantity + delta));
        const normalized = this.normalizeCartItem(item);
        Object.assign(item, normalized);
        this.shippingCost = this.calcShipping();
        this.applyFallbackTax();
        this.lockDisplayCurrency();
        this.syncCartSession();
      },
      async removeItem(key) {
        this.cartItems = this.cartItems.filter((i) => i.key !== key);
        await this.syncCartSession({ remove_key: key });
        if (this.cartItems.length === 0) {
          return;
        }
        this.shippingCost = this.calcShipping();
        this.applyFallbackTax();
        this.lockDisplayCurrency();
        if (this.step >= 2 && this.form.address1 && this.form.zip) {
          const ok = await this.fetchTax();
          if (!ok) {
            this.applyFallbackTax();
          }
        }
      },
      async syncCartSession(extra = {}) {
        const url = checkoutCfg().urls?.cartUpdate;
        if (!url) return;

        try {
          const body = { ...extra };
          if (!extra.remove_key) {
            body.line_quantities = this.lineQuantitiesPayload();
          }

          const res = await fetch(url, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              Accept: 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
              'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(body),
          });

          const data = await res.json().catch(() => ({}));
          if (!res.ok || !data.success) {
            return;
          }

          if (data.empty) {
            window.location.href = data.redirect || checkoutCfg().urls?.shopUrl || '/';
            return;
          }

          if (Array.isArray(data.cartItems)) {
            this.cartItems = data.cartItems.map((item) => this.normalizeCartItem(item));
          }
        } catch (error) {
          console.error('[Dainely] cart sync failed', error);
        }
      },
      lineQuantitiesPayload() {
        return Object.fromEntries(this.cartItemsList().map((item) => [item.key, item.quantity]));
      },
      bootstrapCheckoutState() {
        const fresh = checkoutCfg();
        this.applyClientConfig();
        this.pricing = {
          ...(this.pricing || {}),
          ...(fresh.pricing || {}),
        };
        if (!Number(this.pricing?.subtotal) && Number(fresh.summarySubtotal) > 0) {
          this.pricing.subtotal = Number(fresh.summarySubtotal);
        }
        if (!Number(this.pricing?.subtotal_usd) && Number(fresh.summarySubtotalUsd) > 0) {
          this.pricing.subtotal_usd = Number(fresh.summarySubtotalUsd);
        }
        const sourceItems = fresh.cartItems ?? cfg.cartItems ?? [];
        this.cartItems = this.cartItemsList(sourceItems).map((item) => this.normalizeCartItem(item));
        this.shippingCost = Number(fresh.shippingCost ?? this.pricing?.shipping ?? this.calcShipping());
        this.taxAmount = Number(
          fresh.taxAmount
          ?? this.pricing?.tax
          ?? fresh.summaryTax
          ?? 0,
        );
        if (this.taxAmount <= 0 && this.subtotal() > 0) {
          this.applyFallbackTax();
        }
        this.lockDisplayCurrency();
        this.checkoutReady = true;
      },
      async init() {
        await this.waitForCheckoutConfig();
        this.bootstrapCheckoutState();

        this.$watch('step', async (val) => {
          if (val === 3) {
            this.syncBillingZipFromShipping();
            if (this.taxAmount <= 0 && this.form.address1 && this.form.zip) {
              const ok = await this.fetchTax();
              if (!ok) {
                this.applyFallbackTax();
              }
            }
            await this.initSquare();
          }
        });
        this.$watch('form.shipping_method', () => {
          this.shippingCost = this.calcShipping();
          this.lockDisplayCurrency();
          if (this.step >= 2 && this.form.address1 && this.form.zip) {
            this.fetchTax().then((ok) => {
              if (!ok) {
                this.applyFallbackTax();
              }
            });
          }
        });
        this.$watch('cartItems', () => {
          this.shippingCost = this.calcShipping();
          this.lockDisplayCurrency();
        }, { deep: true });
        ['address1', 'city', 'state'].forEach((field) => {
          this.$watch(`form.${field}`, () => {
            this.lockDisplayCurrency();
          });
        });
        this.$watch('form.zip', () => {
          this.syncBillingZipFromShipping();
          this.lockDisplayCurrency();
          this.scheduleSquareRefresh();
        });
        this.$watch('form.country', () => {
          this.scheduleSquareRefresh();
        });
      },
      async fetchTax() {
        this.lockDisplayCurrency();
        this.taxLoading = true;
        this.taxError = '';
        try {
          const res = await fetch(checkoutCfg().urls?.taxEstimate || '', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
              first_name: this.form.first_name,
              last_name: this.form.last_name,
              email: this.form.email,
              address1: this.form.address1,
              address2: this.form.address2,
              city: this.form.city,
              state: this.form.state,
              zip: this.form.zip,
              country: this.form.country,
              shipping_method: this.form.shipping_method,
              line_quantities: this.lineQuantitiesPayload(),
              discount_code: this.discountCode,
            }),
          });
          const data = await res.json();
          if (data.success) {
            this.syncTotalsFromServer(data.totals || { tax: data.tax, shipping: this.calcShipping() });
            return true;
          }
          this.applyFallbackTax();
          this.taxError = data.message || checkoutI18n.err_tax_failed || '';
          return false;
        } catch (e) {
          this.applyFallbackTax();
          this.taxError = checkoutI18n.err_tax_failed || '';
          return false;
        } finally {
          this.taxLoading = false;
          this.lockDisplayCurrency();
        }
      },
      async initSquare() {
        try {
          if (window.location.hostname === '127.0.0.1') {
            window.location.replace(window.location.href.replace('127.0.0.1', 'localhost'));
            return;
          }

          if (!window.isSecureContext && window.location.protocol !== 'https:') {
            this.paymentError = checkoutI18n.err_secure_context || 'Square requires HTTPS.';
            return;
          }

          if (!window.Square) {
            this.paymentError = checkoutI18n.err_sdk_load || 'Payment SDK failed to load.';
            return;
          }

          this.applyClientConfig();
          const { appId, locationId } = this.squareCredentials();
          const locale = this.squareLocaleForCountry(this.form.country);

          if (!appId) {
            this.paymentError = checkoutI18n.err_square_app || 'Square Application ID is missing.';
            return;
          }

          if (!locationId) {
            this.paymentError = checkoutI18n.err_square_location || 'Square Location ID is missing.';
            return;
          }

          if (this.squareCard?.destroy) {
            try {
              await this.squareCard.destroy();
            } catch (destroyError) {
              console.warn('[Dainely] Square card destroy', destroyError);
            }
            this.squareCard = null;
            this.cardReady = false;
          }
          this.payments = window.Square.payments(appId, locationId, { locale });
          if (typeof this.payments.setLocale === 'function') {
            await this.payments.setLocale(locale);
          }

          const cardOptions = {
            style: {
              '.input-container': { borderColor: '#e2e8f0' },
              '.input-container.is-focus': { borderColor: '#1e3a8a' },
              input: { color: '#1e293b', fontSize: '14px' },
              'input::placeholder': { color: '#94a3b8' },
            },
          };
          const postalPrefill = this.squareCardPostalPrefill();
          if (postalPrefill) {
            cardOptions.postalCode = postalPrefill;
          }

          this.squareCard = await this.payments.card(cardOptions);
          await this.squareCard.attach('#card-container');
          this.cardReady = true;
          this.paymentError = '';
        } catch (e) {
          console.error('Square init error:', e);
          this.paymentError = (checkoutI18n.err_square_init || 'Could not load payment form') + ': ' + (e.message || 'Unknown error');
        }
      },
      isBlank(value) {
        return String(value ?? '').trim() === '';
      },
      isValidEmail(value) {
        const email = String(value ?? '').trim();
        return email !== '' && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
      },
      collectRequiredFieldErrors(stepFilter = null) {
        const errors = {};

        if (stepFilter === 1 || stepFilter === null) {
          if (this.isBlank(this.form.first_name)) errors.first_name = checkoutI18n.err_first_name;
          if (this.isBlank(this.form.last_name)) errors.last_name = checkoutI18n.err_last_name;
          if (!this.isValidEmail(this.form.email)) errors.email = checkoutI18n.err_email;
        }

        if (stepFilter === 2 || stepFilter === null) {
          if (this.isBlank(this.form.address1)) errors.address1 = checkoutI18n.err_address;
          if (this.isBlank(this.form.city)) errors.city = checkoutI18n.err_city;
          if (this.isBlank(this.form.zip)) {
            errors.zip = checkoutI18n.err_zip;
          } else if (!this.isValidPostalCode(this.form.country, this.form.zip)) {
            errors.zip = this.zipInvalidMessage(this.form.country);
          }
          if (this.isBlank(this.form.country)) errors.country = checkoutI18n.err_country;
        }

        return errors;
      },
      focusFirstInvalidField() {
        const order = ['first_name', 'last_name', 'email', 'address1', 'city', 'zip', 'country'];
        const first = order.find((field) => this.errors[field]);
        if (!first) return;
        document.getElementById(first)?.focus();
      },
      validateStep() {
        this.errors = this.collectRequiredFieldErrors(this.step);
        if (Object.keys(this.errors).length > 0) {
          this.focusFirstInvalidField();
        }
        return Object.keys(this.errors).length === 0;
      },
      validateAllRequiredFields() {
        this.errors = this.collectRequiredFieldErrors(null);
        if (Object.keys(this.errors).length === 0) {
          return true;
        }
        if (this.errors.first_name || this.errors.last_name || this.errors.email) {
          this.step = 1;
        } else {
          this.step = 2;
        }
        this.focusFirstInvalidField();
        return false;
      },
      async nextStep() {
        if (!this.validateStep()) return;
        if (this.step === 2) {
          const ok = await this.fetchTax();
          if (!ok) {
            this.applyFallbackTax();
          }
        }
        this.step++;
      },
      async applyDiscount() {
        if (!this.discountCode.trim()) return;
        try {
          const res = await fetch(checkoutCfg().urls?.discountValidate || '', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
              Accept: 'application/json',
            },
            body: JSON.stringify({ code: this.discountCode, subtotal_usd: this.subtotalUsd() }),
          });
          const data = await res.json();
          if (data.valid) {
            this.discountValid = true;
            this.discount = Number(data.discount || 0);
            this.discountCode = String(data.code || this.discountCode).trim();
            this.discountMessage = data.message || checkoutI18n.discount_applied;
            if (data.free_shipping) {
              this.shippingCost = 0;
            } else {
              this.shippingCost = this.calcShipping();
            }
            if (this.step >= 2 && this.form.address1 && this.form.city && this.form.zip && this.form.country) {
              await this.fetchTax();
            }
          } else {
            this.discountValid = false;
            this.discount = 0;
            this.discountMessage = data.message || checkoutI18n.invalid_discount;
            this.shippingCost = this.calcShipping();
          }
        } catch (e) {
          this.discountValid = false;
          this.discount = 0;
          this.discountMessage = checkoutI18n.err_discount_validate;
        }
      },
      async submitOrder() {
        if (!this.validateAllRequiredFields()) {
          return;
        }
        if (!this.validateBillingPostal()) {
          return;
        }
        if (!this.squareCard) {
          this.paymentError = checkoutI18n.err_payment_not_ready;
          return;
        }
        this.loading = true;
        this.paymentError = '';
        this.paymentSuccess = '';
        try {
          const verificationDetails = this.buildSquareVerificationDetails();
          const result = await this.squareCard.tokenize(verificationDetails);
          if (result.status !== 'OK') {
            const sdkMessage = result.errors?.[0]?.message || '';
            this.paymentError = sdkMessage || checkoutI18n.err_card_tokenize;
            this.loading = false;
            return;
          }
          const sourceId = result.token;
          const res = await fetch(checkoutCfg().urls?.process || '', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
              source_id: sourceId,
              first_name: this.form.first_name,
              last_name: this.form.last_name,
              email: this.form.email,
              phone: this.form.phone,
              address1: this.form.address1,
              address2: this.form.address2,
              city: this.form.city,
              state: this.form.state,
              zip: this.form.zip,
              country: this.form.country,
              shipping_method: this.form.shipping_method,
              line_quantities: this.lineQuantitiesPayload(),
              discount_code: this.discountCode,
              currency_code: this._lockedCurrency.code,
              amount_cents: Math.round(this.total() * 100),
            }),
          });
          const raw = await res.text();
          let data;
          try {
            data = JSON.parse(raw);
          } catch (parseError) {
            this.paymentError = checkoutI18n.err_server_response;
            return;
          }
          if (data.success) {
            this.paymentSuccess = checkoutCfg().paymentSuccessMessage || '';
            setTimeout(() => { window.location.href = data.redirect || '/'; }, 1500);
          } else {
            this.paymentError = data.message || checkoutI18n.err_payment_failed;
          }
        } catch (e) {
          this.paymentError = checkoutI18n.err_unexpected + ': ' + e.message;
        } finally {
          this.loading = false;
        }
      },
    };
  });
}
