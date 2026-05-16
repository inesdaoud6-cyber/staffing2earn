<?php

namespace App\Providers;

use App\Filesystem\WindowsFilesystem;
use App\Http\Responses\LogoutResponse;
use App\Models\ApplicationProgress;
use App\Models\Offre;
use App\Observers\ApplicationProgressObserver;
use App\Observers\OffreObserver;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use App\Filament\Resources\BlockResource\Pages\ListBlocks;
use App\Filament\Resources\GroupResource\Pages\ListGroups;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\View\TablesRenderHook;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
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
        Livewire::component('admin-notification-bell', \App\Livewire\AdminNotificationBell::class);

        Offre::observe(OffreObserver::class);
        ApplicationProgress::observe(ApplicationProgressObserver::class);

        Gate::define('view-translation-manager', fn () => true);

        Gate::define('view-candidate-scores', fn ($user) => $user->hasRole('admin'));
        Gate::define('edit-candidate-status', fn ($user) => $user->hasRole('admin'));
        Gate::define('view-all-applications', fn ($user) => $user->hasRole('admin'));
        Gate::define('send-candidate-notification', fn ($user) => $user->hasRole('admin'));
        Gate::define('download-candidate-cv', fn ($user) => $user->hasRole('admin'));
        Gate::define('view-test-results-detail', fn ($user) => $user->hasRole('admin'));

        FilamentView::registerRenderHook(
            TablesRenderHook::TOOLBAR_END,
            fn (): string => view('filament.partials.blocks-list-select-all')->render(),
            scopes: [ListBlocks::class],
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE,
            function (): string {
                $current = Livewire::current();

                if ($current instanceof \App\Filament\Resources\UserResource\Pages\ListUsers) {
                    return View::make('filament.resources.user-list-records-toolbar')->render();
                }

                if ($current instanceof ListGroups) {
                    return View::make('filament.resources.groups-list-toolbar')->render();
                }

                return '';
            },
            scopes: [
                \App\Filament\Resources\UserResource::class,
                \App\Filament\Resources\GroupResource::class,
            ],
        );
    }
}
