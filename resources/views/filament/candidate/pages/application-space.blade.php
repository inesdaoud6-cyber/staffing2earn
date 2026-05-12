<x-filament-panels::page>
    @vite('resources/css/candidate-application-space.css')

    @if($isAdminViewing)
    <div style="background:#fef3c7;border:1px solid #f59e0b;border-radius:10px;padding:0.75rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;">
        <span style="color:#92400e;font-weight:600;">🛡️ {{ __('Admin view — detailed scores visible') }}</span>
        <a href="/admin" style="background:#f59e0b;color:#fff;padding:0.35rem 0.9rem;border-radius:6px;font-size:0.85rem;text-decoration:none;">← {{ __('Back to admin') }}</a>
    </div>
    @endif

    <div class="as-header">
        <div>
            <h2>📁 {{ __('My Applications') }}</h2>
            <p>{{ $candidateName }} — {{ __('Complete tracking of your files') }}</p>
        </div>
        <div style="font-size:3.5rem;opacity:0.25;">📂</div>
    </div>

    <div class="as-stats">
        <div class="as-stat">
            <div class="as-stat-icon blue">📋</div>
            <div>
                <div class="as-stat-value">{{ $totalApplications }}</div>
                <div class="as-stat-label">{{ __('Total applications') }}</div>
            </div>
        </div>
        <div class="as-stat">
            <div class="as-stat-icon cyan">⭐</div>
            <div>
                <div class="as-stat-value">{{ $averageScore }}</div>
                <div class="as-stat-label">{{ __('Average score') }}</div>
            </div>
        </div>
    </div>

    <div class="as-table-section">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:0.75rem;">
            <div class="as-table-title">📄 {{ __('Application history') }}</div>
            <select wire:model.live="filterStatus"
                style="padding:0.4rem 0.75rem;border:1.5px solid #ede9fe;border-radius:8px;font-size:0.85rem;color:#374151;background:white;outline:none;">
                <option value="">{{ __('Tous les statuts') }}</option>
                <option value="pending">{{ __('Pending') }}</option>
                <option value="in_progress">{{ __('In Progress') }}</option>
                <option value="validated">{{ __('Validated') }}</option>
                <option value="rejected">{{ __('Rejected') }}</option>
            </select>
        </div>

        @if($applications->count() > 0)
        <div class="app-cards-grid">
            @foreach($applications as $app)
            <div class="app-card">
                <div class="app-card-header">
                    <span class="app-card-candidate">{{ $candidateName }}</span>
                    <span class="badge badge-{{ $app->status }}">
                        {{ match($app->status) {
                            'pending'     => __('Pending'),
                            'in_progress' => __('In Progress'),
                            'validated'   => __('Validated'),
                            'rejected'    => __('Rejected'),
                            default       => $app->status
                        } }}
                    </span>
                </div>

                <div class="app-card-title">
                    {{ $app->offre?->title ?? __('Open application') }}
                </div>

                <div class="app-card-meta">
                    <span>{{ __('Level') }} {{ $app->current_level }}</span>
                    <span style="font-weight:700;color:#1a1a8c;">
                        {{ $app->score_published ? $app->main_score . '/100' : '—' }}
                    </span>
                    @if($app->test)
                    <span style="color:#6b7280;font-size:0.8rem;">🧪 {{ $app->test->name }}</span>
                    @endif
                </div>

                @can('view-candidate-scores')
                <div style="background:#fef3c7;border-radius:6px;padding:0.4rem 0.75rem;margin:0.5rem 0;font-size:0.82rem;color:#92400e;">
                    🔒 {{ __('Primary score') }}: {{ $app->main_score }} /
                    {{ __('Secondary') }}: {{ $app->secondary_score ?? '—' }}
                </div>
                @endcan

                <div class="app-card-footer">
                    <span class="app-card-date">{{ $app->created_at->diffForHumans() }}</span>
                    <div style="display:flex;gap:0.5rem;align-items:center;">
                        @if(in_array($app->status, ['in_progress', 'pending']))
                        <a href="/candidate/take-test"
                           style="background:linear-gradient(135deg,#1a1a8c,#3730a3);color:white;padding:0.3rem 0.75rem;border-radius:7px;font-size:0.78rem;font-weight:700;text-decoration:none;">
                            📝 {{ __('Take the test') }}
                        </a>
                        @else
                        <a href="/candidate/applications" class="app-card-details-btn">
                            {{ __('View details') }} →
                        </a>
                        @endif
                        @can('view-candidate-scores')
                        <a href="/admin/application-progresses/{{ $app->id }}/edit"
                           style="background:#f59e0b;color:#fff;padding:0.3rem 0.7rem;border-radius:6px;font-size:0.78rem;text-decoration:none;">
                            {{ __('Edit') }}
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-state">
            @if($filterStatus)
                {{ __('Aucune candidature avec ce statut.') }}
            @else
                {{ __('No applications yet.') }}
            @endif
        </div>
        @endif
    </div>
</x-filament-panels::page>