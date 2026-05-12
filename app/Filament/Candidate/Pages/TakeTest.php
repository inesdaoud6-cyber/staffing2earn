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
use Filament\Pages\Page;

class TakeTest extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';
    protected static string $view = 'filament.candidate.pages.take-test';
    protected static ?string $title = 'Passer le Test';
    protected static ?string $slug = 'take-test';
    protected static bool $shouldRegisterNavigation = false;

    public array $answers = [];
    public int $currentLevel = 1;
    public ?int $applicationId = null;
    public string $candidateName = '';
    public bool $hasTest = false;
    public bool $alreadySubmitted = false;
    public string $pageStatus = 'no_application';
    public int $totalQuestions = 0;
    public int $answeredCount = 0;

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
        $application = ApplicationProgress::where('candidate_id', $candidate->id)
            ->whereNotIn('status', ['rejected'])
            ->whereNotNull('test_id')
            ->latest()
            ->first();

        if (! $application) {
            $this->hasTest = false;
            $this->pageStatus = 'no_application';
            return;
        }

        $this->applicationId = $application->id;
        $this->currentLevel  = $application->current_level;
        $this->hasTest       = true;

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

            $existing = QuestionResponse::where('response_id', $existingResponse->id)->get();

            foreach ($existing as $qr) {
                $this->answers[$qr->question_id] = $qr->text_answer ?? $qr->obtained_score;
            }
        } else {
            $this->pageStatus = 'test';
            $this->totalQuestions = $this->getQuestions()->count();
        }
    }

    public function getApplication(): ?ApplicationProgress
    {
        return $this->applicationId
            ? ApplicationProgress::find($this->applicationId)
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
            fn ($a) => $a !== '' && $a !== null
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
            'level'          => $this->currentLevel,
        ]);

        $mainScore      = 0;
        $secondaryScore = 0;

        foreach ($this->answers as $questionId => $answer) {
            $question  = Question::find($questionId);
            $autoScore = 0;

            if ($question && $question->scorable) {
                if (in_array($question->component, ['radio', 'list'])) {
                    $correctAnswer = Answer::where('question_id', $questionId)
                        ->where('is_correct', true)
                        ->first();

                    if ($correctAnswer && $correctAnswer->text === $answer) {
                        $autoScore = $question->max_note ?? 0;
                    }
                }

                if ($question->classification === 'primary') {
                    $mainScore += $autoScore;
                } else {
                    $secondaryScore += $autoScore;
                }
            }

            $answerId = in_array($question?->component, ['radio', 'list'])
                ? Answer::where('question_id', $questionId)->where('text', $answer)->value('id')
                : null;

            QuestionResponse::updateOrCreate(
                [
                    'response_id' => $response->id,
                    'question_id' => $questionId,
                ],
                [
                    'answer_id'      => $answerId,
                    'auto_score'     => $autoScore,
                    'manual_score'   => 0,
                    'obtained_score' => $autoScore,
                    'text_answer'    => is_string($answer) ? $answer : null,
                ]
            );
        }

        $application->update([
            'main_score'      => $mainScore,
            'secondary_score' => $secondaryScore,
        ]);

        Notification::make()
            ->title('Réponses sauvegardées !')
            ->success()
            ->send();
    }

    public function submitLevel(): void
    {
        $application = $this->getApplication();

        if (! $application) {
            return;
        }

        if (empty($this->answers)) {
            Notification::make()
                ->title('Veuillez répondre à au moins une question.')
                ->warning()
                ->send();
            return;
        }

        $this->saveAnswers();

        $application->update(['status' => 'in_progress']);

        CandidateNotification::create([
            'user_id' => auth()->id(),
            'type'    => 'info',
            'title'   => 'Niveau ' . $this->currentLevel . ' soumis',
            'message' => 'Votre niveau ' . $this->currentLevel . ' a été soumis. En attente de validation par l\'administrateur.',
            'offre_id' => $application->offre_id,
        ]);

        $this->alreadySubmitted = true;
        $this->pageStatus = 'waiting_level_validation';

        Notification::make()
            ->title('Niveau ' . $this->currentLevel . ' soumis avec succès !')
            ->success()
            ->send();
    }
}