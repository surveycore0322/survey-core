<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $casts = [
        'options' => 'array',  // JSON
        'required' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $fillable = [
        'form_id','label','type','purpose','options','required','sort_order','description',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }
}
