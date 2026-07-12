<?php

namespace App\Models\Supabase;

use App\Traits\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBundle extends Model
{
    use HasLocalizedContent;

    protected $connection = 'supabase';

    protected $table = 'product_bundles';

    protected $fillable = [
        'bundle_shopify_product_id',
        'locale',
        'title',
        'description',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ProductBundleItem::class, 'bundle_id');
    }
}
