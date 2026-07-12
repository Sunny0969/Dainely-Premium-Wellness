<?php

namespace App\Models\Supabase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PageBlock extends Model
{
    protected $connection = 'supabase';

    protected $table = 'page_blocks';

    protected $fillable = [
        'blockable_type',
        'blockable_id',
        'type',
        'content',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'content'   => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the owning blockable model.
     */
    public function blockable(): MorphTo
    {
        return $this->morphTo();
    }
}
