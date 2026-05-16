<?php

namespace App\Filament\Resources\GroupResource\Pages;

use App\Filament\Resources\GroupResource;
use App\Models\Block;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ListGroups extends ListRecords
{
    protected static string $resource = GroupResource::class;

    #[Url(as: 'block')]
    public ?string $blockFilter = null;

    #[Url(as: 'questions')]
    public ?string $questionsCountFilter = null;

    public function updatedBlockFilter(): void
    {
        if ($this->blockFilter === '') {
            $this->blockFilter = null;
        }

        $this->resetPage();
    }

    public function updatedQuestionsCountFilter(): void
    {
        if ($this->questionsCountFilter === '') {
            $this->questionsCountFilter = null;
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
        return GroupResource::table($table)
            ->filters([])
            ->modifyQueryUsing(function (Builder $query): Builder {
                $query->withCount('questions');

                if (filled($this->blockFilter)) {
                    $query->where('block_id', (int) $this->blockFilter);
                }

                $range = $this->questionsCountFilter;
                if (filled($range) && in_array($range, ['none', '1_5', '6_plus'], true)) {
                    match ($range) {
                        'none' => $query->having('questions_count', '=', 0),
                        '1_5' => $query->havingBetween('questions_count', [1, 5]),
                        '6_plus' => $query->having('questions_count', '>=', 6),
                    };
                }

                return $query;
            });
    }

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
