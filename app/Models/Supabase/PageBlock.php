<?php

namespace App\Models\Supabase;

use App\Traits\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PageBlock extends Model
{
    use HasLocalizedContent;

    protected $connection = 'supabase';

    protected $table = 'page_blocks';

    protected $fillable = [
        'blockable_type',
        'blockable_id',
        'locale',
        'block_type',
        'title',
        'content',
        'sort_order',
        'visible',
    ];

    protected $casts = [
        'visible' => 'boolean',
    ];

    public function blockable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeVisible($query)
    {
        return $query->where('visible', true);
    }
}
