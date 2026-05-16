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
        $this->rankByApplicationId = OfferApplicationRanking::ranksForOffer((int) $this->offre);
        $this->initializeTableLayout();
        $this->mountInteractsWithTable();
    }

    public function getTitle(): string|Htmlable
    {
        $offre = Offre::find((int) $this->offre);

        return $offre
            ? $offre->title.' — '.__('nav.candidates')
            : __('nav.candidates');
    }

    public function getBreadcrumb(): ?string
    {
        return Offre::find((int) $this->offre)?->title;
    }

    public function table(Table $table): Table
    {
        $offreId = (int) $this->offre;

        return CandidateResource::configureOfferApplicantsTable(
            $table
                ->query(
                    ApplicationProgress::query()
                        ->where('offre_id', $offreId)
                        ->where('status', '!=', 'cancelled')
                        ->with(['candidate.user'])
                        ->orderByRaw('COALESCE(main_score, 0) DESC')
                        ->orderBy('id')
                )
                ->recordUrl(fn (ApplicationProgress $record): string => CandidateResource::getUrl('view', [
                    'offre' => $this->offre,
                    'record' => $record->candidate_id,
                ]))
                ->emptyStateHeading(__('admin.candidate_none_for_offer'))
                ->emptyStateDescription(__('admin.candidate_none_for_offer_desc'))
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

    private function assertOffreRouteIsValid(): void
    {
        $key = (string) ($this->offre ?? '');

        if ($key === '' || ! ctype_digit($key)) {
            abort(404);
        }

        if (! Offre::whereKey((int) $key)->exists()) {
            abort(404);
        }
    }
}
