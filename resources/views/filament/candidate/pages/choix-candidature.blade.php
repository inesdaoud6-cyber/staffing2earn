<x-filament-panels::page>
    @vite('resources/css/candidate-choix.css')

    <script>
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
        <span style="color:#92400e;font-weight:600;">🛡️ {{ __('Vue administrateur') }}</span>
        <a href="/admin" style="background:#f59e0b;color:#fff;padding:0.35rem 0.9rem;border-radius:6px;font-size:0.85rem;text-decoration:none;">← {{ __('Back to admin') }}</a>
    </div>
    @endcan

    <div class="cc-stack">
        {{-- Free application — top horizontal card --}}
        <section class="cc-card cc-card--horizontal cc-card--free">
            <div class="cc-icon cc-icon-blue" aria-hidden="true">📋</div>
            <div class="cc-card__body">
                <h2 class="cc-title">{{ __('Free Application') }}</h2>
                <p class="cc-desc">{{ __('Apply without a specific offer. The admin will suggest a suitable test for you.') }}</p>
            </div>
            <div class="cc-card__action">
                @php
                    $freeAction = $this->getOfferApplyAction(null);
                    $freeLabel = $this->offerApplyButtonLabel($freeAction);
                @endphp
                @php
                    $freeDisabled = $this->offerApplyButtonIsDisabled($freeAction);
                    $freeCanStart = $this->offerApplyButtonCanStart($freeAction);
                @endphp
                <button type="button"
                    @class([
                        $this->offerApplyButtonClass($freeAction, 'cc-btn cc-btn-blue'),
                    ])
                    @disabled($freeDisabled)
                    @if ($freeCanStart)
                        wire:click="startApplyFree"
                        wire:loading.attr="disabled"
                        wire:target="startApplyFree"
                    @endif>
                    @if ($freeDisabled)
                        {{ $freeLabel }}
                    @else
                        <span wire:loading.remove wire:target="startApplyFree">
                            @if ($freeAction === 'apply')
                                ✨ {{ __('Choose this option') }}
                            @else
                                {{ $freeLabel }} →
                            @endif
                        </span>
                        <span wire:loading wire:target="startApplyFree">⏳...</span>
                    @endif
                </button>
            </div>
        </section>

        {{-- Job offers — bottom horizontal card --}}
        <section class="cc-card cc-card--offers">
            <div class="cc-card__header">
                <div class="cc-icon cc-icon-green" aria-hidden="true">💼</div>
                <div class="cc-card__body">
                    <h2 class="cc-title">{{ __('nav.job_offers') }}</h2>
                    <p class="cc-desc">{{ __('Apply to a specific job offer published by the company.') }}</p>
                </div>
            </div>

            <div class="cc-search">
                <input type="text"
                    wire:model.live.debounce.300ms="search"
                    class="cc-search__input"
                    placeholder="🔍 {{ __('Rechercher une offre') }}...">
            </div>

            @if ($offres->count() > 0)
                <div class="offres-list">
                    @foreach ($offres as $offre)
                        <div class="offre-card" id="offre-{{ $offre->id }}">
                            <div class="offre-card__info">
                                <div class="offre-title">{{ $offre->title }}</div>
                                <div class="offre-meta">
                                    @if ($offre->contract_type)
                                        <span class="offre-badge">{{ $offre->contract_type }}</span>
                                    @endif
                                    {{ $offre->domain }}
                                    @if ($offre->deadline)
                                        · {{ __('admin.deadline') }} {{ $offre->deadline->format('d/m/Y') }}
                                    @endif
                                </div>
                            </div>
                            <div class="offre-card__actions">
                                <button type="button"
                                    wire:click="showOfferDetails({{ $offre->id }})"
                                    class="offre-details-btn"
                                    wire:loading.attr="disabled"
                                    wire:target="showOfferDetails({{ $offre->id }})">
                                    {{ __('candidate.applications.action_details') }}
                                </button>
                                @include('filament.candidate.pages.partials.choix-offer-apply-button', ['offreId' => $offre->id])
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="cc-empty">
                    @if ($search)
                        🔍 {{ __('Aucun résultat pour') }} "{{ $search }}"
                    @else
                        {{ __('No offers available') }}
                    @endif
                </div>
            @endif
        </section>
    </div>

    @if ($offerDetailsOpen)
        @php $offerDetail = $this->getOfferDetailsData(); @endphp
        @if ($offerDetail)
            @php $offre = $offerDetail['offre']; @endphp
            <div class="cc-dialog-backdrop" wire:click.self="closeOfferDetails">
                <div class="cc-dialog cc-dialog--offer" role="dialog" aria-modal="true" aria-labelledby="offerDetailsTitle">
                    <button type="button" class="cv-dialog-close" wire:click="closeOfferDetails" aria-label="{{ __('Close') }}">✕</button>

                    <header class="cc-offer-detail-hero">
                        <div class="cv-dialog-eyebrow">{{ __('candidate.applications.details_heading') }}</div>
                        <h2 id="offerDetailsTitle" class="cc-offer-detail-hero__title">{{ $offre->title }}</h2>
                        @if ($offre->domain)
                            <p class="cc-offer-detail-hero__sub">{{ $offre->domain }}</p>
                        @endif
                    </header>

                    <div class="cc-offer-detail-stats">
                        <div class="cc-offer-detail-stat">
                            <span class="cc-offer-detail-stat__value">{{ $offerDetail['tests_count'] }}</span>
                            <span class="cc-offer-detail-stat__label">{{ __('candidate.applications.detail_tests_count') }}</span>
                        </div>
                        <div class="cc-offer-detail-stat">
                            <span class="cc-offer-detail-stat__value">{{ $offerDetail['assessment_levels'] }}</span>
                            <span class="cc-offer-detail-stat__label">{{ __('candidate.applications.detail_levels') }}</span>
                        </div>
                        <div class="cc-offer-detail-stat">
                            <span class="cc-offer-detail-stat__value">{{ $offerDetail['total_applicants'] }}</span>
                            <span class="cc-offer-detail-stat__label">{{ __('candidate.applications.detail_applicants_total') }}</span>
                        </div>
                        <div class="cc-offer-detail-stat cc-offer-detail-stat--accent">
                            <span class="cc-offer-detail-stat__value">{{ $offerDetail['other_applicants'] }}</span>
                            <span class="cc-offer-detail-stat__label">{{ __('candidate.applications.detail_other_candidates') }}</span>
                        </div>
                    </div>

                    @if ($offre->description)
                        <section class="cc-offer-detail-section">
                            <h3 class="cc-offer-detail-section__title">{{ __('candidate.applications.detail_description') }}</h3>
                            <div class="cc-offer-detail-prose">{!! nl2br(e($offre->description)) !!}</div>
                        </section>
                    @endif

                    <section class="cc-offer-detail-section">
                        <h3 class="cc-offer-detail-section__title">{{ __('candidate.applications.detail_offer_info') }}</h3>
                        <dl class="cc-offer-detail-dl">
                            @if ($offre->location)
                                <div>
                                    <dt>{{ __('candidate.applications.detail_location') }}</dt>
                                    <dd>{{ $offre->location }}</dd>
                                </div>
                            @endif
                            @if ($offre->contract_type)
                                <div>
                                    <dt>{{ __('candidate.applications.detail_contract') }}</dt>
                                    <dd>{{ $offre->contract_type }}</dd>
                                </div>
                            @endif
                            @if ($offre->deadline)
                                <div>
                                    <dt>{{ __('candidate.applications.detail_deadline') }}</dt>
                                    <dd>{{ $offre->deadline->format('d/m/Y') }}</dd>
                                </div>
                            @endif
                        </dl>
                    </section>

                    @if (count($offerDetail['tests']) > 0)
                        <section class="cc-offer-detail-section">
                            <h3 class="cc-offer-detail-section__title">{{ __('candidate.applications.detail_tests_list') }}</h3>
                            <p class="cc-offer-detail-hint">{{ __('candidate.applications.detail_tests_list_hint') }}</p>
                            <ol class="cc-offer-detail-tests">
                                <li>
                                    <span class="cc-offer-detail-tests__step">{{ __('candidate.applications.caption_cv') }}</span>
                                    <span class="cc-offer-detail-tests__name">{{ __('candidate.applications.detail_cv_step') }}</span>
                                </li>
                                @foreach ($offerDetail['tests'] as $testRow)
                                    <li>
                                        <span class="cc-offer-detail-tests__step">{{ $testRow['label'] }}</span>
                                        <span class="cc-offer-detail-tests__name">{{ $testRow['name'] }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        </section>
                    @endif

                    <footer class="cc-offer-detail-footer">
                        <button type="button" class="offre-details-btn" wire:click="closeOfferDetails">
                            {{ __('Close') }}
                        </button>
                        @php
                            $detailApplyAction = $this->getOfferApplyAction($offre->id);
                            $detailApplyLabel = $this->offerApplyButtonLabel($detailApplyAction);
                            $detailDisabled = $this->offerApplyButtonIsDisabled($detailApplyAction);
                            $detailCanStart = $this->offerApplyButtonCanStart($detailApplyAction);
                        @endphp
                        <button type="button"
                            @class([$this->offerApplyButtonClass($detailApplyAction)])
                            @disabled($detailDisabled)
                            @if ($detailCanStart)
                                wire:click="applyFromOfferDetails"
                            @endif>
                            {{ $detailApplyLabel }}@if ($detailCanStart) →@endif
                        </button>
                    </footer>
                </div>
            </div>
        @endif
    @endif

    @if ($cvDialogOpen)
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
                <div class="cv-dialog-option {{ $hasProfileCv ? '' : 'cv-dialog-option-disabled' }}">
                    <div class="cv-dialog-option-icon">👤</div>
                    <div class="cv-dialog-option-body">
                        <div class="cv-dialog-option-title">{{ __('Use my profile CV') }}</div>
                        <div class="cv-dialog-option-desc">
                            @if ($hasProfileCv)
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

                <div class="cv-dialog-option">
                    <div class="cv-dialog-option-icon">📤</div>
                    <div class="cv-dialog-option-body">
                        <div class="cv-dialog-option-title">{{ __('Upload a new CV') }}</div>
                        <div class="cv-dialog-option-desc">
                            {{ __('PDF only, 5 MB max. The uploaded file will also replace your profile CV.') }}
                        </div>
                        <input type="file" wire:model="newCv" accept="application/pdf" class="cv-dialog-file">

                        <div wire:loading wire:target="newCv" class="cv-dialog-hint">⏳ {{ __('Uploading...') }}</div>

                        @if ($newCv)
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
