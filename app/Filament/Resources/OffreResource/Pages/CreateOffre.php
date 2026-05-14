<?php

namespace App\Filament\Resources\OffreResource\Pages;

use App\Filament\Resources\OffreResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOffre extends CreateRecord
{
    protected static string $resource = OffreResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return OffreResource::normalizeLevelsFormData($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
