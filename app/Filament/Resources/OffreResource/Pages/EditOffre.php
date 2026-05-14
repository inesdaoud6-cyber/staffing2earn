<?php

namespace App\Filament\Resources\OffreResource\Pages;

use App\Filament\Resources\OffreResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOffre extends EditRecord
{
    protected static string $resource = OffreResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (empty($data['level_test_ids']) && ! empty($data['test_id'])) {
            $data['level_test_ids'] = [(int) $data['test_id']];
        }

        if (empty($data['levels_count'])) {
            $ids = $data['level_test_ids'] ?? [];
            $data['levels_count'] = max(2, is_array($ids) ? count($ids) + 1 : 2);
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return OffreResource::normalizeLevelsFormData($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
