<x-filament-panels::page>
    @vite('resources/css/candidate-profile.css')

    @if($isAdminViewing)
    <div style="background:#fef3c7;border:1px solid #f59e0b;border-radius:10px;padding:0.75rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;">
        <span style="color:#92400e;font-weight:600;">🛡️ Vue administrateur — informations confidentielles visibles</span>
        <a href="/admin" style="background:#f59e0b;color:#fff;padding:0.35rem 0.9rem;border-radius:6px;font-size:0.85rem;text-decoration:none;">← {{ __('Back to admin') }}</a>
    </div>
    @endif

    <div class="profile-hero">
        <div class="profile-avatar">
            {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>
        <div class="profile-hero-info">
            <h2>{{ $candidate ? $candidate->first_name . ' ' . $candidate->last_name : $user->name }}</h2>
            <p>{{ $user->email }}</p>
            <div class="profile-hero-badge">
                🗓 {{ __('Membre depuis') }} {{ $user->created_at->format('F Y') }}
            </div>
        </div>
    </div>

    <div class="profile-stats">
        <div class="profile-stat">
            <div class="profile-stat-value">{{ $totalApplications }}</div>
            <div class="profile-stat-label">{{ __('Total applications') }}</div>
        </div>
        <div class="profile-stat">
            <div class="profile-stat-value">{{ $validatedApplications }}</div>
            <div class="profile-stat-label">{{ __('Validated') }}</div>
        </div>
        <div class="profile-stat">
            <div class="profile-stat-value">{{ $averageScore }}</div>
            <div class="profile-stat-label">{{ __('Average score') }}</div>
        </div>
    </div>

    <div class="profile-sections">
        <div class="profile-section">
            <div class="profile-section-title">👤 {{ __('Personal Information') }}</div>
            <div class="profile-row">
                <span class="profile-row-label">{{ __('First Name') }}</span>
                <span class="profile-row-value">{{ $candidate?->first_name ?? '—' }}</span>
            </div>
            <div class="profile-row">
                <span class="profile-row-label">{{ __('Last Name') }}</span>
                <span class="profile-row-value">{{ $candidate?->last_name ?? '—' }}</span>
            </div>
            <div class="profile-row">
                <span class="profile-row-label">{{ __('admin.phone') }}</span>
                <span class="profile-row-value">{{ $candidate?->phone ?? '—' }}</span>
            </div>
            <div class="profile-row">
                <span class="profile-row-label">{{ __('temoignage.birth_date') }}</span>
                <span class="profile-row-value">{{ $candidate?->birth_date?->format('d/m/Y') ?? '—' }}</span>
            </div>
            <div class="profile-row">
                <span class="profile-row-label">{{ __('temoignage.address') }}</span>
                <span class="profile-row-value">{{ $candidate?->address ?? '—' }}</span>
            </div>
            <div class="profile-row">
                <span class="profile-row-label">CV</span>
                <span class="profile-row-value">
                    @if($candidate?->cv_path)
                        <a href="{{ asset('storage/' . $candidate->cv_path) }}" target="_blank"
                           style="color:#1a1a8c;font-weight:600;">✅ {{ __('View') }}</a>
                    @else
                        ❌ {{ __('Non uploadé') }}
                    @endif
                </span>
            </div>
        </div>

        <div class="profile-section">
            <div class="profile-section-title">🔐 {{ __('Account') }}</div>
            <div class="profile-row">
                <span class="profile-row-label">{{ __('Username') }}</span>
                <span class="profile-row-value">{{ $user->name }}</span>
            </div>
            <div class="profile-row">
                <span class="profile-row-label">{{ __('Email') }}</span>
                <span class="profile-row-value">{{ $user->email }}</span>
            </div>
            <div class="profile-row">
                <span class="profile-row-label">{{ __('Date') }}</span>
                <span class="profile-row-value">{{ $user->created_at->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    @if($isAdminViewing && ($primaryScore !== null || $secondaryScore !== null))
    <div style="background:#fffbeb;border:1px solid #f59e0b;border-radius:12px;padding:1.25rem;margin-top:1.5rem;">
        <div style="color:#92400e;font-weight:700;font-size:0.95rem;margin-bottom:0.75rem;">🔒 {{ __('admin.status_scores') }} (admin)</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div style="background:#fff;border-radius:8px;padding:0.75rem;border:1px solid #fde68a;">
                <div style="font-size:0.8rem;color:#92400e;">{{ __('admin.primary_score') }}</div>
                <div style="font-size:1.5rem;font-weight:700;color:#b45309;">{{ $primaryScore ?? '—' }}</div>
            </div>
            <div style="background:#fff;border-radius:8px;padding:0.75rem;border:1px solid #fde68a;">
                <div style="font-size:0.8rem;color:#92400e;">{{ __('admin.secondary_score') }}</div>
                <div style="font-size:1.5rem;font-weight:700;color:#b45309;">{{ $secondaryScore ?? '—' }}</div>
            </div>
        </div>
    </div>
    @endif

    @can('edit-candidate-status')
    <div style="background:#eff6ff;border:1px solid #3b82f6;border-radius:12px;padding:1.25rem;margin-top:1rem;">
        <div style="color:#1e40af;font-weight:700;font-size:0.95rem;margin-bottom:0.5rem;">🔒 Statut candidat (admin)</div>
        <div style="color:#374151;font-size:0.9rem;">
            {{ __('Status') }} : <strong>{{ $candidate?->status ?? '—' }}</strong>
        </div>
    </div>
    @endcan

    @cannot('view-candidate-scores')
    <a href="/candidate/account-settings" class="profile-edit-btn">
        ✏️ {{ __('Modifier mon profil') }}
    </a>
    @endcannot
</x-filament-panels::page>