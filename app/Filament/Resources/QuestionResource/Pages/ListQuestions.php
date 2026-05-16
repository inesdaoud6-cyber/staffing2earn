<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Models\Question;
use App\Support\QuestionFormOptions;
use Filament\Actions;
use App\Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ListQuestions extends ListRecords
{
    protected static string $resource = QuestionResource::class;

    #[Url(as: 'level')]
    public ?string $levelFilter = null;

    #[Url(as: 'type')]
    public ?string $componentFilter = null;

    public function updatedLevelFilter(): void
    {
        if ($this->levelFilter === '') {
            $this->levelFilter = null;
        }

        $this->resetPage();
    }

    public function updatedComponentFilter(): void
    {
        if ($this->componentFilter === '') {
            $this->componentFilter = null;
        }

        $this->resetPage();
    }

    /**
     * @return array<int|string, int|string>
     */
    public function getLevelFilterOptions(): array
    {
        return Question::query()
            ->select('level')
            ->distinct()
            ->orderBy('level')
            ->pluck('level', 'level')
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function getComponentFilterOptions(): array
    {
        return QuestionFormOptions::componentOptions();
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->filters([])
            ->modifyQueryUsing(function (Builder $query): Builder {
                if (filled($this->levelFilter)) {
                    $query->where('level', (int) $this->levelFilter);
                }

                if (filled($this->componentFilter)) {
                    $query->where('component', $this->componentFilter);
                }

                return $query;
            });
    }

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
