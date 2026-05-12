<?php

namespace App\Services;

use App\Models\CandidateNotification;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    public function send(User $user, string $type, string $title, string $message, ?int $offreId = null): CandidateNotification
    {
        return CandidateNotification::create([
            'user_id'  => $user->id,
            'type'     => $type,
            'title'    => $title,
            'message'  => $message,
            'is_read'  => false,
            'offre_id' => $offreId,
        ]);
    }

    public function sendLevelSubmitted(User $user, int $level, ?int $offreId = null): CandidateNotification
    {
        return $this->send(
            $user,
            'info',
            __('Niveau :level soumis', ['level' => $level]),
            __('Votre niveau :level a été soumis. En attente de validation par l\'administrateur.', ['level' => $level]),
            $offreId
        );
    }

    public function sendLevelApproved(User $user, int $level, ?int $offreId = null): CandidateNotification
    {
        return $this->send(
            $user,
            'success',
            __('Niveau :level validé', ['level' => $level]),
            __('Félicitations ! Votre niveau :level a été validé. Vous pouvez passer au niveau suivant.', ['level' => $level]),
            $offreId
        );
    }

    public function sendLevelRejected(User $user, int $level, ?int $offreId = null): CandidateNotification
    {
        return $this->send(
            $user,
            'danger',
            __('Niveau :level non validé', ['level' => $level]),
            __('Votre niveau :level n\'a pas été validé. Veuillez contacter l\'administrateur pour plus d\'informations.', ['level' => $level]),
            $offreId
        );
    }

    public function sendApplicationValidated(User $user, string $offreTitle, ?int $offreId = null): CandidateNotification
    {
        return $this->send(
            $user,
            'success',
            __('Candidature validée'),
            __('Félicitations ! Votre candidature pour le poste « :offre » a été validée.', ['offre' => $offreTitle]),
            $offreId
        );
    }

    public function getUnreadCount(User $user): int
    {
        return CandidateNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
    }

    public function getAll(User $user): Collection
    {
        return CandidateNotification::where('user_id', $user->id)
            ->latest()
            ->get();
    }

    public function markAllRead(User $user): void
    {
        CandidateNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    public function markRead(int $notificationId, User $user): void
    {
        CandidateNotification::where('id', $notificationId)
            ->where('user_id', $user->id)
            ->update(['is_read' => true]);
    }
}