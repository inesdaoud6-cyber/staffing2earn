<x-filament-panels::page>
    @vite('resources/css/candidate-choix.css')

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
            <button wire:click="candidatLibre"
                class="cc-btn cc-btn-blue"
                wire:loading.attr="disabled"
                wire:target="candidatLibre">
                <span wire:loading.remove wire:target="candidatLibre">✨ {{ __('Choose this option') }}</span>
                <span wire:loading wire:target="candidatLibre">⏳...</span>
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
                <div class="offre-card">
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
                    <button wire:click="candidateOffre({{ $offre->id }})"
                        class="offre-apply-btn"
                        wire:loading.attr="disabled"
                        wire:target="candidateOffre({{ $offre->id }})">
                        <span wire:loading.remove wire:target="candidateOffre({{ $offre->id }})">{{ __('Apply to an Offer') }} →</span>
                        <span wire:loading wire:target="candidateOffre({{ $offre->id }})">⏳</span>
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
</x-filament-panels::page>