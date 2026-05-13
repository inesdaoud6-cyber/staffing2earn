<?php

namespace App\Filament\Candidate\Pages;

use App\Models\ApplicationProgress;
use App\Models\Candidate;
use Filament\Pages\Page;

class ApplicationSpace extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static string $view = 'filament.candidate.pages.application-space';
    protected static ?string $slug = 'applications';
    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('My Applications');
    }

    public function getTitle(): string
    {
        return __('My Applications');
    }

    public string $candidateName = '';
    public int $totalApplications = 0;
    public float $averageScore = 0;
    public $applications;
    public bool $isAdminViewing = false;
    public string $filterStatus = '';

    public function mount(): void
    {
        $this->loadApplications();
    }

    public function loadApplications(): void
    {
        $user      = auth()->user();
        $candidate = Candidate::where('user_id', $user->id)->first();

        $this->isAdminViewing = $user->can('view-candidate-scores');
        $this->candidateName  = $candidate
            ? trim($candidate->first_name . ' ' . $candidate->last_name) ?: $user->name
            : $user->name;

        if (! $candidate) {
            $this->applications      = collect();
            $this->totalApplications = 0;
            $this->averageScore      = 0;
            return;
        }

        $query = ApplicationProgress::where('candidate_id', $candidate->id)
            ->with(['offre', 'test']);

        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }

        $this->applications      = $query->latest()->get();
        $this->totalApplications = $this->applications->count();
        $this->averageScore      = round($this->applications->avg('main_score') ?? 0, 2);
    }

    public function updatedFilterStatus(): void
    {
        $this->loadApplications();
    }
}