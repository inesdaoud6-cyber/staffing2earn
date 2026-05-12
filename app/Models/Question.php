<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Question extends Model
{
    protected $fillable = [
        'block_id',
        'group_id',
        'offre_id',
        'question_fr',
        'question_en',
        'question_ar',
        'component',
        'level',
        'obligatory',
        'scorable',
        'auto_evaluation',
        'correct_answer',
        'classification',
        'max_note',
        'second_ratio',
        'user_note',
        'note_rule',
        'possible_answers',
    ];

    protected $casts = [
        'possible_answers' => 'array',
        'obligatory'       => 'boolean',
        'scorable'         => 'boolean',
        'auto_evaluation'  => 'boolean',
    ];

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function offre(): BelongsTo
    {
        return $this->belongsTo(Offre::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function tests()
    {
        return $this->belongsToMany(Test::class, 'question_test');
    }

    public function questionResponses(): HasMany
    {
        return $this->hasMany(QuestionResponse::class);
    }
}