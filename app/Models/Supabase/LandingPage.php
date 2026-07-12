<?php

namespace App\Models\Supabase;

use App\Traits\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
        'canonical_url',
        'published',
    ];

    protected $casts = [
        'published' => 'boolean',
    ];

    public function pageBlocks(): MorphMany
    {
        return $this->morphMany(PageBlock::class, 'blockable')->orderBy('sort_order');
    }

    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable')->orderBy('sort_order');
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }
}
