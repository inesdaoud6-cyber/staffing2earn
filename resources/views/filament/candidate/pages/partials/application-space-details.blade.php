@php
    $detail = $this->getApplicationDetailsPageData();
@endphp

@if ($detail)
    @php
        $selApp = $detail['application'];
        $offre = $detail['offre'];
    @endphp

    <div class="as-detail-page">
        <div class="as-detail-toolbar">
            <button type="button" wire:click="clearApplicationSelection" class="as-back-btn">
                ← {{ __('candidate.applications.back_to_list') }}
            </button>
            <div class="as-detail-title">
                {{ __('candidate.applications.details_heading') }}
            </div>
            <button type="button" wire:click="showApplicationProgress({{ $selApp->id }})" class="as-back-btn">
                {{ __('candidate.applications.action_progress') }} →
            </button>
        </div>

        <header class="as-detail-hero">
            <p class="as-detail-hero__eyebrow">
                <span class="as-app-card__type {{ $detail['is_free'] ? 'as-app-card__type--free' : 'as-app-card__type--job' }}">
                    {{ $this->applicationCardTypeLabel($selApp) }}
                </span>
                <span class="badge badge-{{ $selApp->status }}">{{ $this->applicationStatusLabel($selApp->status) }}</span>
            </p>
            <h1 class="as-detail-hero__title">{{ $this->applicationCardTitle($selApp) }}</h1>
            @if ($offre?->domain)
                <p class="as-detail-hero__subtitle">{{ $offre->domain }}</p>
            @endif
        </header>

        <div class="as-detail-stats">
            @if (! $detail['is_free'])
                <div class="as-detail-stat">
                    <span class="as-detail-stat__value">{{ $detail['tests_count'] }}</span>
                    <span class="as-detail-stat__label">{{ __('candidate.applications.detail_tests_count') }}</span>
                </div>
                <div class="as-detail-stat">
                    <span class="as-detail-stat__value">{{ $detail['assessment_levels'] }}</span>
                    <span class="as-detail-stat__label">{{ __('candidate.applications.detail_levels') }}</span>
                </div>
            @endif
            <div class="as-detail-stat">
                <span class="as-detail-stat__value">{{ $detail['total_applicants'] }}</span>
                <span class="as-detail-stat__label">{{ __('candidate.applications.detail_applicants_total') }}</span>
            </div>
            <div class="as-detail-stat as-detail-stat--accent">
                <span class="as-detail-stat__value">{{ $detail['other_applicants'] }}</span>
                <span class="as-detail-stat__label">{{ __('candidate.applications.detail_other_candidates') }}</span>
            </div>
        </div>

        <div class="as-panel as-offer-details">
            @if ($offre)
                @if ($offre->description)
                    <section class="as-detail-section">
                        <h2 class="as-detail-section__title">{{ __('candidate.applications.detail_description') }}</h2>
                        <div class="as-detail-section__body as-detail-prose">
                            {!! nl2br(e($offre->description)) !!}
                        </div>
                    </section>
                @endif

                <section class="as-detail-section">
                    <h2 class="as-detail-section__title">{{ __('candidate.applications.detail_offer_info') }}</h2>
                    <dl class="as-offer-details__list">
                        @if ($offre->title)
                            <div class="as-offer-details__item">
                                <dt>{{ __('candidate.applications.detail_title') }}</dt>
                                <dd>{{ $offre->title }}</dd>
                            </div>
                        @endif
                        @if ($offre->domain)
                            <div class="as-offer-details__item">
                                <dt>{{ __('candidate.applications.detail_domain') }}</dt>
                                <dd>{{ $offre->domain }}</dd>
                            </div>
                        @endif
                        @if ($offre->location)
                            <div class="as-offer-details__item">
                                <dt>{{ __('candidate.applications.detail_location') }}</dt>
                                <dd>{{ $offre->location }}</dd>
                            </div>
                        @endif
                        @if ($offre->contract_type)
                            <div class="as-offer-details__item">
                                <dt>{{ __('candidate.applications.detail_contract') }}</dt>
                                <dd>{{ $offre->contract_type }}</dd>
                            </div>
                        @endif
                        @if ($offre->deadline)
                            <div class="as-offer-details__item">
                                <dt>{{ __('candidate.applications.detail_deadline') }}</dt>
                                <dd>{{ $offre->deadline->format('d/m/Y') }}</dd>
                            </div>
                        @endif
                        <div class="as-offer-details__item">
                            <dt>{{ __('candidate.applications.detail_levels') }}</dt>
                            <dd>{{ $offre->levels_count }}</dd>
                        </div>
                        <div class="as-offer-details__item">
                            <dt>{{ __('candidate.applications.detail_tests_count') }}</dt>
                            <dd>{{ $detail['tests_count'] }}</dd>
                        </div>
                    </dl>
                </section>

                @if (count($detail['tests']) > 0)
                    <section class="as-detail-section">
                        <h2 class="as-detail-section__title">{{ __('candidate.applications.detail_tests_list') }}</h2>
                        <p class="as-panel-desc">{{ __('candidate.applications.detail_tests_list_hint') }}</p>
                        <ol class="as-detail-tests">
                            <li class="as-detail-tests__cv">
                                <span class="as-detail-tests__step">{{ __('candidate.applications.caption_cv') }}</span>
                                <span class="as-detail-tests__name">{{ __('candidate.applications.detail_cv_step') }}</span>
                            </li>
                            @foreach ($detail['tests'] as $testRow)
                                <li>
                                    <span class="as-detail-tests__step">{{ $testRow['label'] }}</span>
                                    <span class="as-detail-tests__name">{{ $testRow['name'] }}</span>
                                </li>
                            @endforeach
                        </ol>
                    </section>
                @endif
            @else
                <section class="as-detail-section">
                    <h2 class="as-detail-section__title">{{ __('candidate.applications.details_heading') }}</h2>
                    <p class="as-panel-desc">{{ __('candidate.applications.free_details_desc') }}</p>
                    <p class="as-panel-desc">{{ __('candidate.applications.free_applicants_hint', ['count' => $detail['other_applicants']]) }}</p>
                </section>
            @endif

            <section class="as-detail-section as-detail-section--muted">
                <h2 class="as-detail-section__title">{{ __('candidate.applications.detail_your_application') }}</h2>
                <dl class="as-offer-details__list">
                    <div class="as-offer-details__item">
                        <dt>{{ __('candidate.applications.applied_on') }}</dt>
                        <dd>{{ $selApp->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div class="as-offer-details__item">
                        <dt>{{ __('candidate.applications.updated') }}</dt>
                        <dd>{{ $selApp->updated_at->diffForHumans() }}</dd>
                    </div>
                    @if ($selApp->current_level)
                        <div class="as-offer-details__item">
                            <dt>{{ __('candidate.applications.detail_current_level') }}</dt>
                            <dd>{{ $selApp->current_level }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            <div class="as-offer-details__actions">
                <button type="button" wire:click="showApplicationProgress({{ $selApp->id }})" class="as-card-action as-card-action--primary">
                    {{ __('candidate.applications.action_progress') }}
                </button>
                <button type="button" wire:click="clearApplicationSelection" class="as-card-action as-card-action--muted">
                    {{ __('candidate.applications.back_to_list') }}
                </button>
            </div>
        </div>
    </div>
@endif
