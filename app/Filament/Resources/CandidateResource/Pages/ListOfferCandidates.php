<?php

namespace App\Filament\Resources\CandidateResource\Pages;

use App\Filament\Concerns\InteractsWithTableLayout;
use App\Filament\Resources\CandidateResource;
use App\Models\ApplicationProgress;
use App\Models\Offre;
use App\Support\OfferApplicationRanking;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class ListOfferCandidates extends Page implements HasTable
{
    use InteractsWithTableLayout;
    use Tables\Concerns\InteractsWithTable;

    protected static string $resource = CandidateResource::class;

    protected static string $view = 'filament.resources.candidate-resource.pages.list-offer-candidates';

    public string $offre = '';

    /** @var array<int, int> */
    protected array $rankByApplicationId = [];

    public function mount(): void
    {
        $this->offre = (string) (request()->route('offre') ?? '');
        $this->assertOffreRouteIsValid();
        $this->rankByApplicationId = $this->isFreeApplications()
            ? OfferApplicationRanking::ranksForFreeApplications()
            : OfferApplicationRanking::ranksForOffer((int) $this->offre);
        $this->initializeTableLayout();
        $this->mountInteractsWithTable();
    }

    public function getTitle(): string|Htmlable
    {
        if ($this->isFreeApplications()) {
            return __('admin.application_title_free_applications').' — '.__('nav.candidates');
        }

        $offre = Offre::find((int) $this->offre);

        return $offre
            ? $offre->title.' — '.__('nav.candidates')
            : __('nav.candidates');
    }

    public function getBreadcrumb(): ?string
    {
        if ($this->isFreeApplications()) {
            return __('admin.application_title_free_applications');
        }

        return Offre::find((int) $this->offre)?->title;
    }

    public function table(Table $table): Table
    {
        $query = ApplicationProgress::query()
            ->where('status', '!=', 'cancelled')
            ->with(['candidate.user'])
            ->orderByRaw('COALESCE(main_score, 0) DESC')
            ->orderBy('id');

        if ($this->isFreeApplications()) {
            $query->whereNull('offre_id');
        } else {
            $query->where('offre_id', (int) $this->offre);
        }

        return CandidateResource::configureOfferApplicantsTable(
            $table
                ->query($query)
                ->recordUrl(fn (ApplicationProgress $record): string => CandidateResource::getUrl('view', [
                    'offre' => $this->offre,
                    'record' => $record->candidate_id,
                ]))
                ->emptyStateHeading(
                    $this->isFreeApplications()
                        ? __('admin.candidate_none_free_applications')
                        : __('admin.candidate_none_for_offer')
                )
                ->emptyStateDescription(
                    $this->isFreeApplications()
                        ? __('admin.candidate_none_free_applications_desc')
                        : __('admin.candidate_none_for_offer_desc')
                )
                ->paginated([10, 25, 50]),
            $this->tableLayout,
            $this->rankByApplicationId,
        );
    }

    protected function getHeaderActions(): array
    {
        return $this->appendTableLayoutToggleActions([
            Action::make('back_to_offers')
                ->label(__('admin.candidate_back_to_offers'))
                ->icon('heroicon-o-arrow-left')
                ->url(CandidateResource::getUrl('index'))
                ->color('gray'),
        ]);
    }

    private function isFreeApplications(): bool
    {
        return $this->offre === 'libre';
    }

    private function assertOffreRouteIsValid(): void
    {
        $key = (string) ($this->offre ?? '');

        if ($key === '') {
            abort(404);
        }

        if ($key === 'libre') {
            return;
        }

        if (! ctype_digit($key) || ! Offre::whereKey((int) $key)->exists()) {
            abort(404);
        }
    }
}
