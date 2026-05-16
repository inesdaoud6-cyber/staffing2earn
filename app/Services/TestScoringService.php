<?php

namespace App\Services;

use App\Models\ApplicationProgress;
use App\Models\Question;
use App\Models\QuestionResponse;
use App\Models\Response;
use App\Models\Test;
use Illuminate\Support\Collection;

class TestScoringService
{
    public function isAutoScorableComponent(?string $component): bool
    {
        return in_array($component, ['radio', 'list', 'checkbox'], true);
    }

    /**
     * Question score in %: max_note if correct, plus second_ratio bonus if correct.
     */
    public function scoreQuestionPercent(Question $question, mixed $answer): float
    {
        if (! $question->scorable) {
            return 0.0;
        }

        if (! $this->isAutoScorableComponent($question->component)) {
            return 0.0;
        }

        if (! $question->isCandidateAnswerCorrect($answer)) {
            return 0.0;
        }

        return $this->percentForCorrectQuestion($question);
    }

    public function percentForCorrectQuestion(Question $question): float
    {
        return $this->maxPointsForQuestion($question);
    }

    /**
     * Max points this question can contribute to the test total (out of 100).
     */
    public function maxPointsForQuestion(Question $question): float
    {
        $base = max(0, (float) ($question->max_note ?? 0));
        $bonus = max(0, (float) ($question->second_ratio ?? 0));
        $max = $base + $bonus;

        return $max > 0 ? min(100.0, round($max, 2)) : 100.0;
    }

    /**
     * Convert an admin-entered 0–100 % (performance on this question) to test points.
     */
    public function pointsFromManualPercent(Question $question, float $manualPercent): float
    {
        $max = $this->maxPointsForQuestion($question);
        $percent = min(100.0, max(0.0, $manualPercent));

        return min($max, round(($percent / 100.0) * $max, 2));
    }

    /**
     * Express stored test points as a 0–100 % for the admin manual score field.
     */
    public function manualPercentFromPoints(Question $question, float $points): float
    {
        $max = $this->maxPointsForQuestion($question);

        if ($max <= 0) {
            return min(100.0, max(0.0, round($points, 2)));
        }

        return min(100.0, max(0.0, round(($points / $max) * 100.0, 2)));
    }

    /**
     * Test total = sum of per-question points (each question worth up to max_note + bonus).
     *
     * @param  Collection<int, Question>  $questions
     */
    public function totalTestScorePercent(Collection $questions, Collection $questionResponsesByQuestionId): float
    {
        $scorable = $questions->where('scorable', true);

        if ($scorable->isEmpty()) {
            return 0.0;
        }

        $total = 0.0;

        foreach ($scorable as $question) {
            $qr = $questionResponsesByQuestionId->get($question->id);
            $total += $qr
                ? (float) ($qr->obtained_score ?? $qr->auto_score ?? 0)
                : 0.0;
        }

        return min(100.0, round($total, 2));
    }

    /**
     * @param  Collection<int, Question>  $questions
     *
     * @deprecated Use totalTestScorePercent()
     */
    public function averageTestScorePercent(Collection $questions, Collection $questionResponsesByQuestionId): float
    {
        return $this->totalTestScorePercent($questions, $questionResponsesByQuestionId);
    }

    /**
     * @return array{test_score: float, eligibility_passed: bool, talent_passed: bool}
     */
    public function evaluateThresholds(Test $test, float $testScorePercent): array
    {
        $eligibility = (float) ($test->eligibility_threshold ?? 0);
        $talent = (float) ($test->talent_threshold ?? 0);

        return [
            'test_score' => $testScorePercent,
            'eligibility_passed' => $testScorePercent >= $eligibility,
            'talent_passed' => $testScorePercent >= $talent,
        ];
    }

    /**
     * Recompute test score from all scorable questions for this level and persist on response + application.
     */
    public function evaluateResponse(Response $response, ApplicationProgress $application): void
    {
        $application->loadMissing('test');
        $test = $application->test;

        if (! $test) {
            return;
        }

        $level = (int) $response->level;

        $questions = $this->questionsForTestLevel($test, $level);

        $questionResponses = $response->questionResponses()->get()->keyBy('question_id');

        $testScore = $this->totalTestScorePercent($questions, $questionResponses);
        $result = $this->evaluateThresholds($test, $testScore);

        $response->update([
            'test_score' => $result['test_score'],
            'eligibility_passed' => $result['eligibility_passed'],
            'talent_passed' => $result['talent_passed'],
        ]);

        $this->evaluateApplication($application->fresh());
    }

    /**
     * Application score = average of all completed test scores (responses with test_score).
     */
    public function evaluateApplicationScore(ApplicationProgress $application): float
    {
        $scores = Response::query()
            ->where('application_id', $application->id)
            ->whereNotNull('test_score')
            ->pluck('test_score');

        if ($scores->isEmpty()) {
            return 0.0;
        }

        return round((float) $scores->avg(), 2);
    }

    public function evaluateApplication(ApplicationProgress $application): void
    {
        $application->update([
            'main_score' => $this->evaluateApplicationScore($application),
            'secondary_score' => 0,
        ]);
    }

    /**
     * Ensure every scorable question for this level has a row (unanswered / open-ended start at 0 %).
     */
    public function syncScorableQuestionResponses(Response $response, Test $test, int $level): void
    {
        $questions = $this->questionsForTestLevel($test, $level)->where('scorable', true);

        foreach ($questions as $question) {
            QuestionResponse::query()->firstOrCreate(
                [
                    'response_id' => $response->id,
                    'question_id' => $question->id,
                ],
                [
                    'auto_score' => 0,
                    'manual_score' => 0,
                    'obtained_score' => 0,
                ]
            );
        }
    }

    /**
     * True when every scorable question at this test level is auto-graded (QCU/QCM/dropdown).
     */
    public function testLevelIsFullyAutoScored(Test $test, int $level): bool
    {
        $scorable = $this->questionsForTestLevel($test, $level)->where('scorable', true);

        if ($scorable->isEmpty()) {
            return false;
        }

        return $scorable->every(
            fn (Question $question) => $this->isAutoScorableComponent($question->component)
        );
    }

    public function responseHasPendingManualReview(Response $response): bool
    {
        return $response->questionResponses()
            ->whereHas('question', function ($q): void {
                $q->where('scorable', true)
                    ->whereNotIn('component', ['radio', 'list', 'checkbox']);
            })
            ->where('obtained_score', '<=', 0)
            ->exists();
    }

    /**
     * Maps application round (1, 2, 3…) to question.level within a test.
     * Each job-offer test often only has questions at level 1 while rounds still increment.
     */
    public function resolveQuestionLevelForTestRound(Test $test, int $applicationRound): int
    {
        $levels = Question::query()
            ->whereHas('tests', fn ($q) => $q->where('tests.id', $test->id))
            ->distinct()
            ->orderBy('level')
            ->pluck('level')
            ->map(fn ($level): int => (int) $level)
            ->values();

        if ($levels->isEmpty()) {
            return max(1, $applicationRound);
        }

        if ($levels->contains($applicationRound)) {
            return $applicationRound;
        }

        return $levels->first();
    }

    /**
     * @param  Collection<int, Question>  $questions
     */
    public function questionsForTestLevel(Test $test, int $applicationRound): Collection
    {
        $questionLevel = $this->resolveQuestionLevelForTestRound($test, $applicationRound);

        return Question::query()
            ->whereHas('tests', fn ($q) => $q->where('tests.id', $test->id))
            ->where('level', $questionLevel)
            ->get();
    }
}
