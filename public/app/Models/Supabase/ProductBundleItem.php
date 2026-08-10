<?php

namespace App\Models\Supabase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBundleItem extends Model
{
    protected $connection = 'supabase';

    protected $table = 'product_bundle_items';

    protected $fillable = [
        'bundle_id',
        'product_id',
        'quantity',
    ];

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(ProductBundle::class, 'bundle_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
