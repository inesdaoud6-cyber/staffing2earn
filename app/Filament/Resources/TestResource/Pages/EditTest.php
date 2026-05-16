<?php

namespace App\Filament\Resources\TestResource\Pages;

use App\Filament\Resources\TestResource;
use Filament\Actions;
use App\Filament\Resources\Pages\EditRecord;

class EditTest extends EditRecord
{
    protected static string $resource = TestResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = TestResource::splitWholeTestTimerIntoHourMinuteFields($data);

        $data['block_assignments'] = TestResource::blockAssignmentsFormStateForTest($this->record);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['block_assignments']);

        return TestResource::mergeWholeTestTimerFromHourMinuteFields($data);
    }

    protected function afterSave(): void
    {
        $assignments = $this->form->getState()['block_assignments'] ?? [];
        TestResource::syncTestFromBlockAssignments($this->record, $assignments);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Supprimer'),
        ];
    }
}
