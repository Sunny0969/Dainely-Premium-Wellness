<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasLocalizedContent
{
    /**
     * Scope a query to only include records of a given locale.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|null  $locale
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLocalized(Builder $query, ?string $locale = null): Builder
    {
        $locale = $locale ?? app()->getLocale();

        return $query->where('locale', $locale);
    }
}
