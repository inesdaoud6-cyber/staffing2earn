<x-filament-panels::page>
    <div class="candidate-dashboard">
        {{-- Welcome: colors from filament-shell.css (panel Tailwind may omit custom utilities). --}}
        <div class="candidate-dashboard-hero">
            <div class="candidate-dashboard-hero-inner">
                <div class="candidate-dashboard-hero-body">
                    <p class="candidate-dashboard-hero__label">{{ __('Welcome') }}</p>
                    <h1 class="candidate-dashboard-hero__name">{{ $userName }}</h1>
                    <p class="candidate-dashboard-hero__tagline">
                        {{ __('This is your personal recruitment space.') }}
                    </p>
                </div>

                <a
                    href="{{ route('filament.candidate.pages.notifications') }}"
                    wire:navigate
                    class="candidate-dashboard-hero__notif"
                    title="{{ __('Mes Notifications') }}"
                >
                    @svg('heroicon-o-bell')
                    @if ($unreadCount > 0)
                        <span class="candidate-dashboard-hero__badge">
                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                        </span>
                    @endif
                </a>
            </div>
        </div>

        <div class="candidate-dashboard-stats-grid">
            <div class="candidate-dashboard-stat">
                <div class="candidate-dashboard-stat__icon candidate-dashboard-stat__icon--violet">
                    @svg('heroicon-o-clipboard-document-list')
                </div>
                <div>
                    <p class="candidate-dashboard-stat__label">{{ __('Total Applications') }}</p>
                    <p class="candidate-dashboard-stat__value">{{ $totalApplications }}</p>
                </div>
            </div>
            <div class="candidate-dashboard-stat">
                <div class="candidate-dashboard-stat__icon candidate-dashboard-stat__icon--amber">
                    @svg('heroicon-o-clock')
                </div>
                <div>
                    <p class="candidate-dashboard-stat__label">{{ __('Pending') }}</p>
                    <p class="candidate-dashboard-stat__value">{{ $pendingApplications }}</p>
                </div>
            </div>
            <div class="candidate-dashboard-stat">
                <div class="candidate-dashboard-stat__icon candidate-dashboard-stat__icon--emerald">
                    @svg('heroicon-o-check-circle')
                </div>
                <div>
                    <p class="candidate-dashboard-stat__label">{{ __('Validated') }}</p>
                    <p class="candidate-dashboard-stat__value">{{ $completedApplications }}</p>
                </div>
            </div>
        </div>

        <div class="candidate-dashboard-actions">
            <a
                href="{{ route('filament.candidate.pages.choix-candidature') }}"
                wire:navigate
                class="candidate-dashboard-btn-primary"
            >
                @svg('heroicon-o-rocket-launch')
                {{ __('New Application') }}
            </a>

            @if ($activeApplication && ! in_array($activeApplication->level_status, ['awaiting_approval', 'rejected'], true))
                <a
                    href="{{ route('filament.candidate.pages.take-test') }}"
                    wire:navigate
                    class="candidate-dashboard-btn-secondary"
                >
                    @svg('heroicon-o-academic-cap')
                    {{ __('Continue Test — Level') }} {{ $activeApplication->current_level }}
                </a>
            @endif
        </div>
    </div>
</x-filament-panels::page>
