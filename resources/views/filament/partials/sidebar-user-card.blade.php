@php
    /** @var \App\Models\User|null $authUser */
    $authUser  = auth()->user();
    $candidate = $authUser?->candidate ?? null;
    $isAdmin   = $authUser && method_exists($authUser, 'hasRole') && $authUser->hasRole('admin');

    if ($candidate) {
        $displayName = trim(($candidate->first_name ?? '') . ' ' . ($candidate->last_name ?? ''))
            ?: ($authUser->name ?? __('nav.user'));
    } else {
        $displayName = $authUser->name ?? __('nav.user');
    }

    $initials = collect(preg_split('/\s+/u', $displayName))
        ->filter()
        ->map(fn ($word) => mb_substr($word, 0, 1))
        ->take(2)
        ->implode('');
    $initials = $initials !== '' ? mb_strtoupper($initials) : '·';

    if ($isAdmin) {
        $roleLabel = __('nav.role_admin');
    } elseif ($candidate) {
        $roleLabel = __('nav.role_candidate');
    } else {
        $roleLabel = __('nav.role_member');
    }

    $panelId   = filament()->getCurrentPanel()?->getId();
    $profileUrl = match ($panelId) {
        'candidate' => route('filament.candidate.pages.my-profile'),
        default     => $isAdmin
            ? route('filament.admin.pages.dashboard')
            : (auth()->check() ? route('filament.candidate.pages.dashboard') : '#'),
    };
@endphp

@auth
    <div class="s2e-sidebar-footer">
        <a href="{{ $profileUrl }}" class="s2e-user-card" title="{{ $displayName }}">
            <span class="s2e-user-card__avatar" aria-hidden="true">
                {{ $initials }}
            </span>
            <span class="s2e-user-card__body">
                <span class="s2e-user-card__name">{{ $displayName }}</span>
                <span class="s2e-user-card__role">
                    <span class="s2e-user-card__role-dot"></span>
                    {{ $roleLabel }}
                </span>
            </span>
            <svg class="s2e-user-card__chevron" width="14" height="14" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
            </svg>
        </a>
    </div>
@endauth
