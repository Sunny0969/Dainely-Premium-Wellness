<?php

namespace App\Models\Supabase;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $connection = 'supabase';

    protected $table = 'webhook_logs';

    protected $fillable = [
        'source',
        'event_type',
        'payload',
        'status',
        'error',
        'processed_at',
    ];

    protected $casts = [
        'payload'      => 'array',
        'processed_at' => 'datetime',
    ];
}
