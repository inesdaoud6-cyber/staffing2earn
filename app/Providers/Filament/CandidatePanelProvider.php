<?php

namespace App\Providers\Filament;

use App\Http\Middleware\CandidateMiddleware;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class CandidatePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('candidate')
            ->path('candidate')
            ->colors(['primary' => Color::Purple])
            ->loginRouteSlug('login')
            ->brandName('Staffing2Earn')
            ->brandLogo(asset('images/2earn.png'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('images/2earn.png'))
            ->homeUrl(fn () => route('filament.candidate.pages.dashboard'))
            ->renderHook(
                'panels::topbar.end',
                fn () => view('partials.topbar-actions')
            )
            ->userMenuItems([
                MenuItem::make()
                    ->label(fn () => __('Mon Profil'))
                    ->icon('heroicon-o-user-circle')
                    ->url(fn () => route('filament.candidate.pages.my-profile')),
                MenuItem::make()
                    ->label(fn () => __('Paramètres du Compte'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->url(fn () => route('filament.candidate.pages.account-settings')),
                MenuItem::make()
                    ->label(fn () => __('Panel Admin'))
                    ->icon('heroicon-o-shield-check')
                    ->url(fn () => route('filament.admin.pages.dashboard'))
                    ->visible(fn () => auth()->check() && auth()->user()->hasRole('admin')),
            ])
            ->discoverResources(
                in: app_path('Filament/Candidate/Resources'),
                for: 'App\\Filament\\Candidate\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Candidate/Pages'),
                for: 'App\\Filament\\Candidate\\Pages'
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                CandidateMiddleware::class,
                Authenticate::class,
            ]);
    }
}
