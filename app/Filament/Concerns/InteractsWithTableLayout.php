<?php

namespace App\Filament\Concerns;

use App\Filament\Support\TableLayoutConfigurator;
use Livewire\Attributes\Url;

trait InteractsWithTableLayout
{
    #[Url(as: 'layout', history: true)]
    public string $tableLayout = TableLayoutConfigurator::LAYOUT_LIST;

    protected function initializeTableLayout(): void
    {
        $this->tableLayout = TableLayoutConfigurator::normalize($this->tableLayout);
    }

    /**
     * @return array<int, \Filament\Actions\Action>
     */
    protected function getTableLayoutToggleActions(): array
    {
        return TableLayoutConfigurator::toggleActions($this);
    }

    /**
     * @param  array<int, mixed>  $actions
     * @return array<int, mixed>
     */
    protected function prependTableLayoutToggleActions(array $actions): array
    {
        return [...$this->getTableLayoutToggleActions(), ...$actions];
    }

    /**
     * @param  array<int, mixed>  $actions
     * @return array<int, mixed>
     */
    protected function appendTableLayoutToggleActions(array $actions): array
    {
        return [...$actions, ...$this->getTableLayoutToggleActions()];
    }
}
