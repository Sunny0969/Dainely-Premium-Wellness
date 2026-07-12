<?php

namespace App\Models\Supabase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasLocalizedContent;

class ProductKnowledgeSignal extends Model
{
    use HasLocalizedContent;

    protected $connection = 'supabase';

    protected $table = 'product_knowledge_signals';

    protected $fillable = [
        'product_id',
        'locale',
        'question',
        'answer',
        'is_approved',
        'source',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    /**
     * Scope a query to only include approved signals.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Get the product that owns the knowledge signal.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
