<?php

namespace App\Filament\Widgets;

use App\Models\ApplicationProgress;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestApplications extends BaseWidget
{
    protected static ?string $heading = 'Dernières Candidatures';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ApplicationProgress::query()
                    ->with(['candidate.user', 'offre'])
                    ->where('status', '!=', 'cancelled')
                    ->latest()
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('candidate.full_name')
                    ->label('Candidat')
                    ->getStateUsing(fn ($record) => $record->candidate?->full_name ?? $record->candidate?->user?->name ?? '—'),
                TextColumn::make('offre.title')
                    ->label('Offre')
                    ->default('Candidature libre'),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'validated' => 'success',
                        'rejected' => 'danger',
                        'in_progress' => 'info',
                        default => 'warning',
                    }),
                TextColumn::make('main_score')
                    ->label('Score')
                    ->suffix('/100'),
                TextColumn::make('current_level')
                    ->label('Niveau'),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i'),
            ]);
    }
}