<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Response extends Model
{
    protected $fillable = [
        'application_id',
        'level',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(ApplicationProgress::class, 'application_id');
    }

    public function questionResponses(): HasMany
    {
        return $this->hasMany(QuestionResponse::class);
    }
}