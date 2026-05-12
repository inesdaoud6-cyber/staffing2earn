<?php

namespace App\Filament\Resources\ApplicationProgressResource\Pages;

use App\Filament\Resources\ApplicationProgressResource;
use Filament\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewApplicationProgress extends ViewRecord
{
    protected static string $resource = ApplicationProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => ! $this->record->is_archived),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Candidat')
                ->schema([
                    TextEntry::make('candidate.full_name')
                        ->label('Nom complet'),
                    TextEntry::make('candidate.user.email')
                        ->label('Email'),
                ])
                ->columns(2),

            Section::make('Candidature')
                ->schema([
                    TextEntry::make('offre.title')
                        ->label('Offre')
                        ->default('Candidature libre'),
                    TextEntry::make('status')
                        ->label('Statut')
                        ->badge()
                        ->formatStateUsing(fn ($state) => match ($state) {
                            'pending'     => '⏳ En attente',
                            'in_progress' => '🔄 En cours',
                            'validated'   => '✅ Validé',
                            'rejected'    => '❌ Rejeté',
                            default       => $state,
                        })
                        ->color(fn ($state) => match ($state) {
                            'validated'   => 'success',
                            'rejected'    => 'danger',
                            'in_progress' => 'info',
                            default       => 'warning',
                        }),
                    TextEntry::make('current_level')
                        ->label('Niveau actuel'),
                    TextEntry::make('test.name')
                        ->label('Test associé')
                        ->default('Aucun'),
                ])
                ->columns(2),

            Section::make('Scores')
                ->schema([
                    TextEntry::make('main_score')
                        ->label('Score principal')
                        ->suffix('/100'),
                    TextEntry::make('secondary_score')
                        ->label('Score secondaire')
                        ->suffix('/100'),
                    IconEntry::make('score_published')
                        ->label('Score publié')
                        ->boolean(),
                    IconEntry::make('apply_enabled')
                        ->label('Candidature activée')
                        ->boolean(),
                ])
                ->columns(2),

            Section::make('Métadonnées')
                ->schema([
                    TextEntry::make('created_at')
                        ->label('Créée le')
                        ->dateTime('d/m/Y H:i'),
                    TextEntry::make('updated_at')
                        ->label('Mise à jour le')
                        ->dateTime('d/m/Y H:i'),
                    IconEntry::make('is_archived')
                        ->label('Archivée')
                        ->boolean(),
                ])
                ->columns(3),
        ]);
    }
}