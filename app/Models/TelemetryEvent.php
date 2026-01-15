<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelemetryEvent extends Model
{
    protected $fillable = [
        'event_name',
        'form_id',
        'view_id',
        'participant_token_hash',
        'server_ts',
        'client_ts',
        'device_type',
        'locale',
        'user_agent',
        'properties',
    ];

    protected $casts = [
        'server_ts' => 'datetime',
        'client_ts' => 'datetime',
        'properties' => 'array',
    ];
}
