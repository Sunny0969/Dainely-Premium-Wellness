<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ShopifyTaxService
{
    public function __construct(protected ShopifyService $shopify) {}

    /**
     * Estimate sales tax via Shopify draftOrderCalculate (Shopify Tax engine).
     *
     * @param  list<array<string, mixed>>  $items
     * @param  array<string, mixed>  $address
     * @return array{
     *   success: bool,
     *   tax_usd: float,
     *   tax_lines: list<array{title: string, rate: float, price_usd: float}>,
     *   cached: bool,
     *   error: ?string
     * }
     */
    public function estimate(
        array $items,
        array $address,
        string $shippingMethod = 'standard',
        float $discountUsd = 0,
    ): array {
        if (! config('shopify.tax_enabled', true)) {
            return $this->emptyEstimate();
        }

        if ($items === [] || empty($address['country'])) {
            return $this->emptyEstimate(false, 'Address and cart items are required for tax estimation.');
        }

        $subtotalUsd = 0.0;
        foreach ($items as $item) {
            $subtotalUsd += (float) ($item['price'] ?? 0) * max(1, (int) ($item['quantity'] ?? 1));
        }
        $shippingUsd = $this->shopify->estimateShippingUsd($subtotalUsd, $shippingMethod);

        $cacheKey = 'shopify_tax_' . md5(json_encode([
            $items,
            $address,
            $shippingMethod,
            $discountUsd,
            $shippingUsd,
        ]));

        $cached = Cache::get($cacheKey);
        if (is_array($cached) && ($cached['success'] ?? false)) {
            $cached['cached'] = true;

            return $cached;
        }

        // Session layer — same shopper refreshing checkout / tweaking fields.
        try {
            $sessionBag = 'checkout.tax.'.md5(json_encode([
                $address['country'] ?? '',
                $address['zip'] ?? '',
                $address['state'] ?? '',
                $shippingMethod,
                round($discountUsd, 2),
                round($subtotalUsd, 2),
            ]));
            $sessionCached = session($sessionBag);
            if (is_array($sessionCached) && ($sessionCached['success'] ?? false)) {
                $sessionCached['cached'] = true;
                Cache::put($cacheKey, $sessionCached, now()->addSeconds((int) config('shopify.tax_cache_ttl', 1800)));

                return $sessionCached;
            }
        } catch (\Throwable) {
            // ignore
        }

        $lineItems = $this->buildDraftLineItems($items);
        if ($lineItems === []) {
            return $this->emptyEstimate(false, 'No billable line items for tax estimation.');
        }

        $input = [
            'lineItems' => $lineItems,
            'shippingAddress' => [
                'firstName' => (string) ($address['first_name'] ?? ''),
                'lastName'  => (string) ($address['last_name'] ?? ''),
                'address1'  => (string) ($address['address1'] ?? ''),
                'address2'  => (string) ($address['address2'] ?? ''),
                'city'      => (string) ($address['city'] ?? ''),
                'province'  => (string) ($address['state'] ?? ''),
                'country'   => strtoupper((string) ($address['country'] ?? 'US')),
                'zip'       => (string) ($address['zip'] ?? ''),
            ],
        ];

        if ($shippingUsd > 0) {
            $input['shippingLine'] = [
                'title' => $shippingMethod === 'express' ? 'Express Shipping' : 'Standard Shipping',
                'priceWithCurrency' => [
                    'amount'       => number_format($shippingUsd, 2, '.', ''),
                    'currencyCode' => config('shopify.shop_currency', 'USD'),
                ],
            ];
        }

        if (! empty($address['email'])) {
            $input['email'] = (string) $address['email'];
        }

        if ($discountUsd > 0) {
            $input['appliedDiscount'] = [
                'description' => 'Checkout discount',
                'title'       => 'Discount',
                'value'       => round($discountUsd, 2),
                'valueType'   => 'FIXED_AMOUNT',
            ];
        }

        $mutation = <<<'GQL'
mutation draftOrderCalculate($input: DraftOrderInput!) {
  draftOrderCalculate(input: $input) {
    calculatedDraftOrder {
      totalTaxSet {
        shopMoney { amount currencyCode }
      }
      taxLines {
        title
        rate
        ratePercentage
        priceSet {
          shopMoney { amount currencyCode }
        }
      }
    }
    userErrors {
      field
      message
    }
  }
}
GQL;

        $response = $this->shopify->graphql($mutation, ['input' => $input]);

        if (! ($response['success'] ?? false)) {
            Log::warning('Shopify tax estimate failed', ['error' => $response['error'] ?? 'unknown']);

            return $this->handleTaxFailure($response['error'] ?? 'Tax estimation unavailable.', $items, $address, $shippingMethod);
        }

        $calc   = $response['data']['draftOrderCalculate'] ?? [];
        $errors = $calc['userErrors'] ?? [];
        if ($errors !== []) {
            $message = collect($errors)->pluck('message')->implode('; ');
            Log::warning('Shopify tax estimate userErrors', ['errors' => $errors]);

            return $this->handleTaxFailure($message, $items, $address, $shippingMethod);
        }

        $draft = $calc['calculatedDraftOrder'] ?? [];
        $taxUsd = (float) ($draft['totalTaxSet']['shopMoney']['amount'] ?? 0);
        $taxLines = [];

        foreach ($draft['taxLines'] ?? [] as $line) {
            $price = (float) ($line['priceSet']['shopMoney']['amount'] ?? 0);
            $rate  = (float) ($line['rate'] ?? ($line['ratePercentage'] ?? 0) / 100);

            $taxLines[] = [
                'title'     => (string) ($line['title'] ?? 'Tax'),
                'rate'      => $rate,
                'price_usd' => round($price, 2),
            ];
        }

        $result = [
            'success'   => true,
            'tax_usd'   => round($taxUsd, 2),
            'tax_lines' => $taxLines,
            'cached'    => false,
            'error'     => null,
        ];

        Cache::put($cacheKey, $result, now()->addSeconds((int) config('shopify.tax_cache_ttl', 1800)));

        // Also keep a short session copy so refresh / input tweaks reuse the same quote.
        try {
            $sessionBag = 'checkout.tax.'.md5(json_encode([
                $address['country'] ?? '',
                $address['zip'] ?? '',
                $address['state'] ?? '',
                $shippingMethod,
                round($discountUsd, 2),
                round($subtotalUsd, 2),
            ]));
            session([$sessionBag => $result]);
        } catch (\Throwable) {
            // session may be unavailable in some contexts
        }

        return $result;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    protected function buildDraftLineItems(array $items): array
    {
        $lineItems = [];
        $currency  = config('shopify.shop_currency', 'USD');

        foreach ($items as $item) {
            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $variantId = $this->shopify->resolveCartVariantId($item);

            if ($variantId !== null) {
                $lineItems[] = [
                    'variantId' => 'gid://shopify/ProductVariant/' . $variantId,
                    'quantity'  => $qty,
                ];
                continue;
            }

            $unitPrice = (float) ($item['price'] ?? 0);
            $lineItems[] = [
                'title'    => (string) ($item['title'] ?? 'Product'),
                'quantity' => $qty,
                'originalUnitPriceWithCurrency' => [
                    'amount'       => number_format($unitPrice, 2, '.', ''),
                    'currencyCode' => $currency,
                ],
                'requiresShipping' => true,
                'taxable'          => true,
            ];
        }

        return $lineItems;
    }

    /**
     * Estimate tax for checkout summary before the customer enters an address.
     * Uses locale default country (US / FR / DE) and falls back to configured rates when needed.
     *
     * @param  list<array<string, mixed>>  $items
     */
    public function estimateInitialUsd(array $items, string $locale, string $shippingMethod = 'standard'): float
    {
        if ($items === []) {
            return 0.0;
        }

        $country = match ($locale) {
            'fr' => 'FR',
            'de' => 'DE',
            default => 'US',
        };

        $result = $this->estimate($items, [
            'country'  => $country,
            'state'    => '',
            'address1' => 'Checkout',
            'city'     => 'Checkout',
            'zip'      => '00000',
        ], $shippingMethod, 0);

        return ($result['success'] ?? false) ? (float) ($result['tax_usd'] ?? 0) : 0.0;
    }

    /**
     * @return array{success: bool, tax_usd: float, tax_lines: list, cached: bool, error: ?string}
     */
    protected function handleTaxFailure(
        string $error,
        array $items,
        array $address,
        string $shippingMethod,
    ): array {
        if ($this->shouldUseFallback($error)) {
            Log::info('Using configured tax fallback rates (enable write_draft_orders on Shopify app for production)');

            return $this->fallbackTaxEstimate($items, $address);
        }

        return $this->emptyEstimate(false, $this->formatScopeError($error));
    }

    protected function shouldUseFallback(string $error): bool
    {
        if (app()->environment('local')) {
            return true;
        }

        if (config('shopify.tax_fallback', false)) {
            return true;
        }

        return $this->isScopeError($error);
    }

    protected function isScopeError(string $error): bool
    {
        return stripos($error, 'draft_order') !== false
            || stripos($error, 'draftOrderCalculate') !== false;
    }

    protected function formatScopeError(string $error): string
    {
        if (stripos($error, 'draft_order') !== false || stripos($error, 'draftOrderCalculate') !== false) {
            return 'Shopify Tax requires the write_draft_orders API scope on your DMEDE custom app. '
                . 'Enable it in Shopify Admin → Settings → Apps → DMEDE StoreAdminApp → Configure.';
        }

        return $error;
    }

    /**
     * Development fallback when Shopify Tax API scope is not yet approved.
     *
     * @param  list<array<string, mixed>>  $items
     * @param  array<string, mixed>  $address
     */
    protected function fallbackTaxEstimate(array $items, array $address): array
    {
        $subtotalUsd = 0.0;
        foreach ($items as $item) {
            $subtotalUsd += (float) ($item['price'] ?? 0) * max(1, (int) ($item['quantity'] ?? 1));
        }

        $country = strtoupper((string) ($address['country'] ?? 'US'));
        if ($country === 'UK') {
            $country = 'GB';
        }
        $state   = strtoupper((string) ($address['state'] ?? ''));
        $rates   = config('shopify_tax_fallback.' . $country, config('shopify_tax_fallback.US'));
        $rate    = (float) ($rates['states'][$state] ?? $rates['default'] ?? 0);

        $taxUsd = round($subtotalUsd * $rate, 2);
        $title  = in_array($country, ['FR', 'DE', 'GB'], true) ? 'VAT' : 'Sales Tax';

        return [
            'success'   => true,
            'tax_usd'   => $taxUsd,
            'tax_lines' => $taxUsd > 0 ? [[
                'title'     => $title . ' (estimated)',
                'rate'      => $rate,
                'price_usd' => $taxUsd,
            ]] : [],
            'cached'    => false,
            'error'     => null,
        ];
    }

    /**
     * @return array{success: bool, tax_usd: float, tax_lines: list, cached: bool, error: ?string}
     */
    protected function emptyEstimate(bool $success = true, ?string $error = null): array
    {
        return [
            'success'   => $success,
            'tax_usd'   => 0.0,
            'tax_lines' => [],
            'cached'    => false,
            'error'     => $error,
        ];
    }
}
