<?php

namespace App\Livewire\Candidate;

use App\Models\Answer;
use App\Models\ApplicationProgress;
use App\Models\Candidate;
use App\Models\Question;
use App\Models\QuestionResponse;
use App\Models\Response;
use App\Services\CandidateService;
use App\Services\NotificationService;
use Livewire\Component;

class TakeTestComponent extends Component
{
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
            $this->pageStatus = 'waiting_level_validation';

            QuestionResponse::where('response_id', $existingResponse->id)
                ->get()
                ->each(function ($qr) {
                    $question = Question::find($qr->question_id);
                    $stored = $qr->text_answer ?? $qr->obtained_score;
                    $this->answers[$qr->question_id] = $question
                        ? $question->deserializeCandidateAnswer(is_string($stored) ? $stored : null)
                        : $stored;
                });

            return;
        }

        $this->pageStatus = 'take_test';
        $this->totalQuestions = $this->getQuestions()->count();
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

        $this->cachedQuestions = Question::whereHas('tests', function ($q) use ($application) {
            $q->where('tests.id', $application->test_id);
        })
            ->where('level', $this->currentLevel)
            ->with('answers')
            ->get();

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

        $application->update([
            'main_score' => round($mainScore, 2),
            'secondary_score' => round($secondaryScore, 2),
        ]);
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

        $application->update([
            'status' => 'in_progress',
            'level_status' => 'awaiting_approval',
        ]);

        app(NotificationService::class)->sendLevelSubmitted(
            auth()->user(),
            $this->currentLevel,
            $application->offre_id
        );

        $this->alreadySubmitted = true;
        $this->pageStatus = 'waiting_level_validation';
        $this->cachedApplication = null;
        $this->cachedQuestions = null;

        $this->dispatch('notify', type: 'success', message: __('Niveau :n soumis avec succès !', ['n' => $this->currentLevel]));
    }

    public function render()
    {
        return view('livewire.candidate.take-test-component');
    }
}
