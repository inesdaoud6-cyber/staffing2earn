<?php

namespace App\Filament\Candidate\Pages;

use App\Models\ApplicationProgress;
use App\Models\Candidate;
use App\Models\Offre;
use App\Models\Question;
use App\Models\QuestionResponse;
use App\Models\Response;
use App\Models\Test;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;

class ApplicationSpace extends Page
{
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public const APPLICATIONS_PER_PAGE = 12;

    protected static string $view = 'filament.candidate.pages.application-space';

    protected static ?string $slug = 'applications';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'candidate.main';

    public static function getNavigationLabel(): string
    {
        return __('nav.my_applications');
    }

    public function getTitle(): string
    {
        if ($this->applicationView === 'details' && ($app = $this->getSelectedApplication())) {
            return $this->applicationCardTitle($app).' — '.__('candidate.applications.details_heading');
        }

        if ($this->applicationView === 'progress' && ($app = $this->getSelectedApplication())) {
            return $this->applicationCardTitle($app).' — '.__('candidate.applications.progress_heading');
        }

        return __('nav.my_applications');
    }

    public string $candidateName = '';

    public int $totalApplications = 0;

    public float $averageScore = 0;

    public ?int $candidateId = null;

    public bool $isAdminViewing = false;

    public string $filterStatus = '';

    public ?int $selectedApplicationId = null;

    /** @var 'list'|'details'|'progress' */
    public string $applicationView = 'list';

    public int $selectedOfferStep = 1;

    public bool $cvPreviewVisible = false;

    public function mount(): void
    {
        $this->loadApplications();
    }

    public function loadApplications(): void
    {
        $user = auth()->user();
        $candidate = Candidate::where('user_id', $user->id)->first();

        $this->isAdminViewing = $user->can('view-candidate-scores');
        $this->candidateName = $candidate
            ? trim($candidate->first_name.' '.$candidate->last_name) ?: $user->name
            : $user->name;

        $this->candidateId = $candidate?->id;

        if (! $candidate) {
            $this->totalApplications = 0;
            $this->averageScore = 0;

            return;
        }

        $query = $this->applicationsQuery();
        $this->totalApplications = (clone $query)->count();
        $this->averageScore = round((clone $query)->avg('main_score') ?? 0, 2);

        if ($this->selectedApplicationId && ! (clone $query)->where('id', $this->selectedApplicationId)->exists()) {
            $this->clearApplicationSelection();
        }
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage('applicationsPage');
        $this->loadApplications();
    }

    public function getApplicationsProperty(): LengthAwarePaginator
    {
        return $this->applicationsQuery()
            ->with(['offre', 'test'])
            ->orderByDesc('updated_at')
            ->paginate(self::APPLICATIONS_PER_PAGE, pageName: 'applicationsPage');
    }

    protected function applicationsQuery(): Builder
    {
        if (! $this->candidateId) {
            return ApplicationProgress::query()->whereRaw('0 = 1');
        }

        return ApplicationProgress::query()
            ->where('candidate_id', $this->candidateId)
            ->where('status', '!=', 'cancelled')
            ->when($this->filterStatus !== '', fn (Builder $q): Builder => $q->where('status', $this->filterStatus));
    }

    public function applicationCardTitle(ApplicationProgress $app): string
    {
        return $app->offre?->title ?? __('Open application');
    }

    public function applicationCardTypeLabel(ApplicationProgress $app): string
    {
        return $app->offre_id
            ? __('candidate.applications.type_job_offer')
            : __('candidate.applications.type_free');
    }

    public function showApplicationDetails(int $id): void
    {
        $this->selectedApplicationId = $id;
        $this->applicationView = 'details';
        $this->selectedOfferStep = 1;
        $this->cvPreviewVisible = false;
    }

    public function showApplicationProgress(int $id): void
    {
        $this->selectedApplicationId = $id;
        $this->applicationView = 'progress';
        $this->cvPreviewVisible = false;
        $this->selectedOfferStep = $this->resolveDefaultOfferStep();
    }

    public function clearApplicationSelection(): void
    {
        $this->selectedApplicationId = null;
        $this->applicationView = 'list';
        $this->selectedOfferStep = 1;
        $this->cvPreviewVisible = false;
    }

    public function showCvPreview(): void
    {
        $this->cvPreviewVisible = true;
    }

