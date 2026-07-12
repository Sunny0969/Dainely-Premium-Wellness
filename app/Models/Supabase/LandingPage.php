<?php

namespace App\Models\Supabase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Traits\HasLocalizedContent;

class LandingPage extends Model
{
    use HasLocalizedContent;

    protected $connection = 'supabase';

    protected $table = 'landing_pages';

    protected $fillable = [
        'slug',
        'locale',
        'title',
        'meta_title',
        'meta_description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all of the page blocks for the landing page.
     */
    public function pageBlocks(): MorphMany
    {
        return $this->morphMany(PageBlock::class, 'blockable')->orderBy('sort_order');
    }

    /**
     * Get all of the page's FAQs.
     */
    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable')->orderBy('sort_order');
    }
}
