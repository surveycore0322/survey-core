<?php

namespace App\Models;

use App\Casts\ValueCaster;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    protected $fillable = [
        'snapshot_id',
        'question_id',
        'value',
        'value_type',
        'value_scalar',
    ];

    protected $casts = [
        'value' => ValueCaster::class,
    ];

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(Snapshot::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
