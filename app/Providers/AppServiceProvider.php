<?php

namespace App\Providers;

use App\Filesystem\WindowsFilesystem;
use App\Http\Responses\LogoutResponse;
use App\Models\Offre;
use App\Observers\OffreObserver;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LogoutResponseContract::class, LogoutResponse::class);

        if (PHP_OS_FAMILY === 'Windows') {
            $this->app->singleton('files', fn () => new WindowsFilesystem);
        }
    }

    public function boot(): void
    {
        Livewire::component('candidate.dashboard-component', \App\Livewire\Candidate\DashboardComponent::class);
        Livewire::component('candidate.take-test-component', \App\Livewire\Candidate\TakeTestComponent::class);
        Livewire::component('notification-bell', \App\Livewire\NotificationBell::class);

        Offre::observe(OffreObserver::class);

        Gate::define('view-translation-manager', fn () => true);

        Gate::define('view-candidate-scores', fn ($user) => $user->hasRole('admin'));
        Gate::define('edit-candidate-status', fn ($user) => $user->hasRole('admin'));
        Gate::define('view-all-applications', fn ($user) => $user->hasRole('admin'));
        Gate::define('send-candidate-notification', fn ($user) => $user->hasRole('admin'));
        Gate::define('download-candidate-cv', fn ($user) => $user->hasRole('admin'));
        Gate::define('view-test-results-detail', fn ($user) => $user->hasRole('admin'));
    }
}
