<?php

namespace App\Filament\Resources\BlockResource\Pages;

use App\Filament\Resources\BlockResource;
use App\Filament\Resources\Pages\CreateRecord;

class CreateBlock extends CreateRecord
{
    protected static string $resource = BlockResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label(__('Save')),
            $this->getCancelFormAction(),
        ];
    }
}