    public function hideCvPreview(): void
    {
        $this->cvPreviewVisible = false;
    }

    public function applicationStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => __('Pending'),
            'in_progress' => __('In Progress'),
            'validated' => __('Validated'),
            'rejected' => __('Rejected'),
            default => $status,
        };
    }

    public function canCancelApplication(ApplicationProgress $app): bool
    {
        return ! $this->isAdminViewing && in_array($app->status, ['pending', 'in_progress'], true);
    }

    /**
     * @return array{
     *     application: ApplicationProgress,
     *     is_free: bool,
     *     offre: ?Offre,
     *     tests_count: int,
     *     assessment_levels: int,
     *     tests: list<array{step: int, label: string, name: string}>,
     *     total_applicants: int,
     *     other_applicants: int,
     * }|null
     */
    public function getApplicationDetailsPageData(): ?array
    {
        $application = $this->getSelectedApplication();
        if (! $application) {
            return null;
        }

        $application->loadMissing(['offre', 'test']);

        if (! $application->offre_id || ! $application->offre) {
            $freeQuery = ApplicationProgress::query()
                ->whereNull('offre_id')
                ->where('status', '!=', 'cancelled');

            return [
                'application' => $application,
                'is_free' => true,
                'offre' => null,
                'tests_count' => 0,
                'assessment_levels' => 0,
                'tests' => [],
                'total_applicants' => (int) (clone $freeQuery)->count(),
                'other_applicants' => (int) (clone $freeQuery)
                    ->where('candidate_id', '!=', $this->candidateId)
                    ->count(),
            ];
        }

        $offre = $application->offre;
        $tests = $this->resolveOfferTestsList($offre);
        $applicantsQuery = ApplicationProgress::query()
            ->where('offre_id', $offre->id)
            ->where('status', '!=', 'cancelled');

        return [
            'application' => $application,
            'is_free' => false,
            'offre' => $offre,
            'tests_count' => count($tests),
            'assessment_levels' => max(1, (int) $offre->levels_count),
            'tests' => $tests,
            'total_applicants' => (int) (clone $applicantsQuery)->count(),
            'other_applicants' => (int) (clone $applicantsQuery)
                ->where('candidate_id', '!=', $this->candidateId)
                ->count(),
        ];
    }

    /**
     * @return list<array{step: int, label: string, name: string}>
     */
    private function resolveOfferTestsList(Offre $offre): array
    {
        $tests = [];
        $maxLevel = max(2, min(20, (int) $offre->levels_count));

        for ($level = 2; $level <= $maxLevel; $level++) {
            $testId = $offre->testIdForLevel($level);
            if (! $testId) {
                continue;
            }

            $test = Test::query()->find($testId);
            $tests[] = [
                'step' => $level - 1,
                'label' => (string) __('candidate.applications.caption_test', ['n' => $level - 1]),
                'name' => $test?->name ?? ('#'.(int) $testId),
            ];
        }

        return $tests;
    }

    public function setSelectedOfferStep(int $step): void
    {
        $this->selectedOfferStep = max(1, $step);

        if ($this->selectedOfferStep !== 1) {
            $this->cvPreviewVisible = false;
        }
    }

    /**
     * Open progress on the step that matches where the candidate is in the journey.
     */
    public function resolveDefaultOfferStep(?ApplicationProgress $app = null): int
    {
        $app ??= $this->getSelectedApplication();
        if (! $app) {
            return 1;
        }

        if ($app->isAwaitingTestAssignment()) {
            return 1;
        }

        $steps = $this->getStepperSteps();
        if ($steps === []) {
            return 1;
        }

        foreach (['current', 'waiting'] as $preferredState) {
            foreach ($steps as $step) {
                if (($step['state'] ?? '') === $preferredState) {
                    return (int) $step['offer_step'];
                }
            }
        }

        if (in_array($app->status, ['validated', 'rejected'], true)) {
            foreach ($steps as $step) {
                if (($step['kind'] ?? '') === 'decision') {
                    return (int) $step['offer_step'];
                }
            }
        }

        $lastCompleted = 1;
        foreach ($steps as $step) {
            if (($step['state'] ?? '') === 'completed') {
                $lastCompleted = (int) $step['offer_step'];
            }
        }

        return $lastCompleted;
    }

    public function getSelectedApplication(): ?ApplicationProgress
    {
        if (! $this->selectedApplicationId || ! $this->candidateId) {
            return null;
        }

        return ApplicationProgress::query()
            ->where('candidate_id', $this->candidateId)
            ->where('status', '!=', 'cancelled')
            ->with(['offre', 'test', 'responses'])
            ->find($this->selectedApplicationId);
    }

    public function takeTestUrl(?ApplicationProgress $app = null): string
    {
        $app ??= $this->getSelectedApplication();
        if (! $app) {
            return '/candidate/take-test';
        }

        return '/candidate/take-test?application='.$app->id;
    }

    public function hasTestResponse(ApplicationProgress $app, int $responseLevel): bool
    {
        return Response::query()
            ->where('application_id', $app->id)
            ->where('level', $responseLevel)
            ->exists();
    }

    public function isTestStepWritable(ApplicationProgress $app, int $responseLevel): bool
    {
        if ($this->hasTestResponse($app, $responseLevel)) {
            return false;
        }

        return $app->status === 'in_progress'
            && $app->level_status === 'in_progress'
            && $app->current_level === $responseLevel
            && (bool) $app->test_id;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAssessmentStepCount(?ApplicationProgress $app = null): int
    {
        $app ??= $this->getSelectedApplication();
        if (! $app) {
            return 1;
        }

        return $this->resolveAssessmentStepCount($app);
    }

    public function stepStateLabel(string $state): string
    {
        return match ($state) {
            'completed' => __('candidate.applications.pipeline_done'),
            'current' => __('candidate.applications.pipeline_current'),
            'waiting' => __('candidate.applications.pipeline_review'),
            'rejected' => __('candidate.applications.pipeline_rejected'),
            default => __('candidate.applications.pipeline_upcoming'),
        };
    }

    public function isFinalDecisionStep(int $offerStep, ?ApplicationProgress $app = null): bool
    {
        $app ??= $this->getSelectedApplication();
        if (! $app) {
            return false;
        }

        return $offerStep > $this->resolveAssessmentStepCount($app);
    }

    public function isFreeAwaitingTestAssignment(?ApplicationProgress $app = null): bool
    {
        $app ??= $this->getSelectedApplication();

        return $app?->isAwaitingTestAssignment() ?? false;
    }

    public function getStepperSteps(): array
    {
        $app = $this->getSelectedApplication();
        if (! $app) {
            return [];
        }

        $app->loadMissing(['offre', 'test', 'responses']);

        $assessmentTotal = $this->resolveAssessmentStepCount($app);
        $steps = [];

        for ($offerStep = 1; $offerStep <= $assessmentTotal; $offerStep++) {
            if ($offerStep === 1) {
                $steps[] = $this->buildCvStep($app, $offerStep);
            } else {
                $steps[] = $this->buildTestStep($app, $offerStep);
            }
        }

        if ($app->offre_id || $app->isFreeApplication()) {
            $steps[] = $this->buildFinalDecisionStep($app, $assessmentTotal + 1);
        }

        foreach ($steps as $index => $step) {
            if ($index >= count($steps) - 1) {
                break;
            }

            $steps[$index]['line_state'] = $this->resolveConnectorLineState($step['state'] ?? 'future');
        }

        return $steps;
    }

    public function resolveResponseForOfferStep(ApplicationProgress $app, int $offerStep): ?Response
    {
        if ($offerStep <= 1) {
            return null;
        }

        $testIndex = $offerStep - 2;

        return Response::query()
            ->where('application_id', $app->id)
            ->orderBy('level')
            ->orderBy('id')
            ->skip($testIndex)
            ->first();
    }

    public function formatPublishedScore(?float $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    }

    public function formatCandidateQuestionScore(ApplicationProgress $app, QuestionResponse $qr): ?string
    {
        if (! $app->score_published) {
            return null;
        }

        $question = $qr->question;
        if ($question && ! $question->scorable) {
            return null;
        }

        $value = $qr->obtained_score ?? $qr->auto_score;

        return $this->formatPublishedScore($value !== null ? (float) $value : null);
    }

    public function publishedMainScoreLabel(ApplicationProgress $app): ?string
    {
        if (! $app->score_published) {
            return null;
        }

        $formatted = $this->formatPublishedScore((float) $app->main_score);

        return ($formatted !== null && $formatted !== '' ? $formatted : '0').'/100';
    }

    /**
     * @return list<array{question: string, answer: string, score: ?string}>
     */
    public function getTestReviewRows(ApplicationProgress $app, int $responseLevel): array
    {
        $offerStep = $responseLevel + 1;
        $response = $this->resolveResponseForOfferStep($app, $offerStep);

        if (! $response) {
            return [];
        }

        $response->loadMissing(['questionResponses.question', 'questionResponses.answer']);

        $locale = app()->getLocale();
        $field = match ($locale) {
            'ar' => 'question_ar',
            'fr' => 'question_fr',
            default => 'question_en',
        };

        $rows = [];

        foreach ($response->questionResponses->sortBy('question_id') as $qr) {
            $q = $qr->question;
            $qtext = $q
                ? (string) ($q->{$field} ?? $q->question_en ?? $q->question_fr ?? '')
                : '';

            $answer = trim((string) ($qr->text_answer ?? ''));
            if ($answer === '' && $qr->answer) {
                $answer = trim((string) ($qr->answer->text ?? ''));
            }

            $score = $this->formatCandidateQuestionScore($app, $qr);

            $rows[] = [
                'question' => $qtext !== '' ? $qtext : ('#'.$qr->question_id),
                'answer' => $answer !== '' ? $answer : '—',
                'score' => $score,
            ];
        }

        return $rows;
    }

    public function cancelApplication(int $id): void
    {
        if ($this->isAdminViewing) {
            return;
        }

        $candidate = Candidate::where('user_id', auth()->id())->first();
        if (! $candidate) {
            Notification::make()->title(__('Candidate profile not found.'))->danger()->send();

            return;
        }

        $app = ApplicationProgress::where('id', $id)
            ->where('candidate_id', $candidate->id)
            ->first();

        if (! $app) {
            Notification::make()->title(__('Application not found.'))->danger()->send();

            return;
        }

        if (! in_array($app->status, ['pending', 'in_progress'], true)) {
            Notification::make()
                ->title(__('This application can no longer be cancelled.'))
                ->warning()
                ->send();

            return;
        }

        $app->update(['status' => 'cancelled']);

        if ($this->selectedApplicationId === $id) {
            $this->clearApplicationSelection();
        }

        Notification::make()
            ->title(__('Application cancelled.'))
            ->success()
            ->send();

        $this->resetPage('applicationsPage');
        $this->loadApplications();
    }

    /**
     * CV (step 1) + every test configured on the job offer.
     */
    private function resolveAssessmentStepCount(ApplicationProgress $app): int
    {
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
            return $this->resolveFreeApplicationAssessmentStepCount($app);
        }

        $fromResponses = (int) ($app->responses()->max('level') ?? 0);
        $fromCurrent = (int) $app->current_level;

        return min(20, max(2, $fromResponses + 2, $fromCurrent + 1));
    }

    /**
     * CV (1 step) + one pipeline step per test round (submitted or currently open).
     * Final decision is added separately in getStepperSteps() — do not add it here.
     */
    private function resolveFreeApplicationAssessmentStepCount(ApplicationProgress $app): int
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

    private function resolveConnectorLineState(string $stepState): string
    {
        return match ($stepState) {
            'completed' => 'done',
            'current', 'waiting' => 'active',
            default => 'muted',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCvStep(ApplicationProgress $app, int $offerStep): array
    {
        $hasCv = (bool) $app->resolveCvStoragePath();

        $state = 'future';
        $circleKey = 'candidate.applications.step_cv_pending';

        if ($app->status === 'pending') {
            $state = 'current';
            $circleKey = $hasCv
                ? 'candidate.applications.step_cv_submitted'
                : 'candidate.applications.step_cv_pending';
        } elseif (in_array($app->status, ['rejected', 'validated', 'cancelled'], true)) {
            $state = 'completed';
            $circleKey = match ($app->status) {
                'rejected' => 'candidate.applications.step_rejected',
                'validated' => 'candidate.applications.step_validated',
                default => 'candidate.applications.step_closed',
            };
        } else {
            $state = 'completed';
            $circleKey = 'candidate.applications.step_cv_accepted';
        }

        return [
            'offer_step' => $offerStep,
            'kind' => 'cv',
            'circle_label' => __($circleKey),
            'state' => $state,
            'line_state' => $this->resolveConnectorLineState($state),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildTestStep(ApplicationProgress $app, int $offerStep): array
    {
        $testNumber = max(1, $offerStep - 1);
        $testIndex = $testNumber - 1;

        $response = Response::query()
            ->where('application_id', $app->id)
            ->orderBy('level')
            ->orderBy('id')
            ->skip($testIndex)
            ->first();

        $responseLevel = $response
            ? (int) $response->level
            : max(1, (int) $app->current_level);

        if ($app->status === 'pending') {
            return [
                'offer_step' => $offerStep,
                'kind' => 'test',
                'test_number' => $testNumber,
                'response_level' => $responseLevel,
                'circle_label' => $this->futureTestCircleLabel($testNumber),
                'state' => 'future',
                'line_state' => 'muted',
                'has_response' => false,
            ];
        }

        $hasResponse = $response !== null;

        $state = 'future';
        $circleKey = 'candidate.applications.step_test_future';

        $isAwaitingThisRound = $hasResponse
            && $app->level_status === 'awaiting_approval'
            && (int) $app->current_level === $responseLevel;

        $isCurrentOpenRound = ! $hasResponse
            && $app->test_id
            && $app->status === 'in_progress'
            && $app->level_status === 'in_progress'
            && (int) $app->current_level === $responseLevel
            && $testIndex === (int) $app->responses()->count();

        if ($isAwaitingThisRound) {
            return [
                'offer_step' => $offerStep,
                'kind' => 'test',
                'test_number' => $testNumber,
                'response_level' => $responseLevel,
                'circle_label' => __('candidate.applications.step_test_awaiting'),
                'state' => 'waiting',
                'line_state' => $this->resolveConnectorLineState('waiting'),
                'has_response' => true,
            ];
        }

        if ($app->status === 'validated') {
            $state = 'completed';
            $circleKey = $hasResponse
                ? 'candidate.applications.step_test_submitted'
                : 'candidate.applications.step_test_locked';
        } elseif ($app->status === 'rejected' || $app->status === 'cancelled') {
            $state = $hasResponse ? 'completed' : 'future';
            $circleKey = $hasResponse
                ? 'candidate.applications.step_test_submitted'
                : 'candidate.applications.step_test_future';
        } elseif ($hasResponse) {
            $state = 'completed';
            $circleKey = 'candidate.applications.step_test_submitted';
        } elseif ($isCurrentOpenRound) {
            $state = 'current';
            $circleKey = 'candidate.applications.step_test_open';
        } elseif ($hasResponse || $testIndex < (int) $app->responses()->count()) {
            $state = 'completed';
            $circleKey = 'candidate.applications.step_test_submitted';
        }

        $circleLabel = $state === 'future'
            ? $this->futureTestCircleLabel($testNumber)
            : __($circleKey);

        return [
            'offer_step' => $offerStep,
            'kind' => 'test',
            'test_number' => $testNumber,
            'response_level' => $responseLevel,
            'circle_label' => $circleLabel,
            'state' => $state,
            'line_state' => $this->resolveConnectorLineState($state),
            'has_response' => $hasResponse,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFinalDecisionStep(ApplicationProgress $app, int $offerStep): array
    {
        $state = 'future';
        $circleKey = 'candidate.applications.step_decision_waiting';

        if ($app->status === 'validated') {
            $state = 'completed';
            $circleKey = $app->isFreeApplication()
                ? 'candidate.applications.step_free_potential'
                : 'candidate.applications.step_validated';
        } elseif ($app->status === 'rejected') {
            $state = 'rejected';
            $circleKey = 'candidate.applications.step_rejected';
        } elseif ($app->status === 'cancelled') {
            $state = 'future';
            $circleKey = 'candidate.applications.step_closed';
        } elseif ($app->isAwaitingTestAssignment()) {
            $state = 'current';
            $circleKey = 'candidate.applications.step_free_awaiting_test';
        }

        return [
            'offer_step' => $offerStep,
            'kind' => 'decision',
            'circle_label' => __($circleKey),
            'state' => $state,
            'line_state' => 'muted',
        ];
    }

    private function futureTestCircleLabel(int $testNumber): string
    {
        return (string) __('candidate.applications.step_test_future', ['n' => $testNumber]);
    }
}
