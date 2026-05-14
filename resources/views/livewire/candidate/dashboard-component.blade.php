<div>
    @vite('resources/css/candidate-dashboard.css')

    @if($isAdminViewing)
    <div style="background:#fef3c7;border:1px solid #f59e0b;border-radius:10px;padding:0.75rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;">
        <span style="color:#92400e;font-weight:600;">🛡️ {{__('Vous consultez cet espace en tant qu administrateur')}}</span>
        <a href="/admin" style="background:#f59e0b;color:#fff;padding:0.35rem 0.9rem;border-radius:6px;font-size:0.85rem;text-decoration:none;">← {{ __('Back to admin') }}</a>
    </div>
    @endif

    <div class="dash-hero" style="background:linear-gradient(135deg,var(--navy, #1E1EA8),var(--violet, #7C3AED));padding:2rem;border-radius:16px;color:white;display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
        <div>
            <h1 style="font-size:1.8rem;font-weight:800;margin-bottom:0.5rem;">👋 {{ __('Hello') }}, {{ $userName }} !</h1>
            <p style="color:rgba(255,255,255,0.8);">{{ __('Welcome to your candidate space. Manage your applications easily.') }}</p>
        </div>
        <a href="/candidate/notifications" class="dash-notif-btn" style="background:rgba(255,255,255,0.1);padding:0.8rem;border-radius:12px;position:relative;">
            <span class="dash-notif-icon" style="font-size:1.5rem;">🔔</span>
            @if($unreadCount > 0)
            <span class="dash-notif-badge" style="position:absolute;top:-5px;right:-5px;background:red;color:white;font-size:0.7rem;font-weight:700;padding:2px 6px;border-radius:999px;">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
            @endif
        </a>
    </div>

    <div class="dash-cards" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(250px, 1fr));gap:1.5rem;margin-bottom:2.5rem;">
        <div class="dash-card" style="background:white;padding:1.5rem;border-radius:16px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:1.2rem;box-shadow:0 4px 6px rgba(0,0,0,0.02);">
            <div class="dash-card-icon blue" style="background:#eff6ff;color:#3b82f6;width:50px;height:50px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">📋</div>
            <div>
                <div class="dash-card-value" style="font-size:1.5rem;font-weight:800;">{{ $totalApplications }}</div>
                <div class="dash-card-label" style="color:#64748b;font-size:0.85rem;">{{ __('Total applications') }}</div>
            </div>
        </div>
        <div class="dash-card" style="background:white;padding:1.5rem;border-radius:16px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:1.2rem;box-shadow:0 4px 6px rgba(0,0,0,0.02);">
            <div class="dash-card-icon cyan" style="background:#fef3c7;color:#d97706;width:50px;height:50px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">⏳</div>
            <div>
                <div class="dash-card-value" style="font-size:1.5rem;font-weight:800;">{{ $pendingApplications }}</div>
                <div class="dash-card-label" style="color:#64748b;font-size:0.85rem;">{{ __('Pending') }}</div>
            </div>
        </div>
        <div class="dash-card" style="background:white;padding:1.5rem;border-radius:16px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:1.2rem;box-shadow:0 4px 6px rgba(0,0,0,0.02);">
            <div class="dash-card-icon purple" style="background:#f0fdf4;color:#16a34a;width:50px;height:50px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">✅</div>
            <div>
                <div class="dash-card-value" style="font-size:1.5rem;font-weight:800;">{{ $completedApplications }}</div>
                <div class="dash-card-label" style="color:#64748b;font-size:0.85rem;">{{ __('Validated') }}</div>
            </div>
        </div>
    </div>

    <div class="dash-actions" style="display:flex;gap:1rem;margin-bottom:2.5rem;">
        <a href="/candidate/choix-candidature" class="dash-btn test-btn" style="background:#1e1ea8;color:white;padding:0.8rem 1.5rem;border-radius:10px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:0.5rem;">🚀 {{ __('New application') }}</a>
        <a href="/candidate/take-test" class="dash-btn dash-btn-secondary" style="background:white;color:#1e1ea8;border:1px solid #1e1ea8;padding:0.8rem 1.5rem;border-radius:10px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:0.5rem;">📝 {{ __('Take the test') }}</a>
    </div>

    <div class="dash-table-section" style="background:white;padding:1.5rem;border-radius:16px;border:1px solid #e2e8f0;box-shadow:0 4px 6px rgba(0,0,0,0.02);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
            <div class="dash-table-title" style="font-size:1.2rem;font-weight:700;">📌 {{ __('Recent applications') }}</div>
            <button wire:click="refreshData"
                wire:loading.attr="disabled"
                wire:target="refreshData"
                style="display:flex;align-items:center;gap:0.4rem;padding:0.4rem 1rem;border-radius:8px;border:1.5px solid #ede9fe;background:white;color:#1a1a8c;font-size:0.85rem;font-weight:600;cursor:pointer;">
                <span wire:loading.remove wire:target="refreshData">🔄 {{ __('Actualiser') }}</span>
                <span wire:loading wire:target="refreshData">⏳</span>
            </button>
        </div>

        @if($recentApplications && $recentApplications->count() > 0)
        <div class="app-cards-grid" style="display:flex;flex-direction:column;gap:1rem;">
            @foreach($recentApplications as $app)
            <div class="app-card" style="border:1px solid #f1f5f9;border-radius:12px;padding:1.2rem;background:#f8fafc;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="display:flex;align-items:center;gap:0.8rem;margin-bottom:0.5rem;">
                        <span style="font-weight:700;color:#0f172a;">{{ $app->offre?->title ?? __('Open application') }}</span>
                        <span style="font-size:0.75rem;padding:0.2rem 0.6rem;border-radius:999px;font-weight:600;
                            {{ $app->status === 'validated' ? 'background:#dcfce7;color:#166534;' : ($app->status === 'pending' ? 'background:#fef3c7;color:#92400e;' : 'background:#e0e7ff;color:#3730a3;') }}">
                            {{ match($app->status) {
                                'pending'     => __('Pending'),
                                'in_progress' => __('In Progress'),
                                'validated'   => __('Validated'),
                                'rejected'    => __('Rejected'),
                                default       => $app->status
                            } }}
                        </span>
                    </div>
                    <div style="font-size:0.85rem;color:#64748b;display:flex;gap:1rem;align-items:center;">
                        <span>{{ __('Niveau:') }} {{ $app->current_level }}</span>
                        <span style="font-weight:700;color:#1e1ea8;">{{ __('Score:') }} {{ $app->main_score }}/100</span>
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:0.8rem;color:#94a3b8;margin-bottom:0.5rem;">{{ $app->created_at->diffForHumans() }}</div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-state" style="text-align:center;padding:3rem 0;color:#64748b;">
            {{ __('No applications yet.') }}
        </div>
        @endif
    </div>
</div>
