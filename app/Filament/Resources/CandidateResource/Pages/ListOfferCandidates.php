<?php

namespace App\Filament\Resources\CandidateResource\Pages;

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
use Illuminate\Database\Eloquent\Builder;

class ListOfferCandidates extends Page implements HasTable
{
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
        $ranks = $this->rankByApplicationId;

        return $table
            ->query(
                ApplicationProgress::query()
                    ->where('offre_id', $offreId)
                    ->where('status', '!=', 'cancelled')
                    ->with(['candidate.user'])
                    ->orderByRaw('COALESCE(main_score, 0) DESC')
                    ->orderBy('id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->label(__('admin.candidate_rank'))
                    ->getStateUsing(fn (ApplicationProgress $record): string => (string) ($ranks[$record->id] ?? '—'))
                    ->alignCenter()
                    ->sortable(false),
                Tables\Columns\TextColumn::make('candidate.full_name')
                    ->label(__('admin.full_name'))
                    ->getStateUsing(fn (ApplicationProgress $record): string => $record->candidate?->full_name ?? '—')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('candidate', function (Builder $q) use ($search): void {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', "%{$search}%"));
                        });
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending' => __('Pending'),
                        'in_progress' => __('In Progress'),
                        'validated' => __('Validated'),
                        'rejected' => __('Rejected'),
                        'cancelled' => __('Cancelled'),
                        default => (string) $state,
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'validated' => 'success',
                        'rejected' => 'danger',
                        'in_progress' => 'info',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('main_score')
                    ->label(__('admin.candidate_score'))
                    ->formatStateUsing(fn ($state): string => $state !== null && $state !== ''
                        ? number_format((float) $state, 2).'%'
                        : '—')
                    ->alignEnd()
                    ->sortable(),
            ])
            ->recordUrl(fn (ApplicationProgress $record): string => CandidateResource::getUrl('view', [
                'offre' => $this->offre,
                'record' => $record->candidate_id,
            ]))
            ->emptyStateHeading(__('admin.candidate_none_for_offer'))
            ->emptyStateDescription(__('admin.candidate_none_for_offer_desc'))
            ->paginated([10, 25, 50]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back_to_offers')
                ->label(__('admin.candidate_back_to_offers'))
                ->icon('heroicon-o-arrow-left')
                ->url(CandidateResource::getUrl('index'))
                ->color('gray'),
        ];
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
