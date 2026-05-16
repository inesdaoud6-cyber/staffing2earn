<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

trait InteractsWithCreatedAtSort
{
    #[Url(as: 'sort')]
    public string $timeSort = 'newest';

    public function updatedTimeSort(): void
    {
        if (! in_array($this->timeSort, ['newest', 'oldest'], true)) {
            $this->timeSort = 'newest';
        }

        $this->resetPage();
    }

    protected function normalizeTimeSort(): string
    {
        return in_array($this->timeSort, ['newest', 'oldest'], true) ? $this->timeSort : 'newest';
    }

    protected function applyCreatedAtSort(Builder $query): Builder
    {
        $direction = $this->normalizeTimeSort() === 'oldest' ? 'asc' : 'desc';

        return $query->reorder()->orderBy('created_at', $direction)->orderBy('id', $direction);
    }
}
