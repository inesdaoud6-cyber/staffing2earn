<?php

namespace App\Filament\Candidate\Pages;

use App\Models\ApplicationProgress;
use Filament\Pages\Page;

class ApplicationSpace extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static string $view = 'filament.candidate.pages.application-space';
    protected static ?string $title = 'My Applications & Profile';
    protected static ?string $slug = 'applications';

    public function getViewData(): array
    {
        $user = auth()->user();

        $applications = ApplicationProgress::where('candidate_id', $user->id)
            ->latest()
            ->get();

        $candidateName = $user->candidate
            ? trim($user->candidate->first_name . ' ' . $user->candidate->last_name)
            : $user->name;

        return [
            'candidateName'     => $candidateName ?: $user->name,
            'totalApplications' => $applications->count(),
            'averageScore'      => round($applications->avg('main_score') ?? 0, 2),
            'applications'      => $applications,
        ];
    }
}