<?php

namespace App\Filament\Candidate\Pages;

use App\Models\ApplicationProgress;
use App\Models\Candidate;
use Filament\Notifications\Notification;
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

    /**
     * Candidate-initiated cancellation. Only allowed while the application is
     * still pending or in progress — once an admin has validated or rejected
     * it, the candidate can no longer cancel.
     */
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

        Notification::make()
            ->title(__('Application cancelled.'))
            ->success()
            ->send();

        $this->loadApplications();
    }
}