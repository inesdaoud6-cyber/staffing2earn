<x-filament-panels::page>
    @vite('resources/css/candidate-choix.css')

    <script>
        // When the candidate lands here from a notification (URL hash `#offre-ID`),
        // scroll the card into view and pulse it so the right offer is obvious.
        document.addEventListener('DOMContentLoaded', () => {
            const hash = window.location.hash;
            if (!hash || !hash.startsWith('#offre-')) return;

            const target = document.querySelector(hash);
            if (!target) return;

            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            target.classList.add('offre-card-highlight');
            setTimeout(() => target.classList.remove('offre-card-highlight'), 2400);
        });
    </script>

    @can('manage-candidates')
    <div style="background:#fef3c7;border:1px solid #f59e0b;border-radius:10px;padding:0.75rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;">
        <span style="color:#92400e;font-weight:600;">🛡️ Vue administrateur</span>
        <a href="/admin" style="background:#f59e0b;color:#fff;padding:0.35rem 0.9rem;border-radius:6px;font-size:0.85rem;text-decoration:none;">← {{ __('Back to admin') }}</a>
    </div>
    @endcan

    <div class="cc-grid">
        <div class="cc-card">
            <div class="cc-icon cc-icon-blue">📋</div>
            <div class="cc-title">{{ __('Free Application') }}</div>
            <div class="cc-desc">{{ __('Apply without a specific offer. The admin will suggest a suitable test for you.') }}</div>
            <button wire:click="startApplyFree"
                class="cc-btn cc-btn-blue"
                wire:loading.attr="disabled"
                wire:target="startApplyFree">
                <span wire:loading.remove wire:target="startApplyFree">✨ {{ __('Choose this option') }}</span>
                <span wire:loading wire:target="startApplyFree">⏳...</span>
            </button>
        </div>

        <div class="cc-card">
            <div class="cc-icon cc-icon-green">💼</div>
            <div class="cc-title">{{ __('Apply to an Offer') }}</div>
            <div class="cc-desc">{{ __('Apply to a specific job offer published by the company.') }}</div>

            <div style="width:100%;margin-bottom:1rem;">
                <input type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="🔍 {{ __('Rechercher une offre') }}..."
                    style="width:100%;padding:0.6rem 0.9rem;border:1.5px solid #e5e7eb;border-radius:8px;font-size:0.875rem;outline:none;box-sizing:border-box;">
            </div>

            @if($offres->count() > 0)
            <div class="offres-list" style="width:100%;">
                @foreach($offres as $offre)
                <div class="offre-card" id="offre-{{ $offre->id }}">
                    <div>
                        <div class="offre-title">{{ $offre->title }}</div>
                        <div class="offre-meta">
                            @if($offre->contract_type)
                            <span class="offre-badge">{{ $offre->contract_type }}</span>
                            @endif
                            {{ $offre->domain }}
                            @if($offre->deadline)
                                · {{ __('admin.deadline') }} {{ $offre->deadline->format('d/m/Y') }}
                            @endif
                        </div>
                    </div>
                    <button wire:click="startApplyOffre({{ $offre->id }})"
                        class="offre-apply-btn"
                        wire:loading.attr="disabled"
                        wire:target="startApplyOffre({{ $offre->id }})">
                        <span wire:loading.remove wire:target="startApplyOffre({{ $offre->id }})">{{ __('Apply to an Offer') }} →</span>
                        <span wire:loading wire:target="startApplyOffre({{ $offre->id }})">⏳</span>
                    </button>
                </div>
                @endforeach
            </div>
            @else
            <div class="cc-empty">
                @if($search)
                    🔍 {{ __('Aucun résultat pour') }} "{{ $search }}"
                @else
                    {{ __('No offers available') }}
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- CV-choice modal --}}
    @if($cvDialogOpen)
    <div class="cv-dialog-backdrop" wire:click.self="cancelCvDialog">
        <div class="cv-dialog" role="dialog" aria-modal="true" aria-labelledby="cvDialogTitle">
            <button type="button" class="cv-dialog-close" wire:click="cancelCvDialog" aria-label="{{ __('Close') }}">✕</button>

            <div class="cv-dialog-header">
                <div class="cv-dialog-icon">📄</div>
                <div>
                    <div class="cv-dialog-eyebrow">{{ __('Applying to') }}</div>
                    <h3 id="cvDialogTitle" class="cv-dialog-title">{{ $pendingOffreTitle }}</h3>
                </div>
            </div>

            <p class="cv-dialog-desc">
                {{ __('Choose which CV to send to the admin for review. Your application stays in "pending" status until the admin approves it.') }}
            </p>

            <div class="cv-dialog-options">
                {{-- Option 1: profile CV --}}
                <div class="cv-dialog-option {{ $hasProfileCv ? '' : 'cv-dialog-option-disabled' }}">
                    <div class="cv-dialog-option-icon">👤</div>
                    <div class="cv-dialog-option-body">
                        <div class="cv-dialog-option-title">{{ __('Use my profile CV') }}</div>
                        <div class="cv-dialog-option-desc">
                            @if($hasProfileCv)
                                {{ __('Send the CV currently saved on your profile.') }}
                            @else
                                {{ __('No CV on file yet — upload one to use this option, or upload a new CV below.') }}
                            @endif
                        </div>
                        <button type="button"
                                class="cv-dialog-btn cv-dialog-btn-primary"
                                wire:click="applyWithProfileCv"
                                wire:loading.attr="disabled"
                                wire:target="applyWithProfileCv"
                                @disabled(! $hasProfileCv)>
                            <span wire:loading.remove wire:target="applyWithProfileCv">✅ {{ __('Send profile CV') }}</span>
                            <span wire:loading wire:target="applyWithProfileCv">⏳ {{ __('Submitting...') }}</span>
                        </button>
                    </div>
                </div>

                {{-- Option 2: new CV --}}
                <div class="cv-dialog-option">
                    <div class="cv-dialog-option-icon">📤</div>
                    <div class="cv-dialog-option-body">
                        <div class="cv-dialog-option-title">{{ __('Upload a new CV') }}</div>
                        <div class="cv-dialog-option-desc">
                            {{ __('PDF only, 5 MB max. The uploaded file will also replace your profile CV.') }}
                        </div>
                        <input type="file" wire:model="newCv" accept="application/pdf" class="cv-dialog-file">

                        <div wire:loading wire:target="newCv" class="cv-dialog-hint">⏳ {{ __('Uploading...') }}</div>

                        @if($newCv)
                            <div class="cv-dialog-hint cv-dialog-hint-ok">
                                ✔ {{ __('Ready to submit:') }} {{ method_exists($newCv, 'getClientOriginalName') ? $newCv->getClientOriginalName() : __('file selected') }}
                            </div>
                        @endif

                        @error('newCv') <div class="cv-dialog-error">{{ $message }}</div> @enderror

                        <button type="button"
                                class="cv-dialog-btn cv-dialog-btn-success"
                                wire:click="applyWithNewCv"
                                wire:loading.attr="disabled"
                                wire:target="applyWithNewCv,newCv"
                                @disabled(! $newCv)>
                            <span wire:loading.remove wire:target="applyWithNewCv">⬆ {{ __('Submit application with this CV') }}</span>
                            <span wire:loading wire:target="applyWithNewCv">⏳ {{ __('Submitting...') }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="cv-dialog-footer">
                <button type="button" class="cv-dialog-btn cv-dialog-btn-ghost" wire:click="cancelCvDialog">
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>
    @endif
</x-filament-panels::page>
