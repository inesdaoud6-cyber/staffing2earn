<?php

namespace App\Filament\Resources\ApplicationProgressResource\Pages;

use App\Filament\Resources\ApplicationProgressResource;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class ListFreeApplications extends ListApplicationProgress
{
    protected static string $resource = ApplicationProgressResource::class;

    protected static ?string $navigationLabel = null;

    public function mount(): void
    {
        $this->offre = 'libre';

        parent::mount();
    }

    public function getTitle(): string|Htmlable
    {
        return __('admin.application_title_free_applications');
    }

    public function getBreadcrumb(): ?string
    {
        return __('admin.application_title_free_applications');
    }

    public function table(Table $table): Table
    {
        return ApplicationProgressResource::configureTable($table, $this->tableLayout, hideOfferColumn: true)
            ->modifyQueryUsing(fn ($query) => $query->whereNull('offre_id'));
    }

    protected function getHeaderActions(): array
    {
        $actions = parent::getHeaderActions();

        foreach ($actions as $action) {
            if ($action->getName() === 'back_to_offers') {
                $action
                    ->label(__('admin.application_back_to_hub'))
                    ->icon('heroicon-o-arrow-left');
            }
        }

        return $actions;
    }
}
