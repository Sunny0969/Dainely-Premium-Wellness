<?php

namespace App\Models\Supabase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecommendationRule extends Model
{
    protected $connection = 'supabase';

    protected $table = 'recommendation_rules';

    protected $fillable = [
        'name',
        'trigger_type',
        'trigger_value',
        'recommended_product_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the recommended product.
     */
    public function recommendedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'recommended_product_id');
    }
}
