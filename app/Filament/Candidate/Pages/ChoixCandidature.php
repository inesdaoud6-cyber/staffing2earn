<?php

namespace App\Filament\Candidate\Pages;

use App\Models\ApplicationProgress;
use App\Models\Candidate;
use App\Models\Offre;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ChoixCandidature extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.candidate.pages.choix-candidature';
    protected static ?string $title = 'Choisir ma Candidature';
    protected static ?string $slug = 'choix-candidature';
    protected static ?int $navigationSort = 2;

    public $offres;
    public string $search = '';

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

    public function candidatLibre(): void
    {
        $candidate = Candidate::where('user_id', auth()->id())->first();

        if (! $candidate) {
            Notification::make()->title('Profil candidat introuvable.')->danger()->send();
            return;
        }

        $existing = ApplicationProgress::where('candidate_id', $candidate->id)
            ->whereNull('offre_id')
            ->whereNotIn('status', ['rejected'])
            ->first();

        if ($existing) {
            Notification::make()->title('Vous avez déjà une candidature libre en cours.')->warning()->send();
            $this->redirect(route('filament.candidate.pages.applications'));
            return;
        }

        ApplicationProgress::create([
            'candidate_id'    => $candidate->id,
            'offre_id'        => null,
            'status'          => 'pending',
            'current_level'   => 1,
            'main_score'      => 0,
            'secondary_score' => 0,
        ]);

        Notification::make()->title('Candidature libre créée avec succès !')->success()->send();
        $this->redirect(route('filament.candidate.pages.upload-cv'));
    }

    public function candidateOffre(int $offreId): void
    {
        $candidate = Candidate::where('user_id', auth()->id())->first();

        if (! $candidate) {
            Notification::make()->title('Profil candidat introuvable.')->danger()->send();
            return;
        }

        $offre = Offre::find($offreId);
        if (! $offre || ! $offre->is_published) {
            Notification::make()->title('Cette offre n\'est plus disponible.')->danger()->send();
            return;
        }

        $existing = ApplicationProgress::where('candidate_id', $candidate->id)
            ->where('offre_id', $offreId)
            ->whereNotIn('status', ['rejected'])
            ->first();

        if ($existing) {
            Notification::make()->title('Vous avez déjà postulé à cette offre.')->warning()->send();
            $this->redirect(route('filament.candidate.pages.applications'));
            return;
        }

        ApplicationProgress::create([
            'candidate_id'    => $candidate->id,
            'offre_id'        => $offreId,
            'test_id'         => $offre->test_id,
            'status'          => 'pending',
            'current_level'   => 1,
            'main_score'      => 0,
            'secondary_score' => 0,
        ]);

        Notification::make()->title('Candidature soumise pour "' . $offre->title . '" !')->success()->send();
        $this->redirect(route('filament.candidate.pages.upload-cv'));
    }
}