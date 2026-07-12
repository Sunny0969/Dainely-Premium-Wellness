<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyCheckoutService
{
    protected string $domain;
    protected string $token;
    protected string $apiVersion;
    protected string $apiBase;

    public function __construct()
    {
        $this->domain     = config('shopify.storefront_domain', 'dmede-usa.myshopify.com');
        $this->token      = config('shopify.storefront_access_token', '');
        $this->apiVersion = config('shopify.api_version', '2024-01');
        $this->apiBase    = "https://{$this->domain}/api/{$this->apiVersion}/graphql.json";
    }

    /**
     * Phase 2 helper — returns Shopify Checkout web URL or throws.
     * Payment is handled entirely by Shopify on the returned URL.
     *
     * @param  array<int, array{variant_id?: string, quantity?: int}>  $cartItems
     */
    public function createCheckoutUrl(array $cartItems): string
    {
        $result = $this->createCheckout($cartItems);

        if (! ($result['success'] ?? false) || empty($result['web_url'])) {
            throw new \Exception($result['error'] ?? 'Unable to create checkout.');
        }

        return $result['web_url'];
    }

    /**
     * Create a Shopify native checkout from local cart items.
     * Uses Storefront Cart API (checkoutCreate is deprecated).
     * Shopify hosts payment (Shop Pay, cards, local methods, tax).
     *
     * @param array $items Local cart items
     * @param array|null $address Customer shipping address (optional)
     * @param string|null $email Customer email (optional)
     * @param string|null $discountCode Discount/Coupon code (optional)
     * @return array
     */
    public function createCheckout(array $items, ?array $address = null, ?string $email = null, ?string $discountCode = null): array
    {
        if (empty($this->token)) {
            Log::error('Shopify Checkout Service: Storefront API token is missing');
            return ['success' => false, 'error' => 'Storefront API access token is missing. Please configure it in .env.'];
        }

        $lines = [];
        foreach ($items as $item) {
            $variantId = $item['variant_id'] ?? null;
            if (!$variantId) {
                continue;
            }

            if (! str_starts_with((string) $variantId, 'gid://')) {
                $variantId = "gid://shopify/ProductVariant/{$variantId}";
            }

            $lines[] = [
                'merchandiseId' => $variantId,
                'quantity'      => (int) ($item['quantity'] ?? 1),
            ];
        }

        if ($lines === []) {
            return ['success' => false, 'error' => 'No valid product variants found in cart.'];
        }

        $input = [
            'lines' => $lines,
            'attributes' => [
                ['key' => 'checkout_source', 'value' => 'Laravel Client Side'],
                ['key' => 'app_locale', 'value' => app()->getLocale()],
                ['key' => 'return_url', 'value' => (string) config('shopify.checkout_return_url', config('app.url'))],
            ],
        ];

        if ($email) {
            $input['buyerIdentity'] = ['email' => $email];
        }

        if ($discountCode) {
            $input['discountCodes'] = [$discountCode];
        }

        $query = <<<'GRAPHQL'
mutation cartCreate($input: CartInput!) {
  cartCreate(input: $input) {
    cart {
      id
      checkoutUrl
    }
    userErrors {
      field
      message
    }
  }
}
GRAPHQL;

        try {
            $response = $this->graphql($query, ['input' => $input]);

            if ($response['failed']) {
                return ['success' => false, 'error' => $response['error']];
            }

            $result = $response['json'];
            $errors = $result['data']['cartCreate']['userErrors'] ?? [];
            if (! empty($errors)) {
                Log::warning('Shopify cartCreate returned user errors', ['errors' => $errors]);
                return [
                    'success' => false,
                    'error' => $errors[0]['message'] ?? 'Shopify checkout creation failed.',
                ];
            }

            $cart = $result['data']['cartCreate']['cart'] ?? null;
            if (! $cart || empty($cart['checkoutUrl'])) {
                // Fallback for older stores still supporting Checkout API
                return $this->createLegacyCheckout($items, $address, $email, $discountCode);
            }

            return [
                'success' => true,
                'checkout_id' => $cart['id'],
                'web_url' => $cart['checkoutUrl'],
            ];
        } catch (\Throwable $e) {
            Log::error('Error creating Shopify checkout', [
                'message' => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => 'An unexpected error occurred: ' . $e->getMessage()];
        }
    }

    /**
     * Legacy Checkout API fallback (deprecated by Shopify).
     */
    protected function createLegacyCheckout(array $items, ?array $address, ?string $email, ?string $discountCode): array
    {
        $lineItems = [];
        foreach ($items as $item) {
            $variantId = $item['variant_id'] ?? null;
            if (! $variantId) {
                continue;
            }
            if (! str_starts_with((string) $variantId, 'gid://')) {
                $variantId = "gid://shopify/ProductVariant/{$variantId}";
            }
            $lineItems[] = [
                'variantId' => $variantId,
                'quantity'  => (int) ($item['quantity'] ?? 1),
            ];
        }

        $input = [
            'lineItems' => $lineItems,
            'customAttributes' => [
                ['key' => 'checkout_source', 'value' => 'Laravel Client Side'],
                ['key' => 'app_locale', 'value' => app()->getLocale()],
            ],
        ];

        if ($email) {
            $input['email'] = $email;
        }

        if ($address) {
            $input['shippingAddress'] = [
                'firstName' => $address['first_name'] ?? '',
                'lastName'  => $address['last_name'] ?? '',
                'address1'  => $address['address1'] ?? '',
                'address2'  => $address['address2'] ?? '',
                'city'      => $address['city'] ?? '',
                'province'  => $address['state'] ?? '',
                'zip'       => $address['zip'] ?? '',
                'country'   => $address['country'] ?? '',
            ];
        }

        $query = <<<'GRAPHQL'
mutation checkoutCreate($input: CheckoutCreateInput!) {
  checkoutCreate(input: $input) {
    checkout { id webUrl }
    checkoutUserErrors { code field message }
  }
}
GRAPHQL;

        $response = $this->graphql($query, ['input' => $input]);
        if ($response['failed']) {
            return ['success' => false, 'error' => $response['error']];
        }

        $result = $response['json'];
        $errors = $result['data']['checkoutCreate']['checkoutUserErrors'] ?? [];
        if (! empty($errors)) {
            return ['success' => false, 'error' => $errors[0]['message'] ?? 'Checkout failed'];
        }

        $checkout = $result['data']['checkoutCreate']['checkout'] ?? null;
        if (! $checkout || empty($checkout['webUrl'])) {
            Log::error('Shopify checkout creation payload missing checkout/webUrl', ['result' => $result]);
            return ['success' => false, 'error' => 'Invalid response from Shopify checkout.'];
        }

        $webUrl = $checkout['webUrl'];
        if ($discountCode) {
            $apply = $this->applyDiscountCode($checkout['id'], $discountCode);
            if ($apply['success']) {
                $webUrl = $apply['webUrl'];
            }
        }

        return [
            'success' => true,
            'checkout_id' => $checkout['id'],
            'web_url' => $webUrl,
        ];
    }

    protected function applyDiscountCode(string $checkoutId, string $discountCode): array
    {
        $query = <<<'GRAPHQL'
mutation checkoutDiscountCodeApplyV2($checkoutId: ID!, $discountCode: String!) {
  checkoutDiscountCodeApplyV2(checkoutId: $checkoutId, discountCode: $discountCode) {
    checkout { id webUrl }
    checkoutUserErrors { code field message }
  }
}
GRAPHQL;

        try {
            $response = $this->graphql($query, [
                'checkoutId' => $checkoutId,
                'discountCode' => $discountCode,
            ]);

            if ($response['failed']) {
                return ['success' => false];
            }

            $result = $response['json'];
            $errors = $result['data']['checkoutDiscountCodeApplyV2']['checkoutUserErrors'] ?? [];
            if (! empty($errors)) {
                return ['success' => false];
            }

            $checkout = $result['data']['checkoutDiscountCodeApplyV2']['checkout'] ?? null;
            if ($checkout && ! empty($checkout['webUrl'])) {
                return ['success' => true, 'webUrl' => $checkout['webUrl']];
            }
        } catch (\Throwable $e) {
            Log::error('Error applying discount code to Shopify checkout', ['error' => $e->getMessage()]);
        }

        return ['success' => false];
    }

    protected function graphql(string $query, array $variables = []): array
    {
        $response = Http::withOptions([
            'verify' => (bool) config('shopify.verify_ssl', true),
        ])->withHeaders([
            'X-Shopify-Storefront-Access-Token' => $this->token,
            'Content-Type' => 'application/json',
        ])->post($this->apiBase, [
            'query' => $query,
            'variables' => $variables,
        ]);

        if ($response->failed()) {
            Log::error('Shopify Storefront API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'failed' => true,
                'error' => 'Failed to connect to Shopify. Code: ' . $response->status(),
                'json' => [],
            ];
        }

        return [
            'failed' => false,
            'error' => null,
            'json' => $response->json() ?? [],
        ];
    }
}
