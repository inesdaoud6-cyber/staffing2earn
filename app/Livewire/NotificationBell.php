<?php

namespace App\Livewire;

use App\Models\CandidateNotification;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationBell extends Component
{
    public bool $open = false;

    public int $unreadCount = 0;

    public $recent = [];

    public function mount(): void
    {
        $this->refresh();
    }

    /**
     * Poll target — refreshes the badge + dropdown contents in place. Wired
     * via wire:poll on the root element of the view.
     */
    #[On('notifications-changed')]
    public function refresh(): void
    {
        $userId = auth()->id();
        if (! $userId) {
            $this->unreadCount = 0;
            $this->recent     = [];
            return;
        }

        $this->unreadCount = CandidateNotification::query()
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->count();

        $this->recent = CandidateNotification::query()
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
                'offre_id'   => $n->offre_id,
                'url'        => $n->url,
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

    public function markRead(int $id): void
    {
        CandidateNotification::query()
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->update(['is_read' => true]);

        $this->refresh();
    }

    /**
     * Mark the notification as read AND open the full notifications page. The
     * detailed view (offer info, action buttons, etc.) lives there.
     */
    public function openNotification(int $id)
    {
        $notif = CandidateNotification::query()
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (! $notif) {
            return null;
        }

        if (! $notif->is_read) {
            $notif->update(['is_read' => true]);
        }

        return $this->redirect(route('filament.candidate.pages.notifications'));
    }

    public function markAllRead(): void
    {
        CandidateNotification::query()
            ->where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $this->refresh();
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
