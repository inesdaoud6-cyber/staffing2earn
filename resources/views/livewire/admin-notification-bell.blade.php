<div class="nb-wrapper" wire:poll.30s="refresh" x-data="{}" @click.outside="$wire.close()" @keydown.escape.window="$wire.close()">
    <button type="button"
            class="nb-trigger"
            wire:click="toggle"
            aria-label="{{ __('Notifications') }}"
            aria-haspopup="true"
            :aria-expanded="$wire.open">
        <span class="nb-icon" aria-hidden="true">
            @svg('heroicon-o-bell', ['width' => 20, 'height' => 20])
        </span>
        @if($unreadCount > 0)
            <span class="nb-badge" aria-label="{{ $unreadCount }} {{ __('unread') }}">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    @if($open)
        <div class="nb-panel" role="menu" aria-label="{{ __('Notifications') }}">
            <div class="nb-panel-header">
                <span class="nb-panel-title">
                    @svg('heroicon-o-bell', 'nb-panel-title-icon')
                    {{ __('Notifications') }}
                </span>
                @if($unreadCount > 0)
                    <button type="button" class="nb-link" wire:click="markAllRead">
                        {{ __('Mark all read') }}
                    </button>
                @endif
            </div>

            <div class="nb-panel-body">
                @forelse($recent as $n)
                    @php
                        $itemIcon = match ($n['type']) {
                            'application' => 'heroicon-o-clipboard-document-list',
                            default       => 'heroicon-o-bell',
                        };
                    @endphp
                    <div class="nb-item {{ $n['is_read'] ? '' : 'nb-item-unread' }} nb-item-type-{{ $n['type'] }}"
                         wire:key="anb-{{ $n['id'] }}"
                         wire:click="openNotification({{ $n['id'] }})"
                         role="link"
                         tabindex="0"
                         title="{{ __('Open') }}">
                        <div class="nb-item-icon">
                            @svg($itemIcon, 'nb-item-icon-svg')
                        </div>
                        <div class="nb-item-body">
                            <div class="nb-item-title">{{ $n['title'] }}</div>
                            <div class="nb-item-message">{{ $n['message'] }}</div>
                            <div class="nb-item-meta">{{ $n['created_at'] }}</div>
                        </div>
                        @unless($n['is_read'])
                            <span class="nb-dot" aria-hidden="true"></span>
                        @endunless
                    </div>
                @empty
                    <div class="nb-empty">
                        @svg('heroicon-o-inbox', 'nb-empty-icon')
                        <span>{{ __('No notifications yet.') }}</span>
                    </div>
                @endforelse
            </div>

            <div class="nb-panel-footer">
                <a href="{{ route('filament.admin.pages.admin-notifications') }}" class="nb-link">
                    {{ __('View all') }}
                    @svg('heroicon-o-arrow-right', 'nb-link-icon')
                </a>
            </div>
        </div>
    @endif
</div>
