<?php

namespace App\Filament\Resources\ApplicationProgressResource\Pages;

use App\Filament\Resources\ApplicationProgressResource;
use App\Models\Offre;
use Filament\Actions\Action;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ListApplicationProgress extends ListRecords
{
    protected static string $resource = ApplicationProgressResource::class;

    public string $offre = '';

    #[Url(as: 'layout', history: true)]
    public string $applicationsTableLayout = 'list';

    public function mount(): void
    {
        $this->offre = (string) (request()->route('offre') ?? '');

        $this->assertOffreRouteIsValid();

        parent::mount();

        if (! in_array($this->applicationsTableLayout, ['list', 'cards'], true)) {
            $this->applicationsTableLayout = 'list';
        }
    }

    public function getTitle(): string|Htmlable
    {
        if ($this->offre === 'libre') {
            return __('admin.application_title_free_applications');
        }

        $offre = Offre::find((int) $this->offre);

        return $offre
            ? $offre->title.' — '.__('admin.applications')
            : __('admin.applications');
    }

    public function getBreadcrumb(): ?string
    {
        if ($this->offre === 'libre') {
            return __('admin.application_title_free_applications');
        }

        return Offre::find((int) $this->offre)?->title;
    }

    public function table(Table $table): Table
    {
        return ApplicationProgressResource::configureTable($table, $this->applicationsTableLayout)
            ->modifyQueryUsing(function (Builder $query): Builder {
                if ($this->offre === 'libre') {
                    return $query->whereNull('offre_id');
                }

                return $query->where('offre_id', (int) $this->offre);
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back_to_offers')
                ->label(__('admin.application_back_to_hub'))
                ->icon('heroicon-o-arrow-left')
                ->url(ApplicationProgressResource::getUrl('index'))
                ->color('gray'),
            Action::make('layout_list')
                ->label(__('admin.applications_view_list'))
                ->icon('heroicon-o-bars-3')
                ->color(fn (): string => $this->applicationsTableLayout === 'list' ? 'primary' : 'gray')
                ->outlined(fn (): bool => $this->applicationsTableLayout !== 'list')
                ->action(fn () => $this->applicationsTableLayout = 'list'),
            Action::make('layout_cards')
                ->label(__('admin.applications_view_cards'))
                ->icon('heroicon-o-squares-2x2')
                ->color(fn (): string => $this->applicationsTableLayout === 'cards' ? 'primary' : 'gray')
                ->outlined(fn (): bool => $this->applicationsTableLayout !== 'cards')
                ->action(fn () => $this->applicationsTableLayout = 'cards'),
        ];
    }

    private function assertOffreRouteIsValid(): void
    {
        $key = (string) ($this->offre ?? '');

        if ($key === '' || ($key !== 'libre' && ! ctype_digit($key))) {
            abort(404);
        }

        if ($key !== 'libre' && ! Offre::whereKey((int) $key)->exists()) {
            abort(404);
        }
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make(__('admin.applications_tab_all')),
            'awaiting_review' => Tab::make(__('admin.applications_tab_awaiting_review'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('level_status', 'awaiting_approval'))
                ->badge(fn (): ?string => $this->countApplicationsAwaitingReview()),
        ];

        $levelCount = $this->resolvedOfferLevelTabCount();

        for ($level = 1; $level <= $levelCount; $level++) {
            $lv = $level;
            $tabs['level_'.$level] = Tab::make(__('admin.applications_tab_level', ['n' => $lv]))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('current_level', $lv));
        }

        return $tabs;
    }

    private function applicationsTableBaseQuery(): Builder
    {
        $query = ApplicationProgressResource::getEloquentQuery();

        if ($this->offre === 'libre') {
            return $query->whereNull('offre_id');
        }

        return $query->where('offre_id', (int) $this->offre);
    }

    private function countApplicationsAwaitingReview(): ?string
    {
        $count = (int) $this->applicationsTableBaseQuery()
            ->where('level_status', 'awaiting_approval')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    private function resolvedOfferLevelTabCount(): int
    {
        if ($this->offre !== 'libre') {
            $offre = Offre::find((int) $this->offre);
            if ($offre && (int) $offre->levels_count > 0) {
                return min(20, max(1, (int) $offre->levels_count));
            }
        }

        $maxLevel = (int) ($this->applicationsTableBaseQuery()->max('current_level') ?? 1);

        return min(20, max(3, $maxLevel));
    }
}
