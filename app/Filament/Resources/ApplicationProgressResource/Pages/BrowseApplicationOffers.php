<?php

namespace App\Filament\Resources\ApplicationProgressResource\Pages;

use App\Filament\Resources\ApplicationProgressResource;
use App\Models\Offre;
use Filament\Actions\Action;
use Filament\Resources\Concerns\HasTabs;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class BrowseApplicationOffers extends Page implements HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string $resource = ApplicationProgressResource::class;

    protected static string $view = 'filament.resources.application-progress-resource.pages.browse-applications';

    public function mount(): void
    {
        $this->mountInteractsWithTable();
    }

    public function getTitle(): string|Htmlable
    {
        return __('admin.application_hub_title');
    }

    /**
     * @return array{total: int, pending: int, in_progress: int, awaiting_review: int}
     */
    public function getFreeApplicationStats(): array
    {
        $base = ApplicationProgressResource::getEloquentQuery()->whereNull('offre_id');

        return [
            'total' => (int) (clone $base)->count(),
            'pending' => (int) (clone $base)->where('status', 'pending')->count(),
            'in_progress' => (int) (clone $base)->where('status', 'in_progress')->count(),
            'awaiting_review' => (int) (clone $base)->where('level_status', 'awaiting_approval')->count(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Offre::query()
                    ->withCount([
                        'applicationProgresses as applications_count' => fn ($query) => $query->where('status', '!=', 'cancelled'),
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('nav.job_offer'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('domain')
                    ->label(__('admin.domain'))
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_published')
                    ->label(__('admin.application_offer_published'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('applications_count')
                    ->label(__('admin.application_offer_applications_count'))
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deadline')
                    ->label(__('admin.deadline'))
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->recordUrl(fn (Offre $record): string => ApplicationProgressResource::getUrl('by_offer', ['offre' => $record->getKey()]))
            ->actions([
                Tables\Actions\Action::make('viewApplications')
                    ->label(__('admin.application_view_offer_applications'))
                    ->icon('heroicon-o-arrow-right')
                    ->button()
                    ->url(fn (Offre $record): string => ApplicationProgressResource::getUrl('by_offer', ['offre' => $record->getKey()])),
            ])
            ->defaultSort('title');
    }

    protected function getHeaderActions(): array
    {
        $awaiting = $this->getFreeApplicationStats()['awaiting_review'];

        return [
            Action::make('manageFree')
                ->label(__('admin.application_manage_free'))
                ->icon('heroicon-o-inbox')
                ->color('primary')
                ->badge($awaiting > 0 ? (string) $awaiting : null)
                ->url(ApplicationProgressResource::getUrl('free')),
            Action::make('createApplication')
                ->label(__('Create').' '.ApplicationProgressResource::getModelLabel())
                ->icon('heroicon-o-plus')
                ->url(ApplicationProgressResource::getUrl('create')),
        ];
    }
}
