<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionResponse extends Model
{
    protected $fillable = [
        'response_id',
        'question_id',
        'answer_id',
        'auto_score',
        'manual_score',
        'obtained_score',
        'text_answer',
    ];

    protected $casts = [
        'auto_score'     => 'float',
        'manual_score'   => 'float',
        'obtained_score' => 'float',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(Answer::class);
    }

    public function getEffectiveScoreAttribute(): float
    {
        return $this->obtained_score ?? $this->auto_score ?? 0.0;
    }
}