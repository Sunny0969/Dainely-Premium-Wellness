<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTranslation extends Model
{
    protected $fillable = [
        'product_id', 'locale', 'name', 'slug', 'short_description',
        'description', 'benefits', 'meta_title', 'meta_description', 'faq_items',
    ];

    protected $casts = [
        'faq_items' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
