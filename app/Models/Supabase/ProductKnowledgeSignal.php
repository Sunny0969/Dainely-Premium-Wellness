<?php

namespace App\Models\Supabase;

use App\Traits\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductKnowledgeSignal extends Model
{
    use HasLocalizedContent;

    protected $connection = 'supabase';

    protected $table = 'product_knowledge_signals';

    protected $fillable = [
        'product_id',
        'locale',
        'speaker_type',
        'question',
        'answer',
        'keywords',
        'source',
        'confidence',
        'approved',
        'embedding_id',
    ];

    protected $casts = [
        'keywords'   => 'array',
        'confidence' => 'float',
        'approved'   => 'boolean',
    ];

    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
