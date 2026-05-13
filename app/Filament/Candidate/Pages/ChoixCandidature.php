<?php

namespace App\Filament\Candidate\Pages;

use App\Models\ApplicationProgress;
use App\Models\Candidate;
use App\Models\Offre;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\WithFileUploads;

class ChoixCandidature extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.candidate.pages.choix-candidature';
    protected static ?string $title = 'Choisir ma Candidature';
    protected static ?string $slug = 'choix-candidature';
    protected static ?int $navigationSort = 2;

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
    }

    public function updatedSearch(): void
    {
        $this->loadOffres();
    }

    public function startApplyFree(): void
    {
        $candidate = $this->candidateOrFail();
        if (! $candidate) {
            return;
        }

        if ($this->hasPendingOrApproved($candidate->id, null)) {
            Notification::make()->title(__('You already have a free application in progress.'))->warning()->send();
            $this->redirect(route('filament.candidate.pages.applications'));
            return;
        }

        $this->openCvDialog('free', null, __('Free Application'), $candidate);
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

        if ($this->hasPendingOrApproved($candidate->id, $offreId)) {
            Notification::make()->title(__('You have already applied to this offer.'))->warning()->send();
            $this->redirect(route('filament.candidate.pages.applications'));
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
            'test_id'         => $offre?->test_id,
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

    private function hasPendingOrApproved(int $candidateId, ?int $offreId): bool
    {
        return ApplicationProgress::where('candidate_id', $candidateId)
            ->when(
                $offreId === null,
                fn ($q) => $q->whereNull('offre_id'),
                fn ($q) => $q->where('offre_id', $offreId),
            )
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->exists();
    }
}
