<?php

namespace App\Filament\Candidate\Pages;

use App\Models\Answer;
use App\Models\ApplicationProgress;
use App\Models\Candidate;
use App\Models\CandidateNotification;
use App\Models\Question;
use App\Models\QuestionResponse;
use App\Models\Response;
use App\Filament\Candidate\Concerns\InteractsWithTakeTestAnswers;
use App\Services\CandidateTestSubmissionService;
use App\Services\TestScoringService;
use Filament\Notifications\Notification;

class TakeTest extends Page
{
    use InteractsWithTakeTestAnswers;
    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';
    protected static string $view = 'filament.candidate.pages.take-test';
    protected static ?string $slug = 'take-test';
    protected static bool $shouldRegisterNavigation = false;

    protected function resolveCandidateBackUrl(): string
    {
        return route('filament.candidate.pages.applications');
    }

    public array $answers = [];

    public int $currentLevel = 1;

    public ?int $applicationId = null;

    public string $candidateName = '';

    public bool $hasTest = false;

    public bool $alreadySubmitted = false;

    public string $pageStatus = 'no_application';

    public int $totalQuestions = 0;

    public int $answeredCount = 0;

    /** Unix timestamp for Alpine countdown (whole-test timer). */
    public ?int $wholeTestExpiresAtUnix = null;

    public function mount(): void
    {
        $candidate = Candidate::where('user_id', auth()->id())->first();

        if (! $candidate) {
            $this->redirect('/candidate/dashboard');

            return;
        }

        $this->candidateName = $candidate->full_name ?? auth()->user()->name;
        $this->loadApplication($candidate);
    }

    private function loadApplication(Candidate $candidate): void
    {
        $requestedId = request()->query('application');

        $application = null;
        if ($requestedId !== null && $requestedId !== '' && ctype_digit((string) $requestedId)) {
            $application = ApplicationProgress::query()
                ->where('candidate_id', $candidate->id)
                ->whereKey((int) $requestedId)
                ->whereNotIn('status', ['rejected', 'cancelled'])
                ->first();
        }

        if (! $application) {
            $application = ApplicationProgress::where('candidate_id', $candidate->id)
                ->whereNotIn('status', ['rejected', 'cancelled'])
                ->whereNotNull('test_id')
                ->latest()
                ->first();
        }

        if (! $application) {
            $this->hasTest = false;
            $this->pageStatus = 'no_application';

            return;
        }

        $this->applicationId = $application->id;
        $this->currentLevel = $application->current_level;
        $this->hasTest = true;

        if ($application->status === 'pending') {
            $this->pageStatus = 'waiting_admin';

            return;
        }

        if ($application->isAwaitingTestAssignment()) {
            $this->pageStatus = 'waiting_test_assignment';

            return;
        }

        if (! $application->test_id) {
            $this->pageStatus = 'waiting_test_assignment';

            return;
        }

        if ($application->status === 'validated') {
            $this->pageStatus = 'all_validated';

            return;
        }

        $existingResponse = Response::where('application_id', $application->id)
            ->where('level', $this->currentLevel)
            ->first();

        if ($existingResponse) {
            $this->alreadySubmitted = true;
            $this->pageStatus = app(CandidateTestSubmissionService::class)
                ->resolvePageStatusAfterLoad($application)
                ?? 'waiting_level_validation';

            $existing = QuestionResponse::where('response_id', $existingResponse->id)->get();

            foreach ($existing as $qr) {
                $question = Question::find($qr->question_id);
                $stored = $qr->text_answer ?? $qr->obtained_score;
                $this->answers[$qr->question_id] = $question
                    ? $question->deserializeCandidateAnswer(is_string($stored) ? $stored : null)
                    : $stored;
            }

            $this->ensureMcqAnswerDefaults();

            return;
        }

        $this->pageStatus = 'test';
        $application->loadMissing('test');
        $application->ensureWholeTestSessionDeadline();
        $application->refresh();

        if ($application->wholeTestSessionExpired()) {
            $this->finalizeWholeTestTimerExpired($application);

            return;
        }

        $this->totalQuestions = $this->getQuestions()->count();
        $this->syncWholeTestTimerUi();

        if ($this->totalQuestions === 0) {
            $this->pageStatus = 'no_questions';
        }

        $this->ensureMcqAnswerDefaults();
    }

    public function hydrate(): void
    {
        $this->ensureMcqAnswerDefaults();
    }

    public function getApplication(): ?ApplicationProgress
    {
        return $this->applicationId
            ? ApplicationProgress::with('test')->find($this->applicationId)
            : null;
    }

    public function getQuestions()
    {
        $application = $this->getApplication();

        if (! $application || ! $application->test_id || ! $application->test) {
            return collect();
        }

        return app(TestScoringService::class)
            ->questionsForTestLevel($application->test, $this->currentLevel)
            ->load('answers');
    }

    public function updatedAnswers(): void
    {
        $this->answeredCount = count(array_filter(
            $this->answers,
            fn ($a) => is_array($a) ? count($a) > 0 : $a !== '' && $a !== null
        ));
    }

