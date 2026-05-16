<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Models\Question;
use App\Filament\Resources\Pages\EditRecord;

class EditQuestion extends EditRecord
{
    protected static string $resource = QuestionResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (($data['component'] ?? '') === 'checkbox') {
            $data['correct_answers'] = (new Question([
                'component' => 'checkbox',
                'correct_answer' => $data['correct_answer'] ?? null,
            ]))->correctAnswerValues();
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $component = (string) ($data['component'] ?? 'radio');
        $data['correct_answer'] = Question::encodeCorrectAnswerForStorage(
            $component,
            $data['correct_answer'] ?? null,
            $data['correct_answers'] ?? null,
        );
        unset($data['correct_answers']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncAnswerRowsFromMcqOptions();
    }
}