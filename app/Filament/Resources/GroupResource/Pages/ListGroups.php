<?php

namespace App\Filament\Resources\GroupResource\Pages;

use App\Filament\Resources\GroupResource;
use App\Models\Block;
use Filament\Actions;
use App\Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ListGroups extends ListRecords
{
    protected static string $resource = GroupResource::class;

    #[Url(as: 'block')]
    public ?string $blockFilter = null;

    public function updatedBlockFilter(): void
    {
        if ($this->blockFilter === '') {
            $this->blockFilter = null;
        }

        $this->resetPage();
    }

    /**
     * @return array<int, string>
     */
    public function getBlockFilterOptions(): array
    {
        return Block::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->filters([])
            ->modifyQueryUsing(function (Builder $query): Builder {
                $query->withCount('questions');

                if (filled($this->blockFilter)) {
                    $query->where('block_id', (int) $this->blockFilter);
                }

                return $query;
            });
    }

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
