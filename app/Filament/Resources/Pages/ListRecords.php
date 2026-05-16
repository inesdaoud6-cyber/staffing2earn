<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Concerns\InteractsWithTableLayout;
use Filament\Resources\Pages\ListRecords as BaseListRecords;
use Filament\Tables\Table;

class ListRecords extends BaseListRecords
{
    use InteractsWithTableLayout;

    public function mount(): void
    {
        parent::mount();

        $this->initializeTableLayout();
    }

    public function table(Table $table): Table
    {
        $resource = static::getResource();

        $configured = method_exists($resource, 'configureTable')
            ? $resource::configureTable($table, $this->tableLayout)
            : $resource::table($table);

        return $this->configureListRecordsTable($configured);
    }

    protected function configureListRecordsTable(Table $table): Table
    {
        return $table;
    }

    protected function getHeaderActions(): array
    {
        return $this->prependTableLayoutToggleActions(parent::getHeaderActions());
    }
}
