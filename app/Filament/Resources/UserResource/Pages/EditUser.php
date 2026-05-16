<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use App\Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['role'] = $this->getRecord()->roles->first()?->name ?? 'candidate';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $role = Arr::get($this->data, 'role');

        if (! in_array($role, ['admin', 'candidate'], true)) {
            $role = $this->getRecord()->roles->first()?->name ?? 'candidate';
        }

        $data['role'] = $role;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var User $record */
        $role = $data['role'] ?? 'candidate';
        unset($data['role']);

        $record->update($data);

        if ($role === 'candidate' && $record->hasRole('admin')) {
            $otherAdmins = User::role('admin')->where('id', '!=', $record->id)->count();
            if ($otherAdmins === 0) {
                Notification::make()
                    ->title(__('admin.last_admin_cannot_demote'))
                    ->danger()
                    ->send();

                return $record;
            }
        }

        if (in_array($role, ['admin', 'candidate'], true)) {
            $record->syncRoles([$role]);
        }

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (): bool => UserResource::canDelete($this->getRecord())),
        ];
    }
}
