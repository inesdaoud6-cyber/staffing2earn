<?php

namespace App\Filament\Widgets;

use App\Models\ApplicationProgress;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestApplications extends BaseWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Dernières Candidatures';

    protected int|string|array $columnSpan = 'full';

    public static function getHeading(): string
    {
        return __('stats.latest_applications');
    }

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
                    ->label(fn () => __('admin.application_column_applicant'))
                    ->getStateUsing(fn ($record) => $record->candidate?->full_name ?? $record->candidate?->user?->name ?? '—'),
                TextColumn::make('offre.title')
                    ->label(fn () => __('admin.associated_offer'))
                    ->default(fn () => __('admin.free-application')),
                TextColumn::make('status')
                    ->label(fn () => __('Status'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'validated' => 'success',
                        'rejected' => 'danger',
                        'in_progress' => 'info',
                        default      => 'warning',
                    }),
                TextColumn::make('main_score')
                    ->label(fn () => __('Score'))
                    ->suffix('/100'),
                TextColumn::make('current_level')
                    ->label(fn () => __('Level')),
                TextColumn::make('created_at')
                    ->label(fn () => __('Date'))
                    ->dateTime('d/m/Y H:i'),
            ]);
    }
}
