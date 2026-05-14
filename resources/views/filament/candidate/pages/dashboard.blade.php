<x-filament-panels::page>
    @vite('resources/css/candidate-dashboard.css')

    <div class="space-y-8">

        {{-- Welcome Banner --}}
        <div class="p-6 rounded-xl shadow-lg text-white"
             style="background: linear-gradient(135deg, #581c87, #3730a3);">
            <h1 class="text-3xl font-bold mb-1">👋 {{ __('Welcome') }}, {{ $userName }}!</h1>
            <p class="opacity-80 text-lg">{{ __('This is your personal recruitment space.') }}</p>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-xl p-6 shadow flex items-center gap-4" style="background-color:#1f2937;">
                <span class="text-4xl">📋</span>
                <div>
                    <p style="color:#9ca3af;">{{ __('Total Applications') }}</p>
                    <p class="text-3xl font-bold text-white">{{ $totalApplications }}</p>
                </div>
            </div>
            <div class="rounded-xl p-6 shadow flex items-center gap-4" style="background-color:#1f2937;">
                <span class="text-4xl">⏳</span>
                <div>
                    <p style="color:#9ca3af;">{{ __('Pending') }}</p>
                    <p class="text-3xl font-bold" style="color:#f59e0b;">{{ $pendingApplications }}</p>
                </div>
            </div>
            <div class="rounded-xl p-6 shadow flex items-center gap-4" style="background-color:#1f2937;">
                <span class="text-4xl">✅</span>
                <div>
                    <p style="color:#9ca3af;">{{ __('Validated') }}</p>
                    <p class="text-3xl font-bold" style="color:#10b981;">{{ $completedApplications }}</p>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="/candidate/choix-candidature"
               class="flex items-center justify-center gap-2 p-4 rounded-xl font-bold text-white transition hover:opacity-90"
               style="background-color:#7c3aed;">
                🚀 {{ __('New Application') }}
            </a>
            @if($activeApplication && !in_array($activeApplication->level_status, ['awaiting_approval', 'rejected']))
            <a href="/candidate/take-test"
               class="flex items-center justify-center gap-2 p-4 rounded-xl font-bold text-white transition hover:opacity-90"
               style="background-color:#0891b2;">
                📝 {{ __('Continue Test — Level') }} {{ $activeApplication->current_level }}
            </a>
            @endif
        </div>

        {{-- Recent Applications in Cards --}}
        <div>
            <h2 class="text-xl font-bold mb-4 text-white">📌 {{ __('My Applications') }}</h2>

            @forelse($recentApplications as $app)
                <div class="rounded-xl shadow p-5 mb-4" style="background-color:#1f2937;">
                    <div class="flex items-start justify-between flex-wrap gap-3">
                        <div>
                            <p class="text-white font-semibold text-lg">
                                {{ $app->offre?->title ?? __('Free Application') }}
                            </p>
                            <p style="color:#6b7280; font-size:0.85rem;">
                                {{ $app->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @php
                                $statusColors = [
                                    'pending'     => '#d97706',
                                    'in_progress' => '#0891b2',
                                    'validated'   => '#10b981',
                                    'rejected'    => '#ef4444',
                                ];
                                $levelStatusColors = [
                                    'in_progress'       => '#6b7280',
                                    'awaiting_approval' => '#d97706',
                                    'approved'          => '#10b981',
                                    'rejected'          => '#ef4444',
                                ];
                            @endphp
                            <span class="px-3 py-1 rounded-full text-white text-sm font-bold"
                                  style="background-color:{{ $statusColors[$app->status] ?? '#6b7280' }};">
                                {{ ucfirst(str_replace('_', ' ', $app->status)) }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-white text-sm"
                                  style="background-color:{{ $levelStatusColors[$app->level_status] ?? '#6b7280' }};">
                                Level {{ $app->current_level }}
                                @if($app->level_status === 'awaiting_approval') ⏳ @endif
                                @if($app->level_status === 'approved') ✅ @endif
                                @if($app->level_status === 'rejected') ❌ @endif
                            </span>
                        </div>
                    </div>

                    {{-- Score visible if published or owned --}}
                    @if($app->candidateProfile?->score_visibility || true)
                        <div class="mt-3 flex items-center gap-4">
                            <div>
                                <span style="color:#9ca3af; font-size:0.85rem;">{{ __('Score Principal:') }}</span>
                                <span class="font-bold text-white ml-1">{{ $app->main_score }} pts</span>
                            </div>
                            <div>
                                <span style="color:#9ca3af; font-size:0.85rem;">{{ __('Score Secondaire:') }}</span>
                                <span class="font-bold text-white ml-1">{{ $app->secondary_score }} pts</span>
                            </div>
                        </div>
                    @endif

                    @if($app->level_status === 'awaiting_approval')
                        <div class="mt-3 p-3 rounded-lg" style="background-color:#78350f20; border:1px solid #d97706;">
                            <p style="color:#fbbf24; font-size:0.9rem;">
                                ⏳ {{ __('Your level is submitted and awaiting admin approval before you can continue.') }}
                            </p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="rounded-xl p-8 text-center" style="background-color:#1f2937;">
                    <p style="color:#9ca3af;" class="text-lg">
                        {{ __('No applications yet. Start your first application!') }}
                    </p>
                </div>
            @endforelse
        </div>

    </div>
</x-filament-panels::page>
