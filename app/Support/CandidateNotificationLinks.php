<?php

namespace App\Support;

use App\Models\ApplicationProgress;
use App\Models\Candidate;
use App\Models\CandidateNotification;

class CandidateNotificationLinks
{
    public static function applicationsUrl(?int $applicationId = null, string $view = 'progress'): string
    {
        $base = route('filament.candidate.pages.applications');

        if (! $applicationId) {
            return $base;
        }

        return $base.'?'.http_build_query([
            'application' => $applicationId,
            'view' => in_array($view, ['details', 'progress'], true) ? $view : 'progress',
        ]);
    }

    public static function takeTestUrl(?int $applicationId = null): string
    {
        $base = route('filament.candidate.pages.take-test');

        return $applicationId ? $base.'?application='.$applicationId : $base;
    }

    public static function jobOffersUrl(?int $offreId = null): string
    {
        $base = route('filament.candidate.pages.choix-candidature');

        return $offreId ? $base.'#offre-'.$offreId : $base;
    }

    public static function resolve(CandidateNotification $notification): string
    {
        $appId = $notification->resolveApplicationProgressId();

        return match ($notification->type) {
            'offre' => self::jobOffersUrl($notification->offre_id),
            'warning' => self::takeTestUrl($appId),
            'validated', 'rejected' => self::applicationsUrl($appId, 'details'),
            'result' => self::applicationsUrl($appId, 'progress'),
            'application' => self::applicationsUrl($appId, 'details'),
            'info', 'success', 'danger' => self::resolveInfoUrl($appId),
            default => self::applicationsUrl($appId, 'progress'),
        };
    }

    private static function resolveInfoUrl(?int $applicationId): string
    {
        if (! $applicationId) {
            return route('filament.candidate.pages.applications');
        }

        $application = ApplicationProgress::query()->find($applicationId);

        if (! $application) {
            return route('filament.candidate.pages.applications');
        }

        if ($application->score_published) {
            return self::applicationsUrl($applicationId, 'progress');
        }

        if ($application->level_status === 'awaiting_approval') {
            return self::applicationsUrl($applicationId, 'progress');
        }

        if ($application->status === 'rejected') {
            return self::applicationsUrl($applicationId, 'details');
        }

        if (
            $application->status === 'in_progress'
            && $application->test_id
            && in_array($application->level_status, ['in_progress', 'approved'], true)
        ) {
            $hasCurrentLevelResponse = $application->responses()
                ->where('level', $application->current_level)
                ->exists();

            if (! $hasCurrentLevelResponse || $application->canStartTimedTestSession()) {
                return self::takeTestUrl($applicationId);
            }
        }

        return self::applicationsUrl($applicationId, 'progress');
    }

    public static function resolveApplicationProgressId(
        CandidateNotification $notification,
    ): ?int {
        if ($notification->application_progress_id) {
            return (int) $notification->application_progress_id;
        }

        if (! $notification->user_id) {
            return null;
        }

        $candidateId = Candidate::query()
            ->where('user_id', $notification->user_id)
            ->value('id');

        if (! $candidateId) {
            return null;
        }

        $query = ApplicationProgress::query()
            ->where('candidate_id', $candidateId)
            ->where('status', '!=', 'cancelled');

        if ($notification->offre_id) {
            $query->where('offre_id', $notification->offre_id);
        } elseif (in_array($notification->type, ['application', 'validated', 'rejected', 'info', 'warning', 'result'], true)) {
            $query->whereNull('offre_id');
        }

        $id = $query->latest('updated_at')->value('id');

        return $id ? (int) $id : null;
    }
}
