<?php

namespace App\Models\Supabase;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasLocalizedContent;

class AnalyticsEvent extends Model
{
    use HasLocalizedContent;

    protected $connection = 'supabase';

    protected $table = 'analytics_events';

    protected $fillable = [
        'session_id',
        'visitor_id',
        'event_name',
        'payload',
        'locale',
        'url',
        'user_agent',
        'ip_address',
        'processed_ga4',
        'processed_meta',
        'ga4_error',
        'meta_error',
    ];

    protected $casts = [
        'payload'        => 'array',
        'processed_ga4'  => 'boolean',
        'processed_meta' => 'boolean',
    ];
}
