<?php

namespace App\Filament\Resources\ApplicationProgressResource\Pages;

use App\Filament\Resources\ApplicationProgressResource;
use App\Filament\Resources\Pages\CreateRecord;

class CreateApplicationProgress extends CreateRecord
{
    protected static string $resource = ApplicationProgressResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
