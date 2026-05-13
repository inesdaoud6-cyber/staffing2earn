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
        {{-- Personal Information --}}
        <div class="profile-section">
            <div class="profile-section-header">
                <div class="profile-section-title">👤 {{ __('Personal Information') }}</div>
                @cannot('view-candidate-scores')
                    @if($editingPersonal)
                        <div class="profile-section-actions">
                            <button type="button" wire:click="savePersonal" class="profile-section-edit save">
                                💾 <span>{{ __('Save') }}</span>
                            </button>
                            <button type="button" wire:click="cancelPersonal" class="profile-section-edit cancel">
                                ✕ <span>{{ __('Cancel') }}</span>
                            </button>
                        </div>
                    @else
                        <button type="button" wire:click="editPersonal" class="profile-section-edit"
                                title="{{ __('Edit') }}" aria-label="{{ __('Edit') }}">
                            ✏️ <span>{{ __('Edit') }}</span>
                        </button>
                    @endif
                @endcannot
            </div>

            <div class="profile-row">
                <span class="profile-row-label">{{ __('First Name') }}</span>
                @if($editingPersonal)
                    <input type="text" wire:model="firstName" class="profile-row-input"
                           placeholder="{{ __('First Name') }}">
                @else
                    <span class="profile-row-value">{{ $candidate?->first_name ?? '—' }}</span>
                @endif
            </div>
            @error('firstName') <div class="profile-row-error">{{ $message }}</div> @enderror

            <div class="profile-row">
                <span class="profile-row-label">{{ __('Last Name') }}</span>
                @if($editingPersonal)
                    <input type="text" wire:model="lastName" class="profile-row-input"
                           placeholder="{{ __('Last Name') }}">
                @else
                    <span class="profile-row-value">{{ $candidate?->last_name ?? '—' }}</span>
                @endif
            </div>
            @error('lastName') <div class="profile-row-error">{{ $message }}</div> @enderror

            <div class="profile-row">
                <span class="profile-row-label">{{ __('admin.phone') }}</span>
                @if($editingPersonal)
                    <input type="tel" wire:model="phone" class="profile-row-input"
                           placeholder="{{ __('admin.phone') }}">
                @else
                    <span class="profile-row-value">{{ $candidate?->phone ?? '—' }}</span>
                @endif
            </div>
            @error('phone') <div class="profile-row-error">{{ $message }}</div> @enderror

            <div class="profile-row">
                <span class="profile-row-label">{{ __('temoignage.birth_date') }}</span>
                @if($editingPersonal)
                    <input type="date" wire:model="birthDate" class="profile-row-input">
                @else
                    <span class="profile-row-value">{{ $candidate?->birth_date?->format('d/m/Y') ?? '—' }}</span>
                @endif
            </div>
            @error('birthDate') <div class="profile-row-error">{{ $message }}</div> @enderror

            <div class="profile-row">
                <span class="profile-row-label">{{ __('temoignage.address') }}</span>
                @if($editingPersonal)
                    <input type="text" wire:model="address" class="profile-row-input"
                           placeholder="{{ __('temoignage.address') }}">
                @else
                    <span class="profile-row-value">{{ $candidate?->address ?? '—' }}</span>
                @endif
            </div>
            @error('address') <div class="profile-row-error">{{ $message }}</div> @enderror
        </div>

        {{-- Account --}}
        <div class="profile-section">
            <div class="profile-section-header">
                <div class="profile-section-title">🔐 {{ __('Account') }}</div>
                @cannot('view-candidate-scores')
                    @if($editingAccount)
                        <div class="profile-section-actions">
                            <button type="button" wire:click="saveAccount" class="profile-section-edit save">
                                💾 <span>{{ __('Save') }}</span>
                            </button>
                            <button type="button" wire:click="cancelAccount" class="profile-section-edit cancel">
                                ✕ <span>{{ __('Cancel') }}</span>
                            </button>
                        </div>
                    @else
                        <button type="button" wire:click="editAccount" class="profile-section-edit"
                                title="{{ __('Edit') }}" aria-label="{{ __('Edit') }}">
                            ✏️ <span>{{ __('Edit') }}</span>
                        </button>
                    @endif
                @endcannot
            </div>

            <div class="profile-row">
                <span class="profile-row-label">{{ __('Username') }}</span>
                @if($editingAccount)
                    <input type="text" wire:model="userName" class="profile-row-input">
                @else
                    <span class="profile-row-value">{{ $user->name }}</span>
                @endif
            </div>
            @error('userName') <div class="profile-row-error">{{ $message }}</div> @enderror

            <div class="profile-row">
                <span class="profile-row-label">{{ __('Email') }}</span>
                @if($editingAccount)
                    <input type="email" wire:model="userEmail" class="profile-row-input">
                @else
                    <span class="profile-row-value">{{ $user->email }}</span>
                @endif
            </div>
            @error('userEmail') <div class="profile-row-error">{{ $message }}</div> @enderror

            <div class="profile-row">
                <span class="profile-row-label">{{ __('Date') }}</span>
                <span class="profile-row-value">{{ $user->created_at->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    {{-- CV Card --}}
    <div class="profile-section profile-cv-section">
        <div class="profile-section-header">
            <div class="profile-section-title">📄 CV</div>
            <div class="profile-section-actions">
                @if($candidate?->cv_path)
                    <button type="button" wire:click="toggleCv" class="profile-section-edit"
                            title="{{ $showCv ? __('Hide CV') : __('Show CV') }}">
                        @if($showCv)
                            🙈 <span>{{ __('Hide CV') }}</span>
                        @else
                            👁 <span>{{ __('Show CV') }}</span>
                        @endif
                    </button>
                @endif
            </div>
        </div>

        <div class="profile-cv-status">
            @if($candidate?->cv_path)
                <span class="profile-cv-status-ok">
                    ✅ {{ __('CV on file') }}
                </span>
            @else
                <span class="profile-cv-status-empty">
                    ❌ {{ __('No CV uploaded yet.') }}
                </span>
            @endif
        </div>

        @if($showCv && $candidate?->cv_path)
            <div class="profile-cv-viewer">
                <iframe src="{{ asset('storage/' . $candidate->cv_path) }}"
                        title="CV preview"
                        loading="lazy"></iframe>
            </div>
        @endif

        @cannot('view-candidate-scores')
        <div class="profile-cv-upload">
            <label class="profile-cv-upload-label" for="newCvInput">
                <span>📤 {{ __('Replace CV (PDF, max 5 MB)') }}</span>
            </label>
            <div class="profile-cv-upload-row">
                <input id="newCvInput" type="file" wire:model="newCv" accept="application/pdf"
                       class="profile-cv-input">
                <button type="button" wire:click="uploadCv"
                        class="profile-section-edit save"
                        wire:loading.attr="disabled" wire:target="newCv,uploadCv"
                        @disabled(! $newCv)>
                    <span wire:loading.remove wire:target="uploadCv">⬆ {{ __('Upload') }}</span>
                    <span wire:loading wire:target="uploadCv">… {{ __('Uploading') }}</span>
                </button>
                @if($candidate?->cv_path)
                    <button type="button" wire:click="deleteCv"
                            class="profile-section-edit cancel"
                            onclick="return confirm('{{ __('Remove the current CV?') }}')">
                        🗑 <span>{{ __('Remove') }}</span>
                    </button>
                @endif
            </div>
            @error('newCv') <div class="profile-row-error">{{ $message }}</div> @enderror
        </div>
        @endcannot
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
</x-filament-panels::page>
