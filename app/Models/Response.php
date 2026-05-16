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
        'test_score',
        'eligibility_passed',
        'talent_passed',
    ];

    protected $casts = [
        'test_score' => 'decimal:2',
        'eligibility_passed' => 'boolean',
        'talent_passed' => 'boolean',
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