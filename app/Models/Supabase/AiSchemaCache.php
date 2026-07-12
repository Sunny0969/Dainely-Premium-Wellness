<?php

namespace App\Models\Supabase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Traits\HasLocalizedContent;

class AiSchemaCache extends Model
{
    use HasLocalizedContent;

    protected $connection = 'supabase';

    protected $table = 'ai_schema_cache';

    protected $fillable = [
        'schemaable_type',
        'schemaable_id',
        'locale',
        'schema_type',
        'schema_json',
        'expires_at',
    ];

    protected $casts = [
        'schema_json' => 'array',
        'expires_at'  => 'datetime',
    ];

    /**
     * Get the owning schemaable model.
     */
    public function schemaable(): MorphTo
    {
        return $this->morphTo();
    }
}
