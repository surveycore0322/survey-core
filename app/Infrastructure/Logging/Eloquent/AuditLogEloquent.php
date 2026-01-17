<?php

namespace App\Infrastructure\Logging\Eloquent;

use Illuminate\Database\Eloquent\Model;

class AuditLogEloquent extends Model
{
    protected $table = 'audit_logs';
    // ★追加：監査ログは audit コネクションで書く（rollbackの影響を受けない）
    protected $connection = 'audit';

    protected $fillable = [
        'occurred_at',
        'trace_id',
        'level',
        'event',
        'scope_type',
        'scope_id',
        'actor_type',
        'actor_id',
        'request_json',
        'io_json',
        'details_json',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'request_json' => 'array',
        'io_json' => 'array',
        'details_json' => 'array',
    ];
}
