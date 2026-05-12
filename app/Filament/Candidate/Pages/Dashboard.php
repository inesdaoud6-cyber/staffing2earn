<?php

namespace App\Filament\Candidate\Pages;

use App\Models\ApplicationProgress;
use App\Models\CandidateNotification;
use Filament\Actions\Action;
use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.candidate.pages.dashboard';
    protected static ?string $title = 'Mon Espace';
    protected static ?string $slug = 'dashboard';
    protected static ?int $navigationSort = 1;

    public string $userName = '';
    public bool $isAdminViewing = false;
    public int $unreadCount = 0;
    public int $totalApplications = 0;
    public int $pendingApplications = 0;
    public int $completedApplications = 0;
    public $recentApplications;

    public function mount(): void
    {
        abort_unless(auth()->check(), 403);

        $user = auth()->user();
        $this->userName = $user->candidate?->first_name ?? $user->name ?? 'Candidat';
        $this->isAdminViewing = $user->hasRole('admin');

        $candidate = $user->candidate;

        if ($candidate) {
            $apps = ApplicationProgress::where('candidate_id', $candidate->id);
            $this->totalApplications     = $apps->count();
            $this->pendingApplications   = (clone $apps)->whereIn('status', ['pending', 'in_progress'])->count();
            $this->completedApplications = (clone $apps)->where('status', 'validated')->count();
            $this->recentApplications    = (clone $apps)->with('offre')->latest()->take(5)->get();
        } else {
            $this->recentApplications = collect();
        }

        $this->unreadCount = CandidateNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
    }

    public function refreshData(): void
    {
        $this->mount();
    }

    protected function getHeaderActions(): array
    {
        if (!auth()->user()->can('view-all-applications')) {
            return [];
        }

        return [
            Action::make('backToAdmin')
                ->label('Retour au panel admin')
                ->icon('heroicon-o-arrow-left')
                ->color('warning')
                ->url('/admin'),
        ];
    }
}