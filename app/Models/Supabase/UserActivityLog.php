<?php

namespace App\Models\Supabase;

use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    protected $connection = 'supabase';

    protected $table = 'user_activity_log';

    protected $fillable = [
        'session_id',
        'visitor_id',
        'action',
        'details',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'details' => 'array',
    ];
}
