<?php

namespace App\Filament\Resources\TestResource\Pages;

use App\Filament\Resources\TestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTest extends CreateRecord
{
    protected static string $resource = TestResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}