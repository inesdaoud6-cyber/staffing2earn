<?php

namespace App\Observers;

use App\Models\CandidateNotification;
use App\Models\Offre;
use App\Models\User;

class OffreObserver
{
    /**
     * Fires when a brand-new offer row is created. If it lands already
     * published, notify every candidate immediately.
     */
    public function created(Offre $offre): void
    {
        if ($offre->is_published) {
            $this->notifyCandidates($offre);
        }
    }

    /**
     * Fires on every save to an existing offer. Only notifies when the
     * is_published flag transitions from false → true, so admins editing
     * a typo on an already-public offer don't spam the candidates.
     */
    public function updated(Offre $offre): void
    {
        if (! $offre->wasChanged('is_published')) {
            return;
        }

        $previous = $offre->getOriginal('is_published');

        if (! $previous && $offre->is_published) {
            $this->notifyCandidates($offre);
        }
    }

    private function notifyCandidates(Offre $offre): void
    {
        $users = User::role('candidate')->select('id')->get();

        if ($users->isEmpty()) {
            return;
        }

        $now = now();
        $rows = $users->map(fn ($u) => [
            'user_id'    => $u->id,
            'type'       => 'offre',
            'title'      => '💼 ' . __('admin.new_offer_published'),
            'message'    => __('admin.new_offer_msg', ['title' => $offre->title]),
            'is_read'    => false,
            'offre_id'   => $offre->id,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        // Bulk insert is dramatically faster than N model saves when the
        // candidate pool grows. Schema is small and well-defined here.
        CandidateNotification::insert($rows);
    }
}
