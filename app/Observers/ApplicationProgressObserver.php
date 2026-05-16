<?php

namespace App\Observers;

use App\Models\AdminNotification;
use App\Models\ApplicationProgress;
use App\Models\User;

class ApplicationProgressObserver
{
    public function created(ApplicationProgress $progress): void
    {
        $progress->loadMissing(['candidate.user', 'offre']);

        $candidate = $progress->candidate;
        $name = $candidate?->full_name
            ?: $candidate?->user?->name
            ?: __('Unknown candidate');
        $email = $candidate?->user?->email ?? '';

        $offerLabel = $progress->offre?->title ?? __('Open application');

        $title = __('admin.notif_new_application_title');
        $body = __('admin.notif_new_application_message', [
            'candidate' => $name,
            'email' => $email ?: '—',
            'offer' => $offerLabel,
        ]);

        $this->insertAdminNotifications($progress, $title, $body, 'application');
    }

    public function updated(ApplicationProgress $progress): void
    {
        if (! $progress->wasChanged('level_status')) {
            return;
        }

        if ($progress->level_status !== 'awaiting_approval') {
            return;
        }

        $progress->loadMissing(['candidate.user', 'offre']);

        $candidate = $progress->candidate;
        $name = $candidate?->full_name
            ?: $candidate?->user?->name
            ?: __('Unknown candidate');
        $email = $candidate?->user?->email ?? '';
        $offerLabel = $progress->offre?->title ?? __('Open application');

        $title = __('admin.notif_level_submitted_title');
        $body = __('admin.notif_level_submitted_message', [
            'candidate' => $name,
            'email' => $email ?: '—',
            'offer' => $offerLabel,
            'level' => (string) $progress->current_level,
        ]);

        $this->insertAdminNotifications($progress, $title, $body, 'level_submitted');
    }

    private function insertAdminNotifications(
        ApplicationProgress $progress,
        string $title,
        string $body,
        string $type
    ): void {
        $admins = User::role('admin')->select('id')->get();
        if ($admins->isEmpty()) {
            return;
        }

        $now = now();
        $rows = $admins->map(fn (User $u) => [
            'user_id' => $u->id,
            'type' => $type,
            'title' => $title,
            'message' => $body,
            'is_read' => false,
            'application_progress_id' => $progress->id,
            'offre_id' => $progress->offre_id,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        AdminNotification::insert($rows);
    }
}
