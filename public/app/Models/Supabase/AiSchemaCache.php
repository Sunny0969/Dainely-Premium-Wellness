<?php

namespace App\Models\Supabase;

use App\Traits\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AiSchemaCache extends Model
{
    use HasLocalizedContent;

    protected $connection = 'supabase';

    protected $table = 'ai_schema_cache';

    protected $fillable = [
        'cacheable_type',
        'cacheable_id',
        'locale',
        'schema_data',
        'schema_version',
        'generated_at',
    ];

    protected $casts = [
        'schema_data'  => 'array',
        'generated_at' => 'datetime',
    ];

    public function cacheable(): MorphTo
    {
        return $this->morphTo();
    }
}
