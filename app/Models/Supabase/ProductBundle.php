<?php

namespace App\Models\Supabase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBundle extends Model
{
    protected $connection = 'supabase';

    protected $table = 'product_bundles';

    protected $fillable = [
        'shopify_product_id',
        'handle',
        'price_usd',
        'is_active',
    ];

    protected $casts = [
        'price_usd' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the items in this bundle.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ProductBundleItem::class, 'bundle_id');
    }
}
