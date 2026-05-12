<?php
namespace App\Filament\Resources\OffreResource\Pages;
use App\Filament\Resources\OffreResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListOffres extends ListRecords {
    protected static string $resource = OffreResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}