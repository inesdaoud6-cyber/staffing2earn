<?php

namespace App\Livewire;

use App\Models\AdminNotification;
use Livewire\Attributes\On;
use Livewire\Component;

class AdminNotificationBell extends Component
{
    public bool $open = false;

    public int $unreadCount = 0;

    public array $recent = [];

    public function mount(): void
    {
        $this->refresh();
    }

    #[On('admin-notifications-changed')]
    public function refresh(): void
    {
        $userId = auth()->id();
        if (! $userId) {
            $this->unreadCount = 0;
            $this->recent     = [];

            return;
        }

        $this->unreadCount = AdminNotification::query()
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->count();

        $this->recent = AdminNotification::query()
            ->where('user_id', $userId)
            ->latest()
            ->take(8)
            ->get()
            ->map(fn ($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'title'      => $n->title,
                'message'    => $n->message,
                'is_read'    => (bool) $n->is_read,
                'created_at' => $n->created_at?->diffForHumans(),
            ])
            ->all();
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;

        if ($this->open) {
            $this->refresh();
        }
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function markAllRead(): void
    {
        AdminNotification::query()
            ->where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $this->refresh();
    }

    public function openNotification(int $id)
    {
        $notif = AdminNotification::query()
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->with('applicationProgress')
            ->first();

        if (! $notif) {
            return null;
        }

        if (! $notif->is_read) {
            $notif->update(['is_read' => true]);
        }

        return $this->redirect($notif->url);
    }

    public function render()
    {
        return view('livewire.admin-notification-bell');
    }
}
