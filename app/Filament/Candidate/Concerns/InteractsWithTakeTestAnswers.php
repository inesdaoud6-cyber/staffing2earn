<?php

namespace App\Filament\Candidate\Concerns;

use App\Models\Question;

trait InteractsWithTakeTestAnswers
{
    /**
     * Livewire checkbox groups require an array per question; a scalar/boolean binds every box together.
     */
    protected function ensureMcqAnswerDefaults(): void
    {
        foreach ($this->getQuestions() as $question) {
            $id = $question->id;

            if ($question->component === 'checkbox') {
                if (! isset($this->answers[$id]) || ! is_array($this->answers[$id])) {
                    $this->answers[$id] = [];
                }

                continue;
            }

            if (in_array($question->component, ['radio', 'list'], true) && is_array($this->answers[$id] ?? null)) {
                $this->answers[$id] = (string) ($this->answers[$id][0] ?? '');
            }
        }
    }

    /**
     * @return list<string>
     */
    public function mcqOptionsForQuestion(Question $question): array
    {
        $fromTags = collect($question->possible_answers ?? [])
            ->map(static fn ($option): string => is_scalar($option) ? trim((string) $option) : '')
            ->filter(static fn (string $text): bool => $text !== '')
            ->values();

        if ($fromTags->isNotEmpty()) {
            return $fromTags->all();
        }

        if ($question->relationLoaded('answers') && $question->answers->isNotEmpty()) {
            return $question->answers
                ->sortBy('order')
                ->pluck('text')
                ->map(static fn ($text): string => trim((string) $text))
                ->filter(static fn (string $text): bool => $text !== '')
                ->values()
                ->all();
        }

        return [];
    }

    public function updated($property): void
    {
        if (! is_string($property) || ! preg_match('/^answers\.(\d+)$/', $property, $matches)) {
            return;
        }

        $questionId = (int) $matches[1];
        $question = Question::find($questionId);

        if ($question?->component === 'checkbox' && ! is_array($this->answers[$questionId] ?? null)) {
            $this->answers[$questionId] = [];
        }
    }
}
