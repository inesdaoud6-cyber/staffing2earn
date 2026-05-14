<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    #[Url(as: 'role')]
    public ?string $roleFilter = null;

    public function updatedRoleFilter(): void
    {
        if ($this->roleFilter === '') {
            $this->roleFilter = null;
        }

        $this->resetPage();
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->modifyQueryUsing(function (Builder $query): Builder {
                if (filled($this->roleFilter) && in_array($this->roleFilter, ['admin', 'candidate'], true)) {
                    $query->whereHas(
                        'roles',
                        fn (Builder $q): Builder => $q->where('name', $this->roleFilter),
                    );
                }

                return $query;
            });
    }

    /**
     * Require at least 2 characters for global search; a single character returns no rows.
     */
    protected function applySearchToTableQuery(Builder $query): Builder
    {
        $search = $this->getTableSearch();

        $this->applyColumnSearchesToTableQuery($query);

        if (blank($search)) {
            return $query;
        }

        $trimmed = trim($search);
        if (mb_strlen($trimmed) < 2) {
            return $query->whereRaw('0 = 1');
        }

        $this->applyGlobalSearchToTableQuery($query);

        return $query;
    }
}
