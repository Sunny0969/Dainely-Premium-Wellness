<?php

namespace App\Models\Supabase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserActivityLog extends Model
{
    protected $connection = 'supabase';

    protected $table = 'user_activity_log';

    public $timestamps = false;

    protected $fillable = [
        'visitor_id',
        'user_id',
        'event_type',
        'item_type',
        'item_id',
        'context',
        'created_at',
    ];

    protected $casts = [
        'context'    => 'array',
        'created_at' => 'datetime',
    ];

    public function item(): MorphTo
    {
        return $this->morphTo();
    }
}
