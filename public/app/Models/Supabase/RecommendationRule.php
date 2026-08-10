<?php

namespace App\Models\Supabase;

use Illuminate\Database\Eloquent\Model;

class RecommendationRule extends Model
{
    protected $connection = 'supabase';

    protected $table = 'recommendation_rules';

    protected $fillable = [
        'rule_type',
        'source_item_type',
        'source_item_id',
        'recommended_item_type',
        'recommended_item_id',
        'score',
    ];

    protected $casts = [
        'score' => 'float',
    ];

    /**
     * Get the source item that triggers this rule.
     */
    public function sourceItem(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the recommended item.
     */
    public function recommendedItem(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }
}
