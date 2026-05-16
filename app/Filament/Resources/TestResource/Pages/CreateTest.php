<?php

namespace App\Filament\Resources\TestResource\Pages;

use App\Filament\Resources\TestResource;
use App\Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateTest extends CreateRecord
{
    protected static string $resource = TestResource::class;

    protected function afterValidate(): void
    {
        $assignments = $this->form->getState()['block_assignments'] ?? [];
        $questionCount = collect($assignments)
            ->pluck('question_ids')
            ->flatten()
            ->filter(fn ($id) => filled($id))
            ->count();

        if ($questionCount < 1) {
            throw ValidationException::withMessages([
                'data.block_assignments' => __('test.at-least-one-question'),
            ]);
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['block_assignments']);

        return TestResource::mergeWholeTestTimerFromHourMinuteFields($data);
    }

    protected function afterCreate(): void
    {
        $assignments = $this->form->getState()['block_assignments'] ?? [];
        TestResource::syncTestFromBlockAssignments($this->record, $assignments);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
