<?php

namespace App\Filament\Resources\CandidateResource\Pages;

use App\Filament\Resources\CandidateResource;
use App\Models\Offre;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class BrowseJobOffers extends Page implements HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string $resource = CandidateResource::class;

    protected static string $view = 'filament.resources.candidate-resource.pages.browse-job-offers';

    public function mount(): void
    {
        $this->mountInteractsWithTable();
    }

    public function getTitle(): string|Htmlable
    {
        return __('nav.candidates_management');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Offre::query()
                    ->withCount([
                        'applicationProgresses as candidates_count' => fn ($query) => $query->where('status', '!=', 'cancelled'),
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('nav.job_offer'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('candidates_count')
                    ->label(__('nav.candidates'))
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deadline')
                    ->label(__('admin.deadline'))
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->recordUrl(fn (Offre $record): string => CandidateResource::getUrl('by_offer', ['offre' => $record->getKey()]))
            ->emptyStateHeading(__('admin.candidate_no_offers'))
            ->emptyStateDescription(__('admin.candidate_no_offers_desc'))
            ->defaultSort('title');
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
