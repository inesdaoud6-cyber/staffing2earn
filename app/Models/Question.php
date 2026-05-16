<?php

namespace App\Models;

use App\Services\TestScoringService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

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

    protected static function booted(): void
    {
        static::saving(function (Question $question): void {
            if (empty($question->group_id)) {
                return;
            }

            $group = Group::query()->find($question->group_id);
            if (! $group) {
                throw ValidationException::withMessages([
                    'group_id' => __('validation.exists', ['attribute' => 'group_id']),
                ]);
            }

            if (empty($question->block_id)) {
                $question->block_id = $group->block_id;

                return;
            }

            if ((int) $question->block_id !== (int) $group->block_id) {
                throw ValidationException::withMessages([
                    'group_id' => __('test.group-block-mismatch'),
                ]);
            }
        });
    }

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

    public function isMcqComponent(): bool
    {
        return in_array($this->component, ['radio', 'list', 'checkbox'], true);
    }

    public function isMultipleChoiceComponent(): bool
    {
        return $this->component === 'checkbox';
    }

    /**
     * @return list<string>
     */
    public function correctAnswerValues(): array
    {
        if ($this->component === 'checkbox') {
            $raw = $this->correct_answer;
            if (is_string($raw) && str_starts_with(trim($raw), '[')) {
                $decoded = json_decode($raw, true);

                if (is_array($decoded)) {
                    return array_values(array_filter(
                        array_map('strval', $decoded),
                        static fn (string $v): bool => $v !== ''
                    ));
                }
            }

            return [];
        }

        if ($this->correct_answer !== null && $this->correct_answer !== '') {
            return [(string) $this->correct_answer];
        }

        return [];
    }

    public function isCandidateAnswerCorrect(mixed $answer): bool
    {
        if (in_array($this->component, ['radio', 'list'], true)) {
            $correct = $this->answers()->where('is_correct', true)->first();

            return $correct && (string) $correct->text === (string) $answer;
        }

        if ($this->component === 'checkbox') {
            $selected = is_array($answer)
                ? array_values(array_map('strval', $answer))
                : [];
            $correct = $this->correctAnswerValues();
            sort($selected);
            $sortedCorrect = $correct;
            sort($sortedCorrect);

            return $correct !== [] && $selected === $sortedCorrect;
        }

        return false;
    }

    /**
     * Question score in % (max_note + second_ratio bonus when correct).
     */
    public function scoreCandidateAnswer(mixed $answer): float
    {
        return app(TestScoringService::class)->scoreQuestionPercent($this, $answer);
    }

    public function serializeCandidateAnswer(mixed $answer): ?string
    {
        if ($this->component === 'checkbox') {
            $values = is_array($answer)
                ? array_values(array_filter(
                    array_map('strval', $answer),
                    static fn (string $v): bool => $v !== ''
                ))
                : [];

            return json_encode($values, JSON_UNESCAPED_UNICODE);
        }

        return is_string($answer) ? $answer : null;
    }

    public function deserializeCandidateAnswer(?string $stored): mixed
    {
        if ($this->component === 'checkbox' && is_string($stored) && str_starts_with(trim($stored), '[')) {
            $decoded = json_decode($stored, true);

            return is_array($decoded) ? array_values($decoded) : [];
        }

        return $stored;
    }

    /**
     * @param  list<string>|string|null  $multipleCorrect
     */
    public static function encodeCorrectAnswerForStorage(string $component, mixed $singleCorrect, mixed $multipleCorrect = null): ?string
    {
        if ($component === 'checkbox') {
            $values = is_array($multipleCorrect)
                ? array_values(array_filter(
                    array_map('strval', $multipleCorrect),
                    static fn (string $v): bool => $v !== ''
                ))
                : [];

            return $values !== [] ? json_encode($values, JSON_UNESCAPED_UNICODE) : null;
        }

        return $singleCorrect !== null && $singleCorrect !== '' ? (string) $singleCorrect : null;
    }

    /**
     * Keep the answers table in sync with possible_answers + correct answer(s).
     */
    public function syncAnswerRowsFromMcqOptions(): void
    {
        if (! $this->isMcqComponent()) {
            $this->answers()->delete();

            return;
        }

        $this->answers()->delete();

        $options = array_values(array_filter(
            $this->possible_answers ?? [],
            static fn ($t) => $t !== null && $t !== ''
        ));

        $correctValues = $this->correctAnswerValues();

        foreach ($options as $order => $text) {
            Answer::create([
                'question_id' => $this->id,
                'text' => (string) $text,
                'is_correct' => in_array((string) $text, $correctValues, true),
                'order' => $order,
            ]);
        }
    }
}
