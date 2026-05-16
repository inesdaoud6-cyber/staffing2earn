<?php

namespace App\Filament\Resources\BlockResource\Pages;

use App\Filament\Concerns\InteractsWithCreatedAtSort;
use App\Filament\Resources\BlockResource;
use App\Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListBlocks extends ListRecords
{
    use InteractsWithCreatedAtSort;

    protected static string $resource = BlockResource::class;

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->modifyQueryUsing(fn (Builder $query): Builder => $this->applyCreatedAtSort($query));
    }

    protected function getHeaderActions(): array
    {
        return $this->prependTableLayoutToggleActions([
            Actions\CreateAction::make(),
        ]);
    }
}
