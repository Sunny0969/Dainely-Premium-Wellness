<?php

namespace App\Models\Supabase;

use Illuminate\Database\Eloquent\Model;

class RelatedContent extends Model
{
    protected $connection = 'supabase';

    protected $table = 'related_content';

    protected $fillable = [
        'source_type',
        'source_id',
        'related_type',
        'related_id',
        'display_order',
    ];
}
