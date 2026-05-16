<?php
namespace App\Filament\Resources\OffreResource\Pages;
use App\Filament\Resources\OffreResource;
use Filament\Actions;
use App\Filament\Resources\Pages\ListRecords;

class ListOffres extends ListRecords
{
    protected static string $resource = OffreResource::class;
    protected function getHeaderActions(): array
    {
        return $this->prependTableLayoutToggleActions([
            Actions\CreateAction::make(),
        ]);
    }
}