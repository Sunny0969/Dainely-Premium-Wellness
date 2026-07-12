<?php

namespace App\Models\Supabase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $connection = 'supabase';

    protected $fillable = [
        'shopify_product_id', 'variant_id', 'sku', 'handle',
        'title', 'status', 'price', 'compare_at_price',
        'inventory', 'featured_image', 'synced_at',
    ];

    protected $casts = [
        'price'            => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'synced_at'        => 'datetime',
    ];

    public function productContents(): HasMany
    {
        return $this->hasMany(ProductContent::class);
    }
}
