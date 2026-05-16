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
        $totalCandidates   = Candidate::count();
        $totalApplications = ApplicationProgress::where('status', '!=', 'cancelled')->count();
        $pendingCount      = ApplicationProgress::where('status', 'pending')->count();
        $validatedCount    = ApplicationProgress::where('status', 'validated')->count();
        $publishedOffers   = Offre::where('is_published', true)->count();
        $totalOffers       = Offre::count();

        return [
            Stat::make(__('stats.total_candidates'), $totalCandidates)
                ->description(__('stats.registered_accounts'))
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),

            Stat::make(__('stats.applications'), $totalApplications)
                ->description($pendingCount . ' ' . __('stats.pending'))
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make(__('stats.published_offers'), $publishedOffers)
                ->description(__('stats.out_of') . ' ' . $totalOffers . ' ' . __('stats.total_offers'))
                ->descriptionIcon('heroicon-o-briefcase')
                ->color('success'),

            Stat::make(__('stats.validated'), $validatedCount)
                ->description(__('stats.validated_applications'))
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}