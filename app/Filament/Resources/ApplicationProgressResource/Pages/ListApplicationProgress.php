<?php

namespace App\Filament\Resources\ApplicationProgressResource\Pages;

use App\Filament\Resources\ApplicationProgressResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Livewire\Attributes\Url;

class ListApplicationProgress extends ListRecords
{
    protected static string $resource = ApplicationProgressResource::class;

    #[Url(as: 'layout', history: true)]
    public string $applicationsTableLayout = 'list';

    public function mount(): void
    {
        parent::mount();

        if (! in_array($this->applicationsTableLayout, ['list', 'cards'], true)) {
            $this->applicationsTableLayout = 'list';
        }
    }

    public function table(Table $table): Table
    {
        return ApplicationProgressResource::configureTable($table, $this->applicationsTableLayout);
    }

    protected function getHeaderActions(): array
    {
        return [
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
}
