<div class="mx-auto max-w-6xl space-y-8">
    @if ($isAdminViewing)
        <div
            class="flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950 sm:flex-row sm:items-center sm:justify-between dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100"
        >
            <span class="flex items-center gap-2 font-medium">
                @svg('heroicon-o-shield-check', 'h-5 w-5 shrink-0 text-amber-700 dark:text-amber-300')
                {{ __('Vous consultez cet espace en tant qu administrateur') }}
            </span>
            <a
                href="{{ url('/admin') }}"
                class="inline-flex shrink-0 items-center justify-center rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-amber-700"
            >
                {{ __('Back to admin') }}
            </a>
        </div>
    @endif

    <div
        class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-600 via-violet-700 to-violet-900 px-6 py-8 text-white shadow-lg ring-1 ring-white/10 sm:px-8 sm:py-10 dark:from-violet-700 dark:via-violet-800 dark:to-violet-950"
    >
        <div
            class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-3xl"
            aria-hidden="true"
        ></div>
        <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0 space-y-2">
                <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">
                    {{ __('Hello') }}, {{ $userName }}
                </h1>
                <p class="max-w-xl text-sm leading-relaxed text-violet-100/85 sm:text-base">
                    {{ __('Welcome to your candidate space. Manage your applications easily.') }}
                </p>
            </div>
            <a
                href="{{ route('filament.candidate.pages.notifications') }}"
                class="relative inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/15 text-white ring-1 ring-white/25 transition hover:bg-white/25"
                title="{{ __('Mes Notifications') }}"
            >
                @svg('heroicon-o-bell', 'h-6 w-6')
                @if ($unreadCount > 0)
                    <span
                        class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white ring-2 ring-violet-800"
                    >
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                @endif
            </a>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div
            class="flex items-center gap-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
        >
            <div
                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-500/15 dark:text-violet-300"
            >
                @svg('heroicon-o-clipboard-document-list', 'h-6 w-6')
            </div>
            <div>
                <p class="text-2xl font-semibold tabular-nums text-gray-900 dark:text-white">{{ $totalApplications }}</p>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    {{ __('Total applications') }}
                </p>
            </div>
        </div>
        <div
            class="flex items-center gap-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
        >
            <div
                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300"
            >
                @svg('heroicon-o-clock', 'h-6 w-6')
            </div>
            <div>
                <p class="text-2xl font-semibold tabular-nums text-gray-900 dark:text-white">{{ $pendingApplications }}</p>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    {{ __('Pending') }}
                </p>
            </div>
        </div>
        <div
            class="flex items-center gap-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
        >
            <div
                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300"
            >
                @svg('heroicon-o-check-circle', 'h-6 w-6')
            </div>
            <div>
                <p class="text-2xl font-semibold tabular-nums text-gray-900 dark:text-white">{{ $completedApplications }}</p>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    {{ __('Validated') }}
                </p>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
        <a
            href="{{ route('filament.candidate.pages.choix-candidature') }}"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-violet-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700"
        >
            @svg('heroicon-o-rocket-launch', 'h-5 w-5 shrink-0')
            {{ __('New application') }}
        </a>
        <a
            href="{{ route('filament.candidate.pages.take-test') }}"
            class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-900 shadow-sm transition hover:border-violet-300 hover:bg-violet-50/80 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:hover:border-violet-500/40 dark:hover:bg-violet-950/40"
        >
            @svg('heroicon-o-academic-cap', 'h-5 w-5 shrink-0 text-violet-600 dark:text-violet-400')
            {{ __('Take the test') }}
        </a>
    </div>

    <div
        class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 sm:p-6"
    >
        <div class="mb-5 flex flex-col gap-3 border-b border-gray-100 pb-5 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2">
                @svg('heroicon-o-briefcase', 'h-5 w-5 text-violet-600 dark:text-violet-400')
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Recent applications') }}</h2>
            </div>
            <button
                type="button"
                wire:click="refreshData"
                wire:loading.attr="disabled"
                wire:target="refreshData"
                class="inline-flex items-center justify-center gap-2 self-start rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:hover:bg-white/10 sm:self-auto"
            >
                <span wire:loading.remove wire:target="refreshData" class="inline-flex items-center gap-1.5">
                    @svg('heroicon-o-arrow-path', 'h-4 w-4')
                    {{ __('Actualiser') }}
                </span>
                <span wire:loading wire:target="refreshData" class="inline-flex items-center gap-1.5">
                    @svg('heroicon-o-arrow-path', 'h-4 w-4 animate-spin')
                </span>
            </button>
        </div>

        @if ($recentApplications && $recentApplications->count() > 0)
            <div class="space-y-4">
                @foreach ($recentApplications as $app)
                    <div
                        class="flex flex-col gap-3 rounded-xl border border-gray-100 bg-gray-50/80 p-4 transition hover:border-violet-200/60 hover:bg-white dark:border-white/5 dark:bg-white/[0.03] dark:hover:border-violet-500/30 dark:hover:bg-white/[0.06] sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="min-w-0">
                            <div class="mb-1 flex flex-wrap items-center gap-2">
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    {{ $app->offre?->title ?? __('Open application') }}
                                </span>
                                <span
                                    @class([
                                        'inline-flex rounded-full px-2 py-0.5 text-xs font-semibold ring-1 ring-inset',
                                        'bg-amber-50 text-amber-800 ring-amber-600/10 dark:bg-amber-500/10 dark:text-amber-200' => $app->status === 'pending',
                                        'bg-sky-50 text-sky-800 ring-sky-600/10 dark:bg-sky-500/10 dark:text-sky-200' => $app->status === 'in_progress',
                                        'bg-emerald-50 text-emerald-800 ring-emerald-600/10 dark:bg-emerald-500/10 dark:text-emerald-200' => $app->status === 'validated',
                                        'bg-rose-50 text-rose-800 ring-rose-600/10 dark:bg-rose-500/10 dark:text-rose-200' => $app->status === 'rejected',
                                        'bg-gray-100 text-gray-700 ring-gray-500/10 dark:bg-white/10 dark:text-gray-300' => ! in_array($app->status, ['pending', 'in_progress', 'validated', 'rejected'], true),
                                    ])
                                >
                                    {{ match ($app->status) {
                                        'pending' => __('Pending'),
                                        'in_progress' => __('In Progress'),
                                        'validated' => __('Validated'),
                                        'rejected' => __('Rejected'),
                                        default => $app->status,
                                    } }}
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-600 dark:text-gray-400">
                                <span>{{ __('Niveau:') }} {{ $app->current_level }}</span>
                                <span class="font-semibold text-violet-700 dark:text-violet-300">{{ __('Score:') }}
                                    {{ $app->main_score }}/100</span>
                            </div>
                        </div>
                        <div class="shrink-0 text-left text-xs text-gray-500 dark:text-gray-400 sm:text-right">
                            {{ $app->created_at->diffForHumans() }}
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div
                class="rounded-xl border border-dashed border-gray-200 bg-gray-50/50 px-6 py-12 text-center text-sm text-gray-600 dark:border-white/10 dark:bg-white/[0.02] dark:text-gray-300"
            >
                {{ __('No applications yet.') }}
            </div>
        @endif
    </div>
</div>
