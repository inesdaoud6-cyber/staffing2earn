<?php

namespace App\Filament\Resources\ApplicationProgressResource\Pages;

use App\Filament\Resources\ApplicationProgressResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditApplicationProgress extends EditRecord
{
    protected static string $resource = ApplicationProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
