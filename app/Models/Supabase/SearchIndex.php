<?php

namespace App\Models\Supabase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Traits\HasLocalizedContent;

class SearchIndex extends Model
{
    use HasLocalizedContent;

    protected $connection = 'supabase';

    protected $table = 'search_index';

    protected $fillable = [
        'searchable_type',
        'searchable_id',
        'locale',
        'title',
        'content',
        'search_vector',
    ];

    /**
     * Get the owning searchable model.
     */
    public function searchable(): MorphTo
    {
        return $this->morphTo();
    }
}
