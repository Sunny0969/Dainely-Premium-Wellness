<?php

namespace App\Models;

use App\Models\Supabase\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductContent extends Model
{
    protected $connection = 'supabase';

    protected $table = 'product_content';

    protected $fillable = [
        'product_id', 'locale', 'overview', 'benefits', 'how_it_works',
        'who_is_it_for', 'specifications', 'care', 'seo_title',
        'seo_description', 'canonical_url',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
