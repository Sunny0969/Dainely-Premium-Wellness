<?php

namespace App\Models\Supabase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Faq extends Model
{
    protected $connection = 'supabase';

    protected $table = 'faqs';

    protected $fillable = [
        'faqable_type',
        'faqable_id',
        'category',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the owning faqable model (polymorphic relationship).
     */
    public function faqable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the translations for the FAQ.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(FaqTranslation::class, 'faq_id');
    }

    /**
     * Get the translation for a specific locale.
     */
    public function translation(string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', 'en');
    }
}
