<?php

namespace App\Services;

use App\Models\ApplicationProgress;
use App\Models\Candidate;
use App\Models\CandidateNotification;
use App\Models\Offre;
use App\Models\Temoignage;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class CandidateService
{
    public function getCandidateByUser(User $user): ?Candidate
    {
        return $user->candidate;
    }

    public function createFromUser(User $user, array $validated): Candidate
    {
        $user->assignRole('candidate');

        return Candidate::firstOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $validated['first_name'] ?? $user->name,
                'last_name'  => $validated['last_name']  ?? '',
                'email'      => $validated['email']       ?? $user->email,
                'cv_path'    => null,
            ]
        );
    }

    public function getApplicationStats(Candidate $candidate): array
    {
        $base = ApplicationProgress::where('candidate_id', $candidate->id);

        return [
            'total'     => (clone $base)->count(),
            'pending'   => (clone $base)->whereIn('status', ['pending', 'in_progress'])->count(),
            'completed' => (clone $base)->where('status', 'validated')->count(),
            'rejected'  => (clone $base)->where('status', 'rejected')->count(),
            'recent'    => (clone $base)->with('offre')->latest()->take(5)->get(),
            'all'       => (clone $base)->with('offre')->latest()->get(),
        ];
    }

    public function getUnreadNotificationsCount(User $user): int
    {
        return CandidateNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
    }

    public function getNotifications(User $user): Collection
    {
        return CandidateNotification::where('user_id', $user->id)
            ->with('offre')
            ->latest()
            ->get();
    }

    public function markAllNotificationsAsRead(User $user): void
    {
        CandidateNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    public function getActiveOffers(): Collection
    {
        return Offre::where('is_published', true)
            ->where(fn ($q) => $q->whereNull('deadline')->orWhere('deadline', '>=', now()))
            ->latest()
            ->take(10)
            ->get();
    }

    public function saveTestimonial(User $user, array $data): Temoignage
    {
        return Temoignage::updateOrCreate(
            ['user_id' => $user->id],
            [
                'contenu'     => $data['contenu'],
                'note'        => (int) $data['note'],
                'is_approved' => false,
            ]
        );
    }

    public function getTestimonial(User $user): ?Temoignage
    {
        return Temoignage::where('user_id', $user->id)->first();
    }

    public function updateProfile(User $user, Candidate $candidate, array $data): void
    {
        $user->update([
            'name' => trim($data['first_name'] . ' ' . $data['last_name']),
        ]);

        $candidate->update([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'phone'      => $data['phone']      ?? null,
            'address'    => $data['address']    ?? null,
            'birth_date' => $data['birth_date'] ?? null,
        ]);
    }

    public function updatePassword(User $user, string $newPassword): void
    {
        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }
}
