<?php

namespace App\Filament\Resources\TestResource\Pages;

use App\Filament\Resources\TestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTest extends EditRecord
{
    protected static string $resource = TestResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return TestResource::splitWholeTestTimerIntoHourMinuteFields($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return TestResource::mergeWholeTestTimerFromHourMinuteFields($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Supprimer'),
        ];
    }
}
