<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResponseNote extends Model
{
    protected $fillable = [
        'question_response_id',
        'note',
        'reviewer_id',
    ];

    public function questionResponse(): BelongsTo
    {
        return $this->belongsTo(QuestionResponse::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}