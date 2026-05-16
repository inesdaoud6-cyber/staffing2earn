<?php

namespace App\Filament\Resources\ApplicationProgressResource\Pages;

use App\Filament\Resources\ApplicationProgressResource;
use App\Filament\Resources\Pages\EditRecord;

class EditApplicationProgress extends EditRecord
{
    protected static string $resource = ApplicationProgressResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->redirect(ApplicationProgressResource::reviewUrlFor($this->getRecord()));
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
