<?php

namespace App\Filament\Candidate\Pages;

use App\Services\CandidateService;
use Filament\Pages\Page;

class Notifications extends Page
{
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.candidate.pages.notifications';
    protected static ?string $title = 'Notifications';
    protected static ?string $slug = 'notifications';

    public $notifications;
    public $offresNouvelles;

    public function mount(CandidateService $service): void
    {
        $user = auth()->user();

        $this->notifications   = $service->getNotifications($user);
        $this->offresNouvelles = $service->getActiveOffers();

        $service->markAllNotificationsAsRead($user);
    }
}