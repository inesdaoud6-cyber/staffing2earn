<?php

namespace App\Livewire\Candidate;

use App\Models\Answer;
use App\Models\ApplicationProgress;
use App\Models\Candidate;
use App\Models\Question;
use App\Models\QuestionResponse;
use App\Models\Response;
use App\Filament\Candidate\Concerns\InteractsWithTakeTestAnswers;
use App\Services\CandidateTestSubmissionService;
use App\Services\TestScoringService;
use App\Services\CandidateService;
use App\Services\NotificationService;
use Livewire\Component;

class TakeTestComponent extends Component
{
    use InteractsWithTakeTestAnswers;
    public array $answers = [];

    public int $currentLevel = 1;

    public ?int $applicationId = null;

    public string $candidateName = '';

    public bool $hasTest = false;

    public bool $alreadySubmitted = false;

    public string $pageStatus = 'no_application';

    public int $totalQuestions = 0;

    public int $answeredCount = 0;

    private ?ApplicationProgress $cachedApplication = null;

    private $cachedQuestions = null;

    public function mount(CandidateService $service): void
    {
        $user = auth()->user();
        $candidate = $service->getCandidateByUser($user);

        if (! $candidate) {
            $this->pageStatus = 'no_application';

            return;
        }

        $this->candidateName = $candidate->full_name ?? $user->name;
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

            QuestionResponse::where('response_id', $existingResponse->id)
                ->get()
                ->each(function ($qr) {
                    $question = Question::find($qr->question_id);
                    $stored = $qr->text_answer ?? $qr->obtained_score;
                    $this->answers[$qr->question_id] = $question
                        ? $question->deserializeCandidateAnswer(is_string($stored) ? $stored : null)
                        : $stored;
                });

            $this->ensureMcqAnswerDefaults();

            return;
        }

        $this->pageStatus = 'take_test';
        $this->totalQuestions = $this->getQuestions()->count();
        $this->ensureMcqAnswerDefaults();
    }

    public function hydrate(): void
    {
        $this->ensureMcqAnswerDefaults();
    }

    public function getApplication(): ?ApplicationProgress
    {
        if ($this->cachedApplication && $this->cachedApplication->id === $this->applicationId) {
            return $this->cachedApplication;
        }

        $this->cachedApplication = $this->applicationId
            ? ApplicationProgress::with('test')->find($this->applicationId)
            : null;

        return $this->cachedApplication;
    }

    public function getQuestions()
    {
        if ($this->cachedQuestions !== null) {
            return $this->cachedQuestions;
        }

        $application = $this->getApplication();

        if (! $application || ! $application->test_id) {
            return collect();
        }

        $application->loadMissing('test');

        $this->cachedQuestions = $application->test
            ? app(TestScoringService::class)
                ->questionsForTestLevel($application->test, $this->currentLevel)
                ->load('answers')
            : collect();

        return $this->cachedQuestions;
    }

    public function updatedAnswers(): void
    {
        $this->answeredCount = count(array_filter(
            $this->answers,
            fn ($a) => is_array($a) ? count($a) > 0 : $a !== '' && $a !== null
        ));
    }

    public function saveAnswers(): void
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
                ['response_id' => $response->id, 'question_id' => $questionId],
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
    }

    public function getCurrentLevelResponse(): ?Response
    {
        $application = $this->getApplication();

        if (! $application) {
            return null;
        }

        return Response::query()
            ->where('application_id', $application->id)
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

        return $app && $app->main_score !== null ? (float) $app->main_score : null;
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
            $this->currentLevel = (int) ($result['advanced_to_level'] ?? $this->currentLevel);
            $this->alreadySubmitted = false;
            $this->answers = [];
            $this->pageStatus = 'take_test';
            $this->cachedApplication = null;
            $this->cachedQuestions = null;
            $this->totalQuestions = $this->getQuestions()->count();
            $this->answeredCount = 0;

            $this->dispatch('notify', type: 'success', message: __('candidate.take_test_advanced_to_next', ['level' => $this->currentLevel]));

            return;
        }

        $this->alreadySubmitted = true;
        $this->cachedApplication = null;
        $this->cachedQuestions = null;

        $this->pageStatus = match ($outcome) {
            CandidateTestSubmissionService::OUTCOME_ELIGIBILITY_FAILED => 'level_eligibility_failed',
            CandidateTestSubmissionService::OUTCOME_AWAITING_FINAL => 'awaiting_final_validation',
            default => 'waiting_level_validation',
        };
    }

    public function submitLevel(): void
    {
        $application = $this->getApplication();

        if (! $application || $this->alreadySubmitted) {
            return;
        }

        if (empty($this->answers)) {
            $this->dispatch('notify', type: 'warning', message: __('Veuillez répondre à au moins une question.'));

            return;
        }

        $this->saveAnswers();

        $application->refresh();
        $response = $this->getCurrentLevelResponse();

        if (! $response) {
            return;
        }

        $result = app(CandidateTestSubmissionService::class)->processAfterSubmit($application, $response);

        app(NotificationService::class)->sendLevelSubmitted(
            auth()->user(),
            $this->currentLevel,
            $application->offre_id
        );

        $this->applySubmitOutcome($result);

        if ($this->pageStatus !== 'take_test') {
            $message = match ($result['outcome']) {
                CandidateTestSubmissionService::OUTCOME_ELIGIBILITY_FAILED => __('candidate.take_test_eligibility_failed_body', [
                    'score' => number_format((float) ($result['test_score'] ?? 0), 2),
                    'threshold' => number_format((float) ($result['eligibility_threshold'] ?? 0), 2),
                ]),
                CandidateTestSubmissionService::OUTCOME_AWAITING_FINAL => __('candidate.take_test_awaiting_final_validation_body'),
                default => __('candidate.level_submitted_title', ['level' => $this->currentLevel]),
            };
            $type = $result['outcome'] === CandidateTestSubmissionService::OUTCOME_ELIGIBILITY_FAILED
                ? 'error'
                : 'success';
            $this->dispatch('notify', type: $type, message: $message);
        }
    }

    public function render()
    {
        return view('livewire.candidate.take-test-component');
    }
}
