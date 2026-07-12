<?php

namespace App\Models\Supabase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasLocalizedContent;

class FaqTranslation extends Model
{
    use HasLocalizedContent;

    protected $connection = 'supabase';

    protected $table = 'faq_translations';

    protected $fillable = [
        'faq_id',
        'locale',
        'question',
        'answer',
    ];

    /**
     * Get the FAQ that owns this translation.
     */
    public function faq(): BelongsTo
    {
        return $this->belongsTo(Faq::class, 'faq_id');
    }
}
