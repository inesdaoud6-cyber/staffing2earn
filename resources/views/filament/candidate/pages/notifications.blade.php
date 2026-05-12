<x-filament-panels::page>
    @vite('resources/css/candidate-notifications.css')

    @can('send-candidate-notification')
        <div
            style="background:#fef3c7;border:1px solid #f59e0b;border-radius:10px;padding:0.75rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;">
            <span style="color:#92400e;font-weight:600;">🛡️ Vue administrateur</span>
            <a href="/admin"
                style="background:#f59e0b;color:#fff;padding:0.35rem 0.9rem;border-radius:6px;font-size:0.85rem;text-decoration:none;">←
                {{ __('Back to admin') }}</a>
        </div>
    @endcan

    <div class="notif-header">
        <div class="notif-header-icon">🔔</div>
        <div>
            <h2>{{ __('Mes Notifications') }}</h2>
            <p>{{ __('Restez informé de l\'avancement de vos candidatures') }}</p>
        </div>
    </div>

    <div class="notif-list">
        @forelse($notifications as $notif)
            <div class="notif-item {{ !$notif->is_read ? 'unread' : '' }}">
                <div class="notif-icon {{ $notif->type }}">
                    @if($notif->type === 'validated') ✅
                    @elseif($notif->type === 'rejected') ❌
                    @elseif($notif->type === 'offre') 💼
                    @else ℹ️
                    @endif
                </div>
                <div class="notif-content">
                    <div class="notif-title">{{ $notif->title }}</div>
                    <div class="notif-message">{{ $notif->message }}</div>
                    <div class="notif-time">{{ $notif->created_at->diffForHumans() }}</div>
                </div>
                @if(!$notif->is_read)
                    <div class="notif-unread-dot"></div>
                @endif
            </div>
        @empty
            <div class="notif-empty">
                🔕 {{ __('Aucune notification pour le moment.') }}<br>
                <small>{{ __('Nous vous notifierons dès qu\'il y a du nouveau !') }}</small>
            </div>
        @endforelse
    </div>

    @if($offresNouvelles->count() > 0)
        <div class="offres-section">
            <div class="offres-section-title">💼 {{ __('nav.job_offers') }}</div>
            @foreach($offresNouvelles as $offre)
                <div class="offre-notif-card">
                    <div>
                        <div class="offre-notif-title">{{ $offre->title }}</div>
                        <div class="offre-notif-meta">
                            @if($offre->contract_type)
                                <span class="offre-badge">{{ $offre->contract_type }}</span>
                            @endif
                            {{ $offre->domain }}
                            @if($offre->deadline)
                                · {{ __('admin.deadline') }} {{ $offre->deadline->format('d/m/Y') }}
                            @endif
                        </div>
                    </div>
                    <a href="/candidate/choix-candidature" class="postuler-btn">{{ __('Apply to an Offer') }} →</a>
                </div>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>