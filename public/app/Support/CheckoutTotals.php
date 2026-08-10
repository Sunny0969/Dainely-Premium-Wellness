<?php



namespace App\Support;



use App\Services\CurrencyService;

use App\Services\ShopifyService;



class CheckoutTotals

{

    public function __construct(

        protected CurrencyService $currency,

        protected ShopifyService $shopify,

    ) {}



    /**

     * @param  list<array<string, mixed>>  $items

     * @return array{

     *   subtotal_usd: float,

     *   shipping_usd: float,

     *   tax_usd: float,

     *   discount_usd: float,

     *   total_usd: float,

     *   subtotal: float,

     *   shipping: float,

     *   tax: float,

     *   total: float,

     *   currency_code: string,

     *   currency_symbol: string,

     *   amount_cents: int,

     *   free_shipping_threshold: float,

     *   item_count: int

     * }

     */

    public function calculate(

        array $items,

        string $shippingMethod,

        string $locale,

        float $discountUsd = 0,

        float $taxUsd = 0,

        bool $forceFreeShipping = false,

    ): array {

        $subtotalUsd = 0.0;

        $itemCount   = 0;



        foreach ($items as $item) {

            $qty = max(1, (int) ($item['quantity'] ?? 1));

            $subtotalUsd += (float) ($item['price'] ?? 0) * $qty;

            $itemCount += $qty;

        }



        $subtotalUsd = round($subtotalUsd, 2);

        $shippingUsd = $forceFreeShipping
            ? 0.0
            : $this->shopify->estimateShippingUsd($subtotalUsd, $shippingMethod);

        $taxUsd      = round(max(0, $taxUsd), 2);

        $discountUsd = round(max(0, $discountUsd), 2);

        $totalUsd    = round(max(0, $subtotalUsd + $shippingUsd + $taxUsd - $discountUsd), 2);



        $currencyCode = $this->resolveDisplayCurrency($locale);
        $meta         = $this->currency->getCurrencyMeta($currencyCode);
        $exchangeRate = $this->currency->getUsdToCurrencyRate($currencyCode);
        $squareCurrency = strtoupper((string) config('square.charge_currency', 'USD'));

        $subtotal = $this->currency->convert($subtotalUsd, $currencyCode);
        $shipping = $this->currency->convert($shippingUsd, $currencyCode);
        $tax      = $this->currency->convert($taxUsd, $currencyCode);
        $discount = $this->currency->convert($discountUsd, $currencyCode);
        $total    = $this->currency->convert($totalUsd, $currencyCode);
        $freeShip = $this->currency->convert($this->currency->freeShippingThresholdUsd(), $currencyCode);

        return [
            'subtotal_usd'            => $subtotalUsd,
            'shipping_usd'            => $shippingUsd,
            'tax_usd'                 => $taxUsd,
            'discount_usd'            => $discountUsd,
            'total_usd'               => $totalUsd,
            'subtotal'                => $subtotal,
            'shipping'                => $shipping,
            'tax'                     => $tax,
            'discount'                => $discount,
            'total'                   => $total,
            'currency_code'           => $currencyCode,
            'currency_symbol'         => $meta['symbol'],
            'amount_cents'            => (int) round($total * 100),
            'square_currency_code'    => $squareCurrency,
            'square_amount_cents'     => (int) round($totalUsd * 100),
            'exchange_rate'           => $exchangeRate,
            'free_shipping_threshold' => $freeShip,
            'item_count'              => $itemCount,
        ];

    }



    /**

     * @param  list<array<string, mixed>>  $items

     * @return list<array<string, mixed>>

     */

    public function itemsForDisplay(array $items, string $locale): array

    {

        $currencyCode = $this->resolveDisplayCurrency($locale);

        $meta         = $this->currency->getCurrencyMeta($currencyCode);



        return array_values(array_map(function (array $item) use ($currencyCode, $meta) {

            $qty       = max(1, (int) ($item['quantity'] ?? 1));

            $unitUsd   = (float) ($item['price'] ?? 0);

            $lineUsd   = round($unitUsd * $qty, 2);

            $unitLocal = $this->currency->convert($unitUsd, $currencyCode);

            $lineLocal = $this->currency->convert($lineUsd, $currencyCode);



            return array_merge($item, [

                'quantity'       => $qty,

                'unit_price_usd' => $unitUsd,

                'line_total_usd' => $lineUsd,

                'unit_price'     => $unitLocal,

                'line_total'     => $lineLocal,

                'currency_code'  => $currencyCode,

                'currency_symbol'=> $meta['symbol'],

            ]);

        }, $items));

    }



    public function resolveDisplayCurrency(string $locale): string
    {
        return $this->currency->resolveDisplayCurrency($locale);
    }

    /** @deprecated Use resolveDisplayCurrency() */
    public function resolveChargeCurrency(string $locale): string
    {
        return $this->resolveDisplayCurrency($locale);
    }
}


