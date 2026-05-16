<x-filament-panels::page>
    @vite('resources/css/candidate-notifications.css')

    @can('send-candidate-notification')
        <div class="notif-admin-banner">
            <span class="notif-admin-banner-text">
                @svg('heroicon-o-shield-check', 'notif-inline-icon')
                {{ __('Admin view') }}
            </span>
            <a href="/admin">
                @svg('heroicon-o-arrow-left', 'notif-inline-icon')
                {{ __('Back to admin') }}
            </a>
        </div>
    @endcan

    <div class="notif-shell">
        {{-- ── Header ───────────────────────────────────────────── --}}
        <header class="notif-page-header">
            <div>
                <h2>{{ __('My Notifications') }}</h2>
                <p>
                    @if($unreadCount > 0)
                        <strong>{{ $unreadCount }}</strong>
                        {{ trans_choice('{1} unread notification|[2,*] unread notifications', $unreadCount) }}
                    @else
                        {{ __('You are all caught up.') }}
                    @endif
                </p>
            </div>

            <div class="notif-page-header-actions">
                @if($unreadCount > 0)
                    <button type="button" wire:click="markAllRead" class="notif-btn notif-btn-ghost">
                        @svg('heroicon-o-check', 'notif-btn-icon')
                        <span>{{ __('Mark all read') }}</span>
                    </button>
                @endif
                @if($notifications->isNotEmpty())
                    <button type="button"
                            wire:click="deleteAll"
                            onclick="return confirm('{{ __('Delete every notification? This cannot be undone.') }}')"
                            class="notif-btn notif-btn-ghost notif-btn-danger">
                        @svg('heroicon-o-trash', 'notif-btn-icon')
                        <span>{{ __('Delete all') }}</span>
                    </button>
                @endif
            </div>
        </header>

        {{-- ── Toolbar ──────────────────────────────────────────── --}}
        <div class="notif-toolbar">
            <div class="notif-filter">
                @php
                    $tabs = [
                        'all'    => __('All'),
                        'unread' => __('Unread'),
                        'read'   => __('Read'),
                    ];
                @endphp
                @foreach($tabs as $key => $label)
                    <button type="button"
                            wire:click="$set('filter', '{{ $key }}')"
                            class="notif-tab {{ $filter === $key ? 'is-active' : '' }}">
                        {{ $label }}
                        @if($key === 'unread' && $unreadCount > 0)
                            <span class="notif-tab-count">{{ $unreadCount }}</span>
                        @endif
                    </button>
                @endforeach
            </div>

            @if(count($selected) > 0)
                <div class="notif-bulk">
                    <span class="notif-bulk-info">
                        {{ count($selected) }} {{ __('selected') }}
                    </span>
                    <button type="button" wire:click="bulkMarkRead" class="notif-btn notif-btn-ghost">
                        @svg('heroicon-o-check', 'notif-btn-icon')
                        <span>{{ __('Mark read') }}</span>
                    </button>
                    <button type="button" wire:click="bulkMarkUnread" class="notif-btn notif-btn-ghost">
                        @svg('heroicon-o-envelope', 'notif-btn-icon')
                        <span>{{ __('Mark unread') }}</span>
                    </button>
                    <button type="button"
                            wire:click="bulkDelete"
                            onclick="return confirm('{{ __('Delete the selected notifications?') }}')"
                            class="notif-btn notif-btn-danger">
                        @svg('heroicon-o-trash', 'notif-btn-icon')
                        <span>{{ __('Delete') }}</span>
                    </button>
                </div>
            @endif
        </div>

        {{-- ── List ─────────────────────────────────────────────── --}}
        @if($notifications->isEmpty())
            <div class="notif-empty">
                @svg('heroicon-o-inbox', 'notif-empty-icon')
                <div class="notif-empty-title">
                    @switch($filter)
                        @case('unread') {{ __('No unread notifications.') }} @break
                        @case('read')   {{ __('No read notifications yet.') }} @break
                        @default        {{ __('No notifications yet.') }}
                    @endswitch
                </div>
                <div class="notif-empty-sub">{{ __('We will let you know when something new happens.') }}</div>
            </div>
        @else
            <div class="notif-select-all-row">
                <label class="notif-checkbox">
                    <input type="checkbox" wire:model.live="selectAll">
                    <span></span>
                </label>
                <span class="notif-select-all-label">{{ __('Select all visible') }}</span>
            </div>

            <ul class="notif-list">
                @foreach($notifications as $notif)
                    @php
                        $offre       = $notif->type === 'offre' ? $notif->offre : null;
                        $offreActive = $offre && (bool) $offre->is_published
                            && (! $offre->deadline || $offre->deadline >= now()->startOfDay());

                        $typeLabel = match ($notif->type) {
                            'offre'       => __('Job offer'),
                            'validated'   => __('Validated'),
                            'rejected'    => __('Rejected'),
                            'application' => __('Application'),
                            'result'      => __('Result'),
                            default       => __('Info'),
                        };

                        $typeIcon = match ($notif->type) {
                            'offre'       => 'heroicon-o-briefcase',
                            'validated'   => 'heroicon-o-check-circle',
                            'rejected'    => 'heroicon-o-x-circle',
                            'application' => 'heroicon-o-paper-airplane',
                            'result'      => 'heroicon-o-trophy',
                            default       => 'heroicon-o-bell',
                        };
                    @endphp

                    <li class="notif-row {{ $notif->is_read ? 'is-read' : 'is-unread' }}" wire:key="notif-{{ $notif->id }}">
                        <label class="notif-checkbox">
                            <input type="checkbox" wire:model.live="selected" value="{{ $notif->id }}">
                            <span></span>
                        </label>

                        <span class="notif-type type-{{ $notif->type }}" aria-label="{{ $typeLabel }}">
                            @svg($typeIcon, 'notif-type-icon')
                        </span>

                        <div class="notif-body">
                            <div class="notif-row-head">
                                <span class="notif-type-tag type-{{ $notif->type }}">{{ $typeLabel }}</span>
                                @if(! $notif->is_read) <span class="notif-dot" title="{{ __('Unread') }}"></span> @endif
                                <span class="notif-row-time">{{ $notif->created_at->diffForHumans() }}</span>
                            </div>

                            <div class="notif-row-title">{{ $notif->title }}</div>
                            <p class="notif-row-message">{{ $notif->message }}</p>

                            @if($offre)
                                <div class="notif-offer">
                                    <div class="notif-offer-title">{{ $offre->title }}</div>

                                    <div class="notif-offer-meta">
                                        @if($offre->contract_type)
                                            <span class="notif-chip">{{ $offre->contract_type }}</span>
                                        @endif
                                        @if($offre->domain)
                                            <span class="notif-chip">{{ $offre->domain }}</span>
                                        @endif
                                        @if($offre->location)
                                            <span class="notif-chip">{{ $offre->location }}</span>
                                        @endif
                                        @if($offre->deadline)
                                            <span class="notif-chip notif-chip-amber">
                                                @svg('heroicon-o-clock', 'notif-chip-icon')
                                                {{ __('admin.deadline') }} {{ $offre->deadline->format('d/m/Y') }}
                                            </span>
                                        @endif
                                        @unless($offreActive)
                                            <span class="notif-chip notif-chip-muted">
                                                @svg('heroicon-o-no-symbol', 'notif-chip-icon')
                                                {{ __('Offer closed') }}
                                            </span>
                                        @endunless
                                    </div>

                                    @if($offre->description)
                                        <p class="notif-offer-desc">
                                            {{ \Illuminate\Support\Str::limit($offre->description, 220) }}
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="notif-row-actions">
                            <a href="{{ $notif->url }}" class="notif-btn notif-btn-primary">
                                <span>
                                    @if($offreActive)
                                        {{ __('View offer') }}
                                    @else
                                        {{ __('Open') }}
                                    @endif
                                </span>
                                @svg('heroicon-o-arrow-right', 'notif-btn-icon')
                            </a>

                            <button type="button"
                                    wire:click="toggleRead({{ $notif->id }})"
                                    class="notif-btn notif-btn-icon-only notif-btn-ghost"
                                    title="{{ $notif->is_read ? __('Mark unread') : __('Mark read') }}">
                                @if($notif->is_read)
                                    @svg('heroicon-o-envelope', 'notif-btn-icon')
                                @else
                                    @svg('heroicon-o-envelope-open', 'notif-btn-icon')
                                @endif
                            </button>

                            <button type="button"
                                    wire:click="deleteOne({{ $notif->id }})"
                                    onclick="return confirm('{{ __('Delete this notification?') }}')"
                                    class="notif-btn notif-btn-icon-only notif-btn-ghost notif-btn-danger"
                                    title="{{ __('Delete') }}">
                                @svg('heroicon-o-trash', 'notif-btn-icon')
                            </button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-filament-panels::page>
