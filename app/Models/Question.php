<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'obligatory' => 'boolean',
        'scorable' => 'boolean',
        'auto_evaluation' => 'boolean',
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

    /**
     * Keep the answers table in sync with possible_answers + correct_answer so
     * candidate auto-scoring (Answer::is_correct) continues to work.
     */
    public function syncAnswerRowsFromMcqOptions(): void
    {
        if (! in_array($this->component, ['radio', 'list'], true)) {
            $this->answers()->delete();

            return;
        }

        $this->answers()->delete();

        $options = array_values(array_filter(
            $this->possible_answers ?? [],
            static fn ($t) => $t !== null && $t !== ''
        ));

        $correct = $this->correct_answer;

        foreach ($options as $order => $text) {
            Answer::create([
                'question_id' => $this->id,
                'text' => (string) $text,
                'is_correct' => $correct !== null && (string) $correct === (string) $text,
                'order' => $order,
            ]);
        }
    }
}
