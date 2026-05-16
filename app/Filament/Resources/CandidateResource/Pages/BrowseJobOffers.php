<?php

namespace App\Filament\Resources\CandidateResource\Pages;

use App\Filament\Concerns\InteractsWithTableLayout;
use App\Filament\Resources\CandidateResource;
use App\Filament\Resources\OffreResource;
use App\Models\ApplicationProgress;
use App\Models\Offre;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class BrowseJobOffers extends Page implements HasTable
{
    use InteractsWithTableLayout;
    use Tables\Concerns\InteractsWithTable;

    protected static string $resource = CandidateResource::class;

    protected static string $view = 'filament.resources.candidate-resource.pages.browse-job-offers';

    public function mount(): void
    {
        $this->initializeTableLayout();
        $this->mountInteractsWithTable();
    }

    public function getTitle(): string|Htmlable
    {
        return __('nav.candidates_management');
    }

    public function getFreeApplicationsCount(): int
    {
        return ApplicationProgress::query()
            ->whereNull('offre_id')
            ->where('status', '!=', 'cancelled')
            ->count();
    }

    public function getFreeApplicationsUrl(): string
    {
        return CandidateResource::getUrl('by_offer', ['offre' => 'libre']);
    }

    public function table(Table $table): Table
    {
        return OffreResource::configureCandidateOffersHubTable(
            $table
                ->query(
                    Offre::query()
                        ->withCount([
                            'applicationProgresses as applications_count' => fn ($query) => $query->where('status', '!=', 'cancelled'),
                        ])
                )
                ->recordUrl(fn (Offre $record): string => CandidateResource::getUrl('by_offer', ['offre' => $record->getKey()]))
                ->emptyStateHeading(__('admin.candidate_no_offers'))
                ->emptyStateDescription(__('admin.candidate_no_offers_desc'))
                ->defaultSort('title'),
            $this->tableLayout,
        );
    }

    protected function getHeaderActions(): array
    {
        return $this->getTableLayoutToggleActions();
    }
}
