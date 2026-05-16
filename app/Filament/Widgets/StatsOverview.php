<?php

namespace App\Filament\Widgets;

use App\Models\ApplicationProgress;
use App\Models\Candidate;
use App\Models\Offre;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalCandidates = Candidate::count();
        $totalApplications = ApplicationProgress::where('status', '!=', 'cancelled')->count();
        $pendingCount = ApplicationProgress::where('status', 'pending')->count();
        $validatedCount = ApplicationProgress::where('status', 'validated')->count();
        $publishedOffers = Offre::where('is_published', true)->count();
        $totalOffers = Offre::count();

        return [
            Stat::make('Total Candidats', $totalCandidates)
                ->description('Comptes candidats enregistrés')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Candidatures', $totalApplications)
                ->description($pendingCount.' en attente')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Offres Publiées', $publishedOffers)
                ->description('sur '.$totalOffers.' offres totales')
                ->descriptionIcon('heroicon-o-briefcase')
                ->color('success'),

            Stat::make('Validés', $validatedCount)
                ->description('candidatures validées')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}
