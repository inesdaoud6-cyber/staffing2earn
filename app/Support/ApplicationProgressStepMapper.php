<?php

namespace App\Support;

use App\Models\ApplicationProgress;
use App\Models\Response;
use App\Models\Test;

/**
 * Unified pipeline indexing for candidate progress and admin review.
 *
 * Offer step 1 = CV only (not a test).
 * Offer step 2+ = test round (test number = offer step − 1, stored as response/application level).
 */
class ApplicationProgressStepMapper
{
    public const CV_OFFER_STEP = 1;

    public static function isCvOfferStep(int $offerStep): bool
    {
        return $offerStep === self::CV_OFFER_STEP;
    }

    public static function testNumberFromOfferStep(int $offerStep): int
    {
        return max(1, $offerStep - 1);
    }

    public static function responseLevelFromOfferStep(int $offerStep): int
    {
        return max(1, $offerStep - 1);
    }

    public static function offerStepFromResponseLevel(int $responseLevel): int
    {
        return $responseLevel + 1;
    }

    /**
     * Admin review URL / sub-nav parameter (same integer as candidate offer_step).
     */
    public static function reviewPageStepForResponseLevel(int $responseLevel): int
    {
        return self::offerStepFromResponseLevel($responseLevel);
    }

    public static function reviewPageStepForCurrentTestRound(ApplicationProgress $app): int
    {
        return self::offerStepFromResponseLevel(max(1, (int) $app->current_level));
    }

    public static function resolveResponseForOfferStep(ApplicationProgress $app, int $offerStep): ?Response
    {
        if ($offerStep <= self::CV_OFFER_STEP) {
            return null;
        }

        return Response::query()
            ->where('application_id', $app->id)
            ->where('level', self::responseLevelFromOfferStep($offerStep))
            ->first();
    }

    public static function resolveTestForOfferStep(ApplicationProgress $app, int $offerStep): ?Test
    {
        if ($offerStep <= self::CV_OFFER_STEP) {
            return null;
        }

        $app->loadMissing('offre', 'test');

        if ($app->offre) {
            $testId = $app->offre->testIdForLevel($offerStep);

            return $testId ? Test::query()->find($testId) : null;
        }

        return $app->test;
    }

    public static function assessmentStepCount(ApplicationProgress $app): int
    {
        $app->loadMissing('offre');

        if ($app->offre) {
            $offre = $app->offre;
            $fromLevels = max(2, (int) $offre->levels_count);
            $configuredTests = count(array_filter(
                array_values($offre->level_test_ids ?? []),
                fn ($id) => (int) $id > 0
            ));

            return min(20, max($fromLevels, 1 + $configuredTests));
        }

        if ($app->isFreeApplication()) {
            return self::freeApplicationAssessmentStepCount($app);
        }

        $fromResponses = (int) ($app->responses()->max('level') ?? 0);
        $fromCurrent = (int) $app->current_level;

        return min(20, max(2, $fromResponses + 2, $fromCurrent + 1));
    }

    private static function freeApplicationAssessmentStepCount(ApplicationProgress $app): int
    {
        $submittedRounds = (int) $app->responses()->count();

        $hasOpenRound = $app->test_id
            && $app->status === 'in_progress'
            && $app->level_status === 'in_progress'
            && ! $app->responses()
                ->where('level', $app->current_level)
                ->exists();

        $testRounds = $submittedRounds + ($hasOpenRound ? 1 : 0);

        if ($app->status !== 'pending' && $testRounds < 1 && $app->test_id) {
            $testRounds = 1;
        }

        return min(20, max(2, 1 + $testRounds));
    }

    /**
     * Clamp admin/candidate pipeline step to a valid offer step (never remap CV → test).
     */
    public static function normalizeReviewPageStep(ApplicationProgress $app, int $requestedStep): int
    {
        $max = self::assessmentStepCount($app);

        return max(self::CV_OFFER_STEP, min($max, $requestedStep));
    }
}
