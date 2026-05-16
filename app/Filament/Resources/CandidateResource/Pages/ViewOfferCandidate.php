<?php

namespace App\Filament\Resources\CandidateResource\Pages;

use App\Filament\Resources\CandidateResource;
use App\Models\ApplicationProgress;
use App\Models\Candidate;
use App\Models\Offre;
use App\Support\OfferApplicationRanking;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ViewOfferCandidate extends Page
{
    protected static string $resource = CandidateResource::class;

    protected static string $view = 'filament.resources.candidate-resource.pages.view-offer-candidate';

    public string $offre = '';

    public int $record;

    public bool $showCv = false;

    public ?ApplicationProgress $application = null;

    public ?Candidate $candidate = null;

    public function mount(int|string $offre, int|string $record): void
    {
        $this->offre = (string) $offre;
        $this->record = (int) $record;

        if ($this->offre !== 'libre') {
            if (! ctype_digit($this->offre) || ! Offre::whereKey((int) $this->offre)->exists()) {
                abort(404);
            }
        }

        $this->candidate = Candidate::with('user')->findOrFail($this->record);

        $applicationQuery = ApplicationProgress::query()
            ->where('candidate_id', $this->record)
            ->where('status', '!=', 'cancelled')
            ->with([
                'responses' => fn ($q) => $q->orderBy('level'),
                'offre',
            ]);

        if ($this->offre === 'libre') {
            $applicationQuery->whereNull('offre_id');
        } else {
            $applicationQuery->where('offre_id', (int) $this->offre);
        }

        $this->application = $applicationQuery->firstOrFail();
    }

    public function getTitle(): string|Htmlable
    {
        return $this->candidate?->full_name ?? __('nav.candidate');
    }

    public function getBreadcrumb(): ?string
    {
        return $this->candidate?->full_name;
    }

    public function getRank(): ?int
    {
        if (! $this->application) {
            return null;
        }

        $ranks = $this->offre === 'libre'
            ? OfferApplicationRanking::ranksForFreeApplications()
            : OfferApplicationRanking::ranksForOffer((int) $this->offre);

        return $ranks[$this->application->id] ?? null;
    }

    /**
     * @return list<array{level: int, label: string, score: string}>
     */
    public function getTestScoreRows(): array
    {
        if (! $this->application) {
            return [];
        }

        $rows = [];
        $index = 1;

        foreach ($this->application->responses as $response) {
            if ($response->test_score === null) {
                continue;
            }

            $rows[] = [
                'level' => (int) $response->level,
                'label' => __('admin.candidate_test_number', ['n' => $index]),
                'score' => number_format((float) $response->test_score, 2).'%',
            ];
            $index++;
        }

        return $rows;
    }

    public function getFinalScoreLabel(): string
    {
        $score = $this->application?->main_score;

        return $score !== null && $score !== ''
            ? number_format((float) $score, 2).'%'
            : '—';
    }

    public function getCvUrl(): ?string
    {
        return $this->application?->cvPublicUrl() ?? $this->candidate?->cv_path
            ? asset('storage/'.$this->candidate->cv_path)
            : null;
    }

    public function getStatusLabel(): string
    {
        return match ($this->application?->status) {
            'pending' => __('Pending'),
            'in_progress' => __('In Progress'),
            'validated' => __('Validated'),
            'rejected' => __('Rejected'),
            default => (string) ($this->application?->status ?? '—'),
        };
    }

    public function toggleCv(): void
    {
        $this->showCv = ! $this->showCv;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label(__('admin.candidate_back_to_list'))
                ->icon('heroicon-o-arrow-left')
                ->url(CandidateResource::getUrl('by_offer', ['offre' => $this->offre]))
                ->color('gray'),
        ];
    }
}
