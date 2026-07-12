<?php

namespace App\Models\Supabase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RelatedContent extends Model
{
    protected $connection = 'supabase';

    protected $table = 'related_content';

    protected $fillable = [
        'source_type',
        'source_id',
        'related_type',
        'related_id',
        'relation_type',
        'sort_order',
    ];

    /**
     * Get the source model.
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the related model.
     */
    public function related(): MorphTo
    {
        return $this->morphTo();
    }
}
