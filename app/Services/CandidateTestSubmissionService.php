<?php

namespace App\Services;

use App\Filament\Resources\ApplicationProgressResource;
use App\Models\ApplicationProgress;
use App\Models\Response;
use App\Models\Test;

class CandidateTestSubmissionService
{
    public const OUTCOME_AWAITING_MANUAL = 'awaiting_manual';

    public const OUTCOME_ELIGIBILITY_FAILED = 'eligibility_failed';

    public const OUTCOME_ADVANCED = 'advanced';

    public const OUTCOME_AWAITING_FINAL = 'awaiting_final';

    /**
     * After answers are saved and scores computed, apply pipeline rules for fully auto-scored tests.
     *
     * @return array{
     *     outcome: string,
     *     test_score: float|null,
     *     eligibility_passed: bool|null,
     *     eligibility_threshold: float|null,
     *     application_score: float|null,
     *     advanced_to_level: int|null
     * }
     */
    public function processAfterSubmit(ApplicationProgress $application, Response $response): array
    {
        $application->loadMissing('test', 'offre');
        $response->refresh();
        $test = $application->test;

        $testScore = $response->test_score !== null ? (float) $response->test_score : null;
        $eligibilityPassed = $response->eligibility_passed;
        $threshold = $test ? (float) ($test->eligibility_threshold ?? 0) : null;

        $base = [
            'test_score' => $testScore,
            'eligibility_passed' => $eligibilityPassed,
            'eligibility_threshold' => $threshold,
            'application_score' => $application->fresh()->main_score !== null
                ? (float) $application->main_score
                : null,
            'advanced_to_level' => null,
        ];

        $scoring = app(TestScoringService::class);

        if (! $test || ! $scoring->testLevelIsFullyAutoScored($test, (int) $response->level)) {
            $application->update([
                'status' => 'in_progress',
                'level_status' => 'awaiting_approval',
            ]);

            return array_merge($base, ['outcome' => self::OUTCOME_AWAITING_MANUAL]);
        }

        if (! $eligibilityPassed) {
            $application->update([
                'status' => 'in_progress',
                'level_status' => 'rejected',
            ]);

            return array_merge($base, ['outcome' => self::OUTCOME_ELIGIBILITY_FAILED]);
        }

        if ($this->hasNextTestRound($application)) {
            ApplicationProgressResource::advanceToNextLevel(
                $application->fresh(),
                notifyCandidate: true,
                notifyAdmin: false,
            );
            $application->refresh();

            return array_merge($base, [
                'outcome' => self::OUTCOME_ADVANCED,
                'advanced_to_level' => (int) $application->current_level,
                'application_score' => $application->main_score !== null
                    ? (float) $application->main_score
                    : null,
            ]);
        }

        $application->update([
            'status' => 'in_progress',
            'level_status' => 'awaiting_approval',
        ]);

        return array_merge($base, ['outcome' => self::OUTCOME_AWAITING_FINAL]);
    }

    public function hasNextTestRound(ApplicationProgress $application): bool
    {
        if ($application->isFreeApplication()) {
            return false;
        }

        $offre = $application->offre;
        if (! $offre) {
            return false;
        }

        $current = (int) $application->current_level;

        return $offre->testIdForLevel($current + 2) !== null;
    }

    public function resolvePageStatusAfterLoad(ApplicationProgress $application): ?string
    {
        $response = Response::query()
            ->where('application_id', $application->id)
            ->where('level', $application->current_level)
            ->first();

        if (! $response) {
            return null;
        }

        if ($application->level_status === 'rejected') {
            return 'level_eligibility_failed';
        }

        if ($application->level_status !== 'awaiting_approval') {
            return 'waiting_level_validation';
        }

        $test = $application->test;
        $scoring = app(TestScoringService::class);

        if ($test && $scoring->testLevelIsFullyAutoScored($test, (int) $response->level)) {
            if (! $this->hasNextTestRound($application) && $response->eligibility_passed) {
                return 'awaiting_final_validation';
            }
        }

        return 'waiting_level_validation';
    }
}