    /**
     * Server-side safety net if the browser tab is backgrounded or JS stalls.
     */
    public function pollTestTimer(): void
    {
        if ($this->pageStatus !== 'test' || $this->alreadySubmitted) {
            return;
        }

        $application = $this->getApplication()?->fresh();

        if ($application?->wholeTestSessionExpired()) {
            $this->handleTestTimeout();

            return;
        }
    }

    public function handleTestTimeout(): void
    {
        if ($this->pageStatus !== 'test' || $this->alreadySubmitted) {
            return;
        }

        $application = $this->getApplication()?->fresh();

        if (! $application || ! $application->test?->whole_test_timer_enabled) {
            return;
        }

        if (! $application->wholeTestSessionExpired()) {
            return;
        }

        $this->finalizeWholeTestTimerExpired($application);
    }

    private function finalizeWholeTestTimerExpired(ApplicationProgress $application): void
    {
        $application->refresh();

        if ($application->level_status === 'awaiting_approval'
            && Response::where('application_id', $application->id)
                ->where('level', $this->currentLevel)
                ->exists()) {
            $this->alreadySubmitted = true;
            $this->pageStatus = app(CandidateTestSubmissionService::class)
                ->resolvePageStatusAfterLoad($application)
                ?? 'waiting_level_validation';
            $this->wholeTestExpiresAtUnix = null;

            return;
        }

        if ($this->alreadySubmitted) {
            return;
        }

        $this->saveAnswers(false);

        CandidateNotification::create([
            'user_id' => auth()->id(),
            'type' => 'warning',
            'title' => __('candidate.test_timer_expired_notification_title'),
            'message' => __('candidate.test_timer_expired_notification_body', ['level' => $this->currentLevel]),
            'offre_id' => $application->offre_id,
            'application_progress_id' => $application->id,
        ]);

        $response = Response::query()
            ->where('application_id', $application->id)
            ->where('level', $this->currentLevel)
            ->first();

        if ($response) {
            $this->applySubmitOutcome(
                app(CandidateTestSubmissionService::class)->processAfterSubmit($application->fresh(), $response)
            );
        } else {
            $application->update([
                'status' => 'in_progress',
                'level_status' => 'awaiting_approval',
            ]);
            $this->alreadySubmitted = true;
            $this->pageStatus = 'waiting_level_validation';
        }

        $this->wholeTestExpiresAtUnix = null;

        Notification::make()
            ->title(__('candidate.test_timer_expired_title'))
            ->body(__('candidate.test_timer_expired_body'))
            ->warning()
            ->persistent()
            ->send();
    }

    private function syncWholeTestTimerUi(): void
    {
        $this->wholeTestExpiresAtUnix = null;

        if ($this->pageStatus !== 'test' || $this->alreadySubmitted) {
            return;
        }

        $app = $this->getApplication();

        if (! $app?->test?->whole_test_timer_enabled || ! $app->test->whole_test_timer_minutes) {
            return;
        }

        if (! $app->test_session_expires_at) {
            return;
        }

        $this->wholeTestExpiresAtUnix = $app->test_session_expires_at->getTimestamp();
    }

    public function saveAnswers(bool $notifySuccess = true): void
    {
        $application = $this->getApplication();

        if (! $application || $this->alreadySubmitted) {
            return;
        }

        $response = Response::firstOrCreate([
            'application_id' => $application->id,
            'level' => $this->currentLevel,
        ]);

        $scoring = app(TestScoringService::class);

        foreach ($this->answers as $questionId => $answer) {
            $question = Question::find($questionId);
            $autoScore = $question ? $scoring->scoreQuestionPercent($question, $answer) : 0.0;

            $answerId = null;
            if ($question && in_array($question->component, ['radio', 'list'], true) && is_string($answer)) {
                $answerId = Answer::where('question_id', $questionId)->where('text', $answer)->value('id');
            }

            QuestionResponse::updateOrCreate(
                [
                    'response_id' => $response->id,
                    'question_id' => $questionId,
                ],
                [
                    'answer_id' => $answerId,
                    'auto_score' => $autoScore,
                    'manual_score' => 0,
                    'obtained_score' => $autoScore,
                    'text_answer' => $question?->serializeCandidateAnswer($answer),
                ]
            );
        }

        $application->loadMissing('test');
        if ($application->test) {
            $scoring->syncScorableQuestionResponses($response, $application->test, $this->currentLevel);
        }

        $scoring->evaluateResponse($response->fresh(), $application->fresh());

        if ($notifySuccess) {
            Notification::make()
                ->title(__('candidate.answers_saved_title'))
                ->success()
                ->send();
        }
    }

    public function getCurrentLevelResponse(): ?Response
    {
        if (! $this->applicationId) {
            return null;
        }

        return Response::query()
            ->where('application_id', $this->applicationId)
            ->where('level', $this->currentLevel)
            ->first();
    }

