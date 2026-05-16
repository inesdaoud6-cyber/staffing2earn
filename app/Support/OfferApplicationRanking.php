<?php

namespace App\Support;

use App\Models\ApplicationProgress;
use Illuminate\Support\Collection;

class OfferApplicationRanking
{
    /**
     * @return array<int, int> application id => rank (1 = highest score)
     */
    public static function ranksForOffer(int $offreId): array
    {
        return self::ranksFromQuery(
            ApplicationProgress::query()->where('offre_id', $offreId)
        );
    }

    /**
     * @return array<int, int> application id => rank (1 = highest score)
     */
    public static function ranksForFreeApplications(): array
    {
        return self::ranksFromQuery(
            ApplicationProgress::query()->whereNull('offre_id')
        );
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<ApplicationProgress>  $query
     * @return array<int, int>
     */
    private static function ranksFromQuery($query): array
    {
        $orderedIds = $query
            ->where('status', '!=', 'cancelled')
            ->orderByRaw('COALESCE(main_score, 0) DESC')
            ->orderBy('id')
            ->pluck('id');

        $ranks = [];
        foreach ($orderedIds as $index => $id) {
            $ranks[(int) $id] = $index + 1;
        }

        return $ranks;
    }

    /**
     * @return Collection<int, ApplicationProgress>
     */
    public static function orderedApplicationsForOffer(int $offreId): Collection
    {
        return ApplicationProgress::query()
            ->where('offre_id', $offreId)
            ->where('status', '!=', 'cancelled')
            ->with(['candidate.user'])
            ->orderByRaw('COALESCE(main_score, 0) DESC')
            ->orderBy('id')
            ->get();
    }
}
