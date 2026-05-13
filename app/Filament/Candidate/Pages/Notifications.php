<?php

namespace App\Filament\Candidate\Pages;

use App\Models\CandidateNotification;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;

class Notifications extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.candidate.pages.notifications';

    protected static ?string $slug = 'notifications';

    public static function getNavigationLabel(): string
    {
        return __('My Notifications');
    }

    public function getTitle(): string
    {
        return __('My Notifications');
    }

    /** all | unread | read */
    public string $filter = 'all';

    /** @var array<int,int> Ids of currently-selected notifications. */
    public array $selected = [];

    /** Drives the "select all visible rows" checkbox. */
    public bool $selectAll = false;

    /** @var Collection<int,CandidateNotification> */
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
        return CandidateNotification::query()->where('user_id', auth()->id());
    }

    private function load(): void
    {
        $query = $this->ownedQuery()->with('offre');

        if ($this->filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($this->filter === 'read') {
            $query->where('is_read', true);
        }

        $this->notifications = $query->latest()->get();
        $this->unreadCount   = $this->ownedQuery()->where('is_read', false)->count();

        // Drop selected ids that aren't visible anymore.
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
