<?php

namespace App\Filament\Pages;

use App\Models\AdminNotification;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;

class AdminNotifications extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationGroup = 'Recrutement';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.admin-notifications';

    protected static ?string $slug = 'admin-notifications';

    public static function getNavigationLabel(): string
    {
        return __('nav.notifications_management');
    }

    public function getTitle(): string
    {
        return __('admin.notifications_inbox');
    }

    public string $filter = 'all';

    public array $selected = [];

    public bool $selectAll = false;

    /** @var Collection<int,AdminNotification> */
    public Collection $notifications;

    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->notifications = new Collection();
        $this->load();
    }

    public function updatedFilter(): void
    {
        $this->selected  = [];
        $this->selectAll = false;
        $this->load();
    }

    public function updatedSelectAll(bool $value): void
    {
        $this->selected = $value
            ? $this->notifications->pluck('id')->map(fn ($id) => (int) $id)->all()
            : [];
    }

    public function toggleRead(int $id): void
    {
        $row = $this->ownedQuery()->find($id);
        if (! $row) {
            return;
        }

        $row->update(['is_read' => ! $row->is_read]);

        $this->load();
    }

    public function deleteOne(int $id): void
    {
        $this->ownedQuery()->whereKey($id)->delete();
        $this->selected = array_values(array_filter(
            $this->selected,
            fn ($i) => (int) $i !== $id,
        ));
        $this->load();

        Notification::make()->title(__('Notification deleted.'))->success()->send();
    }

    public function bulkMarkRead(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = $this->ownedQuery()
            ->whereIn('id', $this->selected)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $this->afterBulk();

        Notification::make()
            ->title(trans_choice('{0} Nothing to mark.|{1} 1 notification marked as read.|[2,*] :count notifications marked as read.', $count, ['count' => $count]))
            ->success()
            ->send();
    }

    public function bulkMarkUnread(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = $this->ownedQuery()
            ->whereIn('id', $this->selected)
            ->where('is_read', true)
            ->update(['is_read' => false]);

        $this->afterBulk();

        Notification::make()
            ->title(trans_choice('{0} Nothing to update.|{1} 1 notification marked as unread.|[2,*] :count notifications marked as unread.', $count, ['count' => $count]))
            ->success()
            ->send();
    }

    public function bulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = $this->ownedQuery()->whereIn('id', $this->selected)->delete();

        $this->afterBulk();

        Notification::make()
            ->title(trans_choice('{0} Nothing to delete.|{1} 1 notification deleted.|[2,*] :count notifications deleted.', $count, ['count' => $count]))
            ->success()
            ->send();
    }

    public function deleteAll(): void
    {
        $count = $this->ownedQuery()->delete();

        $this->afterBulk();

        Notification::make()
            ->title(trans_choice('{0} Nothing to delete.|{1} 1 notification deleted.|[2,*] :count notifications deleted.', $count, ['count' => $count]))
            ->success()
            ->send();
    }

    public function markAllRead(): void
    {
        $count = $this->ownedQuery()->where('is_read', false)->update(['is_read' => true]);

        $this->load();

        Notification::make()
            ->title(trans_choice('{0} Nothing to mark.|{1} 1 notification marked as read.|[2,*] :count notifications marked as read.', $count, ['count' => $count]))
            ->success()
            ->send();
    }

    private function ownedQuery()
    {
        return AdminNotification::query()->where('user_id', auth()->id());
    }

    private function load(): void
    {
        $query = $this->ownedQuery()->with([
            'applicationProgress.candidate.user',
            'applicationProgress.offre',
            'offre',
        ]);

        if ($this->filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($this->filter === 'read') {
            $query->where('is_read', true);
        }

        $this->notifications = $query->latest()->get();
        $this->unreadCount   = $this->ownedQuery()->where('is_read', false)->count();

        $visibleIds     = $this->notifications->pluck('id')->all();
        $this->selected = array_values(array_intersect($this->selected, $visibleIds));
        $this->selectAll = ! empty($visibleIds) && count($this->selected) === count($visibleIds);
    }

    private function afterBulk(): void
    {
        $this->selected  = [];
        $this->selectAll = false;
        $this->load();
    }
}
