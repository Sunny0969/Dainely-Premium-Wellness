<?php

namespace App\Models\Supabase;

use App\Traits\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
        'body_plain',
        'keywords',
        'tsv',
    ];

    public function searchable(): MorphTo
    {
        return $this->morphTo();
    }
}
