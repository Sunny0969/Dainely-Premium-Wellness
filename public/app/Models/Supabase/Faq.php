<?php

namespace App\Models\Supabase;

use App\Traits\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Faq extends Model
{
    use HasLocalizedContent;

    protected $connection = 'supabase';

    protected $table = 'faqs';

    protected $fillable = [
        'faqable_type',
        'faqable_id',
        'locale',
        'question',
        'answer',
        'sort_order',
        'approved',
    ];

    protected $casts = [
        'approved' => 'boolean',
    ];

    public function faqable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }
}
