<?php

namespace App\Models\Supabase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasLocalizedContent;

class ProductContent extends Model
{
    use HasLocalizedContent;

    protected $connection = 'supabase';

    protected $table = 'product_content';

    protected $fillable = [
        'product_id',
        'locale',
        'overview',
        'benefits',
        'how_it_works',
        'who_is_it_for',
        'specifications',
        'care',
        'seo_title',
        'seo_description',
        'canonical_url',
    ];

    /**
     * Get the product that owns this content.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
