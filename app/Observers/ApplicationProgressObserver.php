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
        $name        = $candidate?->full_name
            ?: $candidate?->user?->name
            ?: __('Unknown candidate');
        $email = $candidate?->user?->email ?? '';

        $offerLabel = $progress->offre?->title ?? __('Open application');

        $title = __('admin.notif_new_application_title');
        $body  = __('admin.notif_new_application_message', [
            'candidate' => $name,
            'email'     => $email ?: '—',
            'offer'     => $offerLabel,
        ]);

        $admins = User::role('admin')->select('id')->get();
        if ($admins->isEmpty()) {
            return;
        }

        $now = now();
        $rows = $admins->map(fn (User $u) => [
            'user_id'                   => $u->id,
            'type'                      => 'application',
            'title'                     => $title,
            'message'                   => $body,
            'is_read'                   => false,
            'application_progress_id'   => $progress->id,
            'offre_id'                  => $progress->offre_id,
            'created_at'                => $now,
            'updated_at'                => $now,
        ])->all();

        AdminNotification::insert($rows);
    }
}
