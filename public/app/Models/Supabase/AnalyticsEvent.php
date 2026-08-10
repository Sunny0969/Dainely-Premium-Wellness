<?php

namespace App\Models\Supabase;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    protected $connection = 'supabase';

    protected $table = 'analytics_events';

    protected $fillable = [
        'event_name',
        'event_data',
        'session_id',
        'user_id',
        'occurred_at',
    ];

    protected $casts = [
        'event_data'  => 'array',
        'occurred_at' => 'datetime',
    ];
}
