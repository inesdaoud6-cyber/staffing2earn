<?php

namespace App\Filament\Resources\TestResource\Pages;

use App\Filament\Resources\TestResource;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewTest extends ViewRecord
{
    protected static string $resource = TestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Modifier propriétés'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Informations du test')->schema([
                TextEntry::make('name')
                    ->label('Nom'),
                TextEntry::make('description')
                    ->label('Description'),
                TextEntry::make('eligibility_threshold')
                    ->label('Seuil éligibilité')
                    ->suffix('%'),
                TextEntry::make('talent_threshold')
                    ->label('Seuil talent')
                    ->suffix('%'),
                TextEntry::make('questions_count')
                    ->label('Nombre de questions')
                    ->state(fn ($record) => $record->questions()->count()),
                TextEntry::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y'),
            ])->columns(2),
        ]);
    }
}
