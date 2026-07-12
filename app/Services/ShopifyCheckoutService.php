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
     * Create a Shopify native checkout from local cart items.
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

        $lineItems = [];
        foreach ($items as $item) {
            $variantId = $item['variant_id'] ?? null;
            if (!$variantId) {
                continue;
            }

            // Standardize variant ID to Shopify GID format
            if (!str_starts_with((string)$variantId, 'gid://')) {
                $variantId = "gid://shopify/ProductVariant/{$variantId}";
            }

            $lineItems[] = [
                'variantId' => $variantId,
                'quantity'  => (int)($item['quantity'] ?? 1),
            ];
        }

        if (empty($lineItems)) {
            return ['success' => false, 'error' => 'No valid product variants found in cart.'];
        }

        $input = [
            'lineItems' => $lineItems,
            'customAttributes' => [
                ['key' => 'checkout_source', 'value' => 'Laravel Client Side'],
                ['key' => 'app_locale', 'value' => app()->getLocale()],
            ]
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

        if ($discountCode) {
            // Storefront API applies discount via checkoutDiscountCodeApplyV2 after creation
            Log::info("Discount code supplied: {$discountCode}. Will apply after checkout creation.");
        }

        $query = <<<'GRAPHQL'
mutation checkoutCreate($input: CheckoutCreateInput!) {
  checkoutCreate(input: $input) {
    checkout {
      id
      webUrl
    }
    checkoutUserErrors {
      code
      field
      message
    }
  }
}
GRAPHQL;

        try {
            $response = Http::withHeaders([
                'X-Shopify-Storefront-Access-Token' => $this->token,
                'Content-Type' => 'application/json',
            ])
            ->post($this->apiBase, [
                'query' => $query,
                'variables' => [
                    'input' => $input,
                ],
            ]);

            if ($response->failed()) {
                Log::error('Shopify Storefront API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return ['success' => false, 'error' => 'Failed to connect to Shopify. Code: ' . $response->status()];
            }

            $result = $response->json();
            $errors = $result['data']['checkoutCreate']['checkoutUserErrors'] ?? [];

            if (!empty($errors)) {
                Log::warning('Shopify checkout creation returned user errors', ['errors' => $errors]);
                return [
                    'success' => false,
                    'error' => $errors[0]['message'] ?? 'Shopify checkout creation failed.',
                ];
            }

            $checkout = $result['data']['checkoutCreate']['checkout'] ?? null;
            if (!$checkout || empty($checkout['webUrl'])) {
                Log::error('Shopify checkout creation payload missing checkout/webUrl', ['result' => $result]);
                return ['success' => false, 'error' => 'Invalid response from Shopify checkout.'];
            }

            $checkoutId = $checkout['id'];
            $webUrl = $checkout['webUrl'];

            // If discount code exists, apply it
            if ($discountCode) {
                $applyResult = $this->applyDiscountCode($checkoutId, $discountCode);
                if ($applyResult['success']) {
                    $webUrl = $applyResult['webUrl'];
                }
            }

            return [
                'success' => true,
                'checkout_id' => $checkoutId,
                'web_url' => $webUrl,
            ];

        } catch (\Throwable $e) {
            Log::error('Error creating Shopify checkout', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['success' => false, 'error' => 'An unexpected error occurred: ' . $e->getMessage()];
        }
    }

    /**
     * Apply a discount code to an existing checkout.
     *
     * @param string $checkoutId
     * @param string $discountCode
     * @return array
     */
    protected function applyDiscountCode(string $checkoutId, string $discountCode): array
    {
        $query = <<<'GRAPHQL'
mutation checkoutDiscountCodeApplyV2($checkoutId: ID!, $discountCode: String!) {
  checkoutDiscountCodeApplyV2(checkoutId: $checkoutId, discountCode: $discountCode) {
    checkout {
      id
      webUrl
    }
    checkoutUserErrors {
      code
      field
      message
    }
  }
}
GRAPHQL;

        try {
            $response = Http::withHeaders([
                'X-Shopify-Storefront-Access-Token' => $this->token,
                'Content-Type' => 'application/json',
            ])
            ->post($this->apiBase, [
                'query' => $query,
                'variables' => [
                    'checkoutId' => $checkoutId,
                    'discountCode' => $discountCode,
                ],
            ]);

            if ($response->failed()) {
                return ['success' => false];
            }

            $result = $response->json();
            $errors = $result['data']['checkoutDiscountCodeApplyV2']['checkoutUserErrors'] ?? [];

            if (!empty($errors)) {
                Log::warning('Failed to apply discount to Shopify checkout', ['errors' => $errors]);
                return ['success' => false];
            }

            $checkout = $result['data']['checkoutDiscountCodeApplyV2']['checkout'] ?? null;
            if ($checkout && !empty($checkout['webUrl'])) {
                return [
                    'success' => true,
                    'webUrl' => $checkout['webUrl'],
                ];
            }
        } catch (\Throwable $e) {
            Log::error('Error applying discount code to Shopify checkout', ['error' => $e->getMessage()]);
        }

        return ['success' => false];
    }
}
