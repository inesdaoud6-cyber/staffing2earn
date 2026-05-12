<?php

namespace App\Filament\Resources\ApplicationProgressResource\Pages;

use App\Filament\Resources\ApplicationProgressResource;
use Filament\Resources\Pages\ListRecords;

class ListApplicationProgress extends ListRecords
{
    protected static string $resource = ApplicationProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
