<?php

namespace App\Filament\Candidate\Pages;

use App\Models\ApplicationProgress;
use App\Models\Candidate;
use App\Models\Offre;
use App\Models\Test;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\WithFileUploads;

class ChoixCandidature extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static string $view = 'filament.candidate.pages.choix-candidature';

    protected static ?string $slug = 'choix-candidature';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'candidate.main';

    public static function getNavigationLabel(): string
    {
        return __('nav.job_offers');
    }

    public function getTitle(): string
    {
        return __('nav.job_offers');
    }

    public $offres;
    public string $search = '';

    /**
     * CV-choice modal state. The modal opens only after the candidate clicks
     * "Apply" on an offer or "Free Application". The ApplicationProgress row
     * is created only once a CV has been selected — never before.
     */
    public bool $cvDialogOpen   = false;
    public string $pendingMode  = '';   // 'offer' | 'free'
    public ?int $pendingOffreId = null;
    public string $pendingOffreTitle = '';
    public bool $hasProfileCv   = false;
    public ?string $profileCvName = null;
    public $newCv = null;

    public bool $offerDetailsOpen = false;

    public ?int $detailsOffreId = null;

    /** @var array<int, 'apply'|'applied'|'rejected'|'reapply'> */
    public array $offerApplicationActions = [];

    public string $freeApplicationAction = 'apply';

    public function mount(): void
    {
        $this->loadOffres();
    }

    public function loadOffres(): void
    {
        $query = Offre::where('is_published', true);

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('domain', 'like', '%' . $this->search . '%')
                  ->orWhere('location', 'like', '%' . $this->search . '%');
            });
        }

        $this->offres = $query->latest()->get();
        $this->loadApplicationActions();
    }

    public function updatedSearch(): void
    {
        $this->loadOffres();
    }

    public function getOfferApplyAction(?int $offreId): string
    {
        if ($offreId === null) {
            return $this->freeApplicationAction;
        }

        return $this->offerApplicationActions[$offreId] ?? 'apply';
    }

    public function offerApplyButtonLabel(string $action): string
    {
        return match ($action) {
            'applied' => __('candidate.choix.applied_already'),
            'rejected' => __('candidate.choix.rejected'),
            'reapply' => __('candidate.choix.reapply'),
            default => __('candidate.choix.apply'),
        };
    }

    public function offerApplyButtonIsDisabled(string $action): bool
    {
        return in_array($action, ['applied', 'rejected'], true);
    }

    public function offerApplyButtonCanStart(string $action): bool
    {
        return in_array($action, ['apply', 'reapply'], true);
    }

    public function offerApplyButtonClass(string $action, string $base = 'offre-apply-btn'): string
    {
        return match ($action) {
            'applied' => $base.' offre-apply-btn--applied',
            'rejected' => $base.' offre-apply-btn--rejected',
            'reapply' => $base.' offre-apply-btn--reapply',
            default => $base,
        };
    }

    public function startApplyFree(): void
    {
        $candidate = $this->candidateOrFail();
        if (! $candidate) {
            return;
        }

        if (! $this->offerApplyButtonCanStart($this->getOfferApplyAction(null))) {
            return;
        }

        $this->openCvDialog('free', null, __('Free Application'), $candidate);
    }

    public function showOfferDetails(int $offreId): void
    {
        $offre = Offre::query()->where('is_published', true)->find($offreId);
        if (! $offre) {
            Notification::make()->title(__('This offer is no longer available.'))->danger()->send();

            return;
        }

        $this->detailsOffreId = $offre->id;
        $this->offerDetailsOpen = true;
    }

    public function closeOfferDetails(): void
    {
        $this->offerDetailsOpen = false;
        $this->detailsOffreId = null;
    }

    public function applyFromOfferDetails(): void
    {
        if (! $this->detailsOffreId) {
            return;
        }

        if (! $this->offerApplyButtonCanStart($this->getOfferApplyAction($this->detailsOffreId))) {
            return;
        }

        $offreId = $this->detailsOffreId;
        $this->closeOfferDetails();
        $this->startApplyOffre($offreId);
    }

    /**
     * @return array{
     *     offre: Offre,
     *     tests_count: int,
     *     assessment_levels: int,
     *     tests: list<array{step: int, label: string, name: string}>,
     *     total_applicants: int,
     *     other_applicants: int,
     * }|null
     */
    public function getOfferDetailsData(): ?array
    {
        if (! $this->detailsOffreId) {
            return null;
        }

        $offre = Offre::query()->where('is_published', true)->find($this->detailsOffreId);
        if (! $offre) {
            return null;
        }

        $tests = $this->resolveOfferTestsList($offre);
        $applicantsQuery = ApplicationProgress::query()
            ->where('offre_id', $offre->id)
            ->where('status', '!=', 'cancelled');

        $candidateId = Candidate::query()->where('user_id', auth()->id())->value('id');

        return [
            'offre' => $offre,
            'tests_count' => count($tests),
            'assessment_levels' => max(1, (int) $offre->levels_count),
            'tests' => $tests,
            'total_applicants' => (int) (clone $applicantsQuery)->count(),
            'other_applicants' => $candidateId
                ? (int) (clone $applicantsQuery)->where('candidate_id', '!=', $candidateId)->count()
                : (int) (clone $applicantsQuery)->count(),
        ];
    }

    public function startApplyOffre(int $offreId): void
    {
        $candidate = $this->candidateOrFail();
        if (! $candidate) {
            return;
        }

        $offre = Offre::find($offreId);
        if (! $offre || ! $offre->is_published) {
            Notification::make()->title(__('This offer is no longer available.'))->danger()->send();
            return;
        }

        if (! $this->offerApplyButtonCanStart($this->getOfferApplyAction($offreId))) {
            return;
        }

        $this->openCvDialog('offer', $offreId, $offre->title, $candidate);
    }

    public function cancelCvDialog(): void
    {
        $this->reset(['cvDialogOpen', 'pendingMode', 'pendingOffreId', 'pendingOffreTitle', 'newCv']);
    }

    public function applyWithProfileCv(): void
    {
        $candidate = $this->candidateOrFail();
        if (! $candidate || ! $candidate->cv_path) {
            Notification::make()->title(__('No profile CV is available. Please upload one.'))->danger()->send();
            return;
        }

        $this->createApplication($candidate, $candidate->cv_path);
    }

    public function applyWithNewCv(): void
    {
        $candidate = $this->candidateOrFail();
        if (! $candidate) {
            return;
        }

        $this->validate([
            'newCv' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ], [], [
            'newCv' => __('CV'),
        ]);

        $path = $this->newCv->store('cvs', 'public');

        // The freshly uploaded CV also becomes the profile CV so the candidate
        // can reuse it on future applications without re-uploading.
        $candidate->update(['cv_path' => $path]);

        $this->createApplication($candidate, $path);
    }

    private function createApplication(Candidate $candidate, string $cvPath): void
    {
        $offre = $this->pendingOffreId ? Offre::find($this->pendingOffreId) : null;

        ApplicationProgress::create([
            'candidate_id'    => $candidate->id,
            'offre_id'        => $offre?->id,
            'test_id'         => $offre?->firstTestIdAfterCv(),
            'cv_path'         => $cvPath,
            'status'          => 'pending',
            'current_level'   => 1,
            'main_score'      => 0,
            'secondary_score' => 0,
        ]);

        $title = $this->pendingMode === 'offer'
            ? __('Application submitted for ":title". The admin will review your CV.', ['title' => $this->pendingOffreTitle])
            : __('Free application submitted. The admin will review your CV.');

        Notification::make()->title($title)->success()->send();

        $this->cancelCvDialog();
        $this->redirect(route('filament.candidate.pages.applications'));
    }

    private function openCvDialog(string $mode, ?int $offreId, string $title, Candidate $candidate): void
    {
        $this->pendingMode       = $mode;
        $this->pendingOffreId    = $offreId;
        $this->pendingOffreTitle = $title;
        $this->hasProfileCv      = (bool) $candidate->cv_path;
        $this->profileCvName     = $candidate->cv_path ? basename($candidate->cv_path) : null;
        $this->newCv             = null;
        $this->cvDialogOpen      = true;
    }

    private function candidateOrFail(): ?Candidate
    {
        $candidate = Candidate::where('user_id', auth()->id())->first();
        if (! $candidate) {
            Notification::make()->title(__('Candidate profile not found.'))->danger()->send();
            return null;
        }
        return $candidate;
    }

    private function loadApplicationActions(): void
    {
        $candidateId = Candidate::query()->where('user_id', auth()->id())->value('id');

        if (! $candidateId) {
            $this->offerApplicationActions = [];
            $this->freeApplicationAction = 'apply';

            return;
        }

        $offreIds = collect($this->offres)->pluck('id')->filter()->all();

        $byOffre = ApplicationProgress::query()
            ->where('candidate_id', $candidateId)
            ->whereNotNull('offre_id')
            ->when($offreIds !== [], fn ($q) => $q->whereIn('offre_id', $offreIds))
            ->orderByDesc('created_at')
            ->get()
            ->unique('offre_id')
            ->keyBy('offre_id');

        $actions = [];
        foreach ($offreIds as $offreId) {
            $latest = $byOffre->get($offreId);
            $actions[(int) $offreId] = $this->resolveApplicationAction($latest);
        }

        $this->offerApplicationActions = $actions;

        $latestFree = ApplicationProgress::query()
            ->where('candidate_id', $candidateId)
            ->whereNull('offre_id')
            ->orderByDesc('created_at')
            ->first();

        $this->freeApplicationAction = $this->resolveApplicationAction($latestFree);
    }

    /**
     * @return 'apply'|'applied'|'rejected'|'reapply'
     */
    private function resolveApplicationAction(?ApplicationProgress $application): string
    {
        if ($application === null) {
            return 'apply';
        }

        if ($application->status === 'cancelled') {
            return 'reapply';
        }

        if ($application->status === 'rejected' || $application->level_status === 'rejected') {
            return 'rejected';
        }

        return 'applied';
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
}