    public function getSubmittedTestScorePercent(): ?float
    {
        $score = $this->getCurrentLevelResponse()?->test_score;

        return $score !== null ? (float) $score : null;
    }

    public function getApplicationScorePercent(): ?float
    {
        $app = $this->getApplication();
        if (! $app || $app->main_score === null) {
            return null;
        }

        return (float) $app->main_score;
    }

    public function hasPendingManualReview(): bool
    {
        $response = $this->getCurrentLevelResponse();

        return $response
            ? app(TestScoringService::class)->responseHasPendingManualReview($response)
            : false;
    }

    public function didPassEligibility(): ?bool
    {
        $passed = $this->getCurrentLevelResponse()?->eligibility_passed;

        return $passed === null ? null : (bool) $passed;
    }

    public function getEligibilityThresholdPercent(): ?float
    {
        $test = $this->getApplication()?->test;

        return $test ? (float) ($test->eligibility_threshold ?? 0) : null;
    }

    public function isFullyAutoScoredLevel(): bool
    {
        $application = $this->getApplication();
        $test = $application?->test;

        if (! $test) {
            return false;
        }

        return app(TestScoringService::class)->testLevelIsFullyAutoScored($test, $this->currentLevel);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function applySubmitOutcome(array $result): void
    {
        $outcome = $result['outcome'] ?? CandidateTestSubmissionService::OUTCOME_AWAITING_MANUAL;

        if ($outcome === CandidateTestSubmissionService::OUTCOME_ADVANCED) {
            $application = $this->getApplication()?->fresh(['test']);

            if ($application) {
                $application->ensureWholeTestSessionDeadline();
                $application->refresh();
            }

            $this->currentLevel = (int) ($result['advanced_to_level'] ?? $application?->current_level ?? $this->currentLevel);
            $this->alreadySubmitted = false;
            $this->answers = [];
            $this->ensureMcqAnswerDefaults();
            $this->pageStatus = 'test';
            $this->totalQuestions = $this->getQuestions()->count();
            $this->answeredCount = 0;
            $this->syncWholeTestTimerUi();

            if ($this->totalQuestions === 0) {
                $this->pageStatus = 'no_questions';

                Notification::make()
                    ->title(__('candidate.take_test_no_questions_title'))
                    ->body(__('candidate.take_test_no_questions_body'))
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title(__('candidate.take_test_eligibility_passed_title'))
                ->body(__('candidate.take_test_advanced_to_next', ['level' => $this->currentLevel]))
                ->success()
                ->send();

            return;
        }

        $this->alreadySubmitted = true;
        $this->wholeTestExpiresAtUnix = null;

        $this->pageStatus = match ($outcome) {
            CandidateTestSubmissionService::OUTCOME_ELIGIBILITY_FAILED => 'level_eligibility_failed',
            CandidateTestSubmissionService::OUTCOME_AWAITING_FINAL => 'awaiting_final_validation',
            default => 'waiting_level_validation',
        };
    }

    public function submitLevel(): void
    {
        $application = $this->getApplication()?->fresh();

        if (! $application) {
            return;
        }

        if (empty($this->answers)) {
            Notification::make()
                ->title(__('candidate.answer_at_least_one'))
                ->warning()
                ->send();

            return;
        }

        $this->saveAnswers(false);

        $application->refresh();
        $response = $this->getCurrentLevelResponse();

        if (! $response) {
            return;
        }

        $result = app(CandidateTestSubmissionService::class)->processAfterSubmit($application, $response);

        if (($result['outcome'] ?? null) !== CandidateTestSubmissionService::OUTCOME_ELIGIBILITY_FAILED) {
            CandidateNotification::create([
                'user_id' => auth()->id(),
                'type' => 'info',
                'title' => __('candidate.level_submitted_notification_title', ['level' => $this->currentLevel]),
                'message' => __('candidate.level_submitted_notification_body', ['level' => $this->currentLevel]),
                'offre_id' => $application->offre_id,
                'application_progress_id' => $application->id,
            ]);
        }

        $this->applySubmitOutcome($result);

        if ($this->pageStatus !== 'test') {
            $toast = match ($result['outcome']) {
                CandidateTestSubmissionService::OUTCOME_ELIGIBILITY_FAILED => Notification::make()
                    ->title(__('candidate.take_test_eligibility_failed_title'))
                    ->body(__('candidate.take_test_eligibility_failed_body', [
                        'score' => number_format((float) ($result['test_score'] ?? 0), 2),
                        'threshold' => number_format((float) ($result['eligibility_threshold'] ?? 0), 2),
                    ]))
                    ->danger(),
                CandidateTestSubmissionService::OUTCOME_AWAITING_FINAL => Notification::make()
                    ->title(__('candidate.take_test_eligibility_passed_title'))
                    ->body(__('candidate.take_test_awaiting_final_validation_body'))
                    ->success(),
                default => Notification::make()
                    ->title(__('candidate.level_submitted_title', ['level' => $this->currentLevel]))
                    ->success(),
            };
            $toast->send();
        }
    }
}