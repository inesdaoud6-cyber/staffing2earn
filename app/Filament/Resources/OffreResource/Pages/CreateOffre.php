<?php

namespace App\Filament\Resources\OffreResource\Pages;

use App\Filament\Resources\OffreResource;
use App\Filament\Resources\Pages\CreateRecord;

class CreateOffre extends CreateRecord
{
    protected static string $resource = OffreResource::class;

    protected function afterFill(): void
    {
        $raw = $this->form->getRawState();
        $this->form->fill(array_merge($raw, [
            'level_test_ids' => OffreResource::paddedLevelTestIds(
                is_array($raw['level_test_ids'] ?? null) ? $raw['level_test_ids'] : null,
                (int) ($raw['levels_count'] ?? 2)
            ),
        ]));
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return OffreResource::normalizeLevelsFormData($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
