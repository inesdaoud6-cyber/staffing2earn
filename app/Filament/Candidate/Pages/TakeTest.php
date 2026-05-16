<?php

namespace App\Filament\Candidate\Pages;

use App\Models\Answer;
use App\Models\ApplicationProgress;
use App\Models\Candidate;
use App\Models\CandidateNotification;
use App\Models\Question;
use App\Models\QuestionResponse;
use App\Models\Response;
use Filament\Notifications\Notification;
class TakeTest extends Page
{
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
            $this->pageStatus = 'waiting_level_validation';

            $existing = QuestionResponse::where('response_id', $existingResponse->id)->get();

            foreach ($existing as $qr) {
                $question = Question::find($qr->question_id);
                $stored = $qr->text_answer ?? $qr->obtained_score;
                $this->answers[$qr->question_id] = $question
                    ? $question->deserializeCandidateAnswer(is_string($stored) ? $stored : null)
                    : $stored;
            }

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

        if (! $application || ! $application->test_id) {
            return collect();
        }

        return Question::whereHas('tests', function ($q) use ($application) {
            $q->where('tests.id', $application->test_id);
        })
            ->where('level', $this->currentLevel)
            ->with('answers')
            ->get();
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
            $this->pageStatus = 'waiting_level_validation';
            $this->wholeTestExpiresAtUnix = null;

            return;
        }

        if ($this->alreadySubmitted) {
            return;
        }

        $this->saveAnswers(false);

        $application->refresh();
        $application->update([
            'status' => 'in_progress',
            'level_status' => 'awaiting_approval',
        ]);

        CandidateNotification::create([
            'user_id' => auth()->id(),
            'type' => 'warning',
            'title' => __('candidate.test_timer_expired_notification_title'),
            'message' => __('candidate.test_timer_expired_notification_body', ['level' => $this->currentLevel]),
            'offre_id' => $application->offre_id,
        ]);

        $this->alreadySubmitted = true;
        $this->pageStatus = 'waiting_level_validation';
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

        $mainScore = 0;
        $secondaryScore = 0;

        foreach ($this->answers as $questionId => $answer) {
            $question = Question::find($questionId);
            $autoScore = 0;

            if ($question && $question->scorable) {
                $autoScore = $question->scoreCandidateAnswer($answer);

                if ($question->classification === 'primary') {
                    $mainScore += $autoScore;
                } else {
                    $secondaryScore += $autoScore;
                }
            }

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

        $application->update([
            'main_score' => round($mainScore, 2),
            'secondary_score' => round($secondaryScore, 2),
        ]);

        if ($notifySuccess) {
            Notification::make()
                ->title(__('candidate.answers_saved_title'))
                ->success()
                ->send();
        }
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
        $application->update([
            'status' => 'in_progress',
            'level_status' => 'awaiting_approval',
        ]);

        CandidateNotification::create([
            'user_id' => auth()->id(),
            'type' => 'info',
            'title' => __('candidate.level_submitted_notification_title', ['level' => $this->currentLevel]),
            'message' => __('candidate.level_submitted_notification_body', ['level' => $this->currentLevel]),
            'offre_id' => $application->offre_id,
        ]);

        $this->alreadySubmitted = true;
        $this->pageStatus = 'waiting_level_validation';
        $this->wholeTestExpiresAtUnix = null;

        Notification::make()
            ->title(__('candidate.level_submitted_title', ['level' => $this->currentLevel]))
            ->success()
            ->send();
    }
}