<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasLocalizedContent
{
    /**
     * Scope a query to only include records of a given locale.
     * Alias: forLocale() (Phase 2 doc naming).
     */
    public function scopeLocalized(Builder $query, ?string $locale = null): Builder
    {
        $locale = $locale ?? app()->getLocale();

        return $query->where('locale', $locale);
    }

    /**
     * Phase 2 doc name — same as localized().
     */
    public function scopeForLocale(Builder $query, ?string $locale = null): Builder
    {
        return $this->scopeLocalized($query, $locale);
    }
}
