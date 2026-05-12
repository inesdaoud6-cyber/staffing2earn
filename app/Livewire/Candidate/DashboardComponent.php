<?php

namespace App\Livewire\Candidate;

use App\Services\CandidateService;
use Livewire\Component;

class DashboardComponent extends Component
{
    public string $userName            = '';
    public int    $totalApplications   = 0;
    public int    $pendingApplications = 0;
    public int    $completedApplications = 0;
    public int    $rejectedApplications  = 0;
    public        $recentApplications;
    public int    $unreadCount         = 0;
    public bool   $isAdminViewing      = false;

    public function mount(CandidateService $service): void
    {
        $this->loadData($service);
    }

    public function loadData(CandidateService $service): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $candidate = $service->getCandidateByUser($user);

        $this->userName       = $candidate?->first_name ?? $user->name;
        $this->isAdminViewing = $user->can('view-all-applications');
        $this->unreadCount    = $service->getUnreadNotificationsCount($user);

        if (! $candidate) {
            $this->recentApplications = collect();
            return;
        }

        $stats = $service->getApplicationStats($candidate);

        $this->totalApplications     = $stats['total'];
        $this->pendingApplications   = $stats['pending'];
        $this->completedApplications = $stats['completed'];
        $this->rejectedApplications  = $stats['rejected'];
        $this->recentApplications    = $stats['recent'];
    }

    public function refreshData(CandidateService $service): void
    {
        $this->loadData($service);
    }

    public function render()
    {
        return view('livewire.candidate.dashboard-component');
    }
}
