<?php
namespace App\Filament\Resources\OffreResource\Pages;
use App\Filament\Resources\OffreResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditOffre extends EditRecord {
    protected static string $resource = OffreResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}