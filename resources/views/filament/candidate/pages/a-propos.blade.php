<x-filament-panels::page>
    @vite('resources/css/candidate-apropos.css')

    @can('manage-candidates')
    <div style="background:#fef3c7;border:1px solid #f59e0b;border-radius:10px;padding:0.75rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;">
        <span style="color:#92400e;font-weight:600;">🛡️ Vue administrateur</span>
        <a href="/admin" style="background:#f59e0b;color:#fff;padding:0.35rem 0.9rem;border-radius:6px;font-size:0.85rem;text-decoration:none;">← {{ __('Back to admin') }}</a>
    </div>
    @endcan

    <div class="apropos-hero">
        <h1>🏢 {{ __('About Staffing2Earn') }}</h1>
        <p>{{ __('An intelligent recruitment platform that connects talent with the best opportunities.') }}</p>
    </div>

    <div class="apropos-cards">
        <div class="apropos-card">
            <div class="apropos-card-icon blue">🧪</div>
            <h3>{{ __('Smart Tests') }}</h3>
            <p>{{ __('Create and manage multi-level assessment tests') }}</p>
        </div>
        <div class="apropos-card">
            <div class="apropos-card-icon cyan">👥</div>
            <h3>{{ __('Track candidates throughout recruitment') }}</h3>
            <p>{{ __('This is your personal recruitment space. Start your journey with us today.') }}</p>
        </div>
        <div class="apropos-card">
            <div class="apropos-card-icon purple">📊</div>
            <h3>{{ __('Test Results') }}</h3>
            <p>{{ __('Analyze performance and results easily') }}</p>
        </div>
    </div>

    <div class="temoignages-section">
        <div class="temoignages-title">💬 {{ __('nav.testimonials') }}</div>

        @if($temoignages->count() > 0)
        <div class="temoignages-grid">
            @foreach($temoignages as $t)
            <div class="temoignage-card">
                <div class="temoignage-quote">"</div>
                <div class="temoignage-stars">
                    @for($i = 1; $i <= 5; $i++){{ $i <= $t->note ? '⭐' : '☆' }}@endfor
                </div>
                <div class="temoignage-text">{{ $t->contenu }}</div>
                <div class="temoignage-author">
                    <div class="temoignage-avatar">{{ strtoupper(substr($t->user->name, 0, 2)) }}</div>
                    <div>
                        <div class="temoignage-name">{{ $t->user->name }}</div>
                        <div class="temoignage-date">{{ $t->created_at->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-temoignages">{{ __('Aucun témoignage pour le moment. Soyez le premier !') }} 👇</div>
        @endif
    </div>

    @if($hasApplied)
    <div class="form-section">
        <div class="form-section-title">✍️ {{ __('temoignage.share_title') }}</div>

        @if($myTemoignage)
        <div style="background:#f0fdf4;border-radius:10px;padding:1rem;color:#065f46;font-size:0.9rem;margin-bottom:1rem;">
            ✅ {{ __('temoignage.pending_approval') }} — {{ __('temoignage.pending_msg') }}
        </div>
        @endif

        @error('contenu')
        <div style="background:#fff0f3;border:1px solid #fca5a5;border-radius:8px;padding:0.6rem 0.9rem;margin-bottom:1rem;font-size:0.85rem;color:#dc2626;">
            ⚠️ {{ $message }}
        </div>
        @enderror

        <div class="form-group">
            <label class="form-label">{{ __('temoignage.share_desc') }}</label>
            <textarea wire:model.live="contenu"
                class="form-textarea"
                placeholder="{{ __('temoignage.placeholder') }}"></textarea>
            <div style="text-align:right;font-size:0.75rem;color:{{ strlen($contenu) > 450 ? '#dc2626' : '#9ca3af' }};margin-top:0.25rem;">
                {{ strlen($contenu) }}/500
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('admin.rating') }}</label>
            <div class="stars-input">
                @for($i = 1; $i <= 5; $i++)
                <button type="button"
                    wire:click="$set('note', {{ $i }})"
                    class="star-btn {{ $note >= $i ? 'active' : '' }}">★</button>
                @endfor
                <span style="margin-left:0.5rem;color:#6b7280;font-size:0.9rem;align-self:center;">{{ $note }}/5</span>
            </div>
        </div>

        <button wire:click="submitTemoignage"
            wire:loading.attr="disabled"
            wire:target="submitTemoignage"
            class="submit-btn">
            <span wire:loading.remove wire:target="submitTemoignage">🚀 {{ __('temoignage.submit') }}</span>
            <span wire:loading wire:target="submitTemoignage">⏳ {{ __('In Progress') }}...</span>
        </button>
    </div>
    @endif
</x-filament-panels::page>