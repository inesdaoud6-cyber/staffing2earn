<x-filament-panels::page>
    @vite('resources/css/candidate-application-space.css')

    <div class="candidate-application-space">
    @if ($isAdminViewing)
        <div
            style="background:#fef3c7;border:1px solid #f59e0b;border-radius:10px;padding:0.75rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;">
            <span style="color:#92400e;font-weight:600;">🛡️ {{ __('Admin view — detailed scores visible') }}</span>
            <a href="/admin"
                style="background:#f59e0b;color:#fff;padding:0.35rem 0.9rem;border-radius:6px;font-size:0.85rem;text-decoration:none;">←
                {{ __('Back to admin') }}</a>
        </div>
    @endif

    @if ($applicationView === 'list')
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
    @endif

    @if ($selectedApplicationId && $applicationView === 'details')
        @include('filament.candidate.pages.partials.application-space-details')
    @elseif ($selectedApplicationId && $applicationView === 'progress')
        @php
            $selApp = $this->getSelectedApplication();
            $steps = $this->getStepperSteps();
        @endphp

        @if ($selApp)
            <div class="as-detail-toolbar">
                <button type="button" wire:click="clearApplicationSelection" class="as-back-btn">
                    ← {{ __('candidate.applications.back_to_list') }}
                </button>
                <div class="as-detail-title">
                    {{ __('candidate.applications.progress_heading') }} — {{ $selApp->offre?->title ?? __('Open application') }}
                </div>
                <button type="button" wire:click="showApplicationDetails({{ $selApp->id }})" class="as-back-btn">
                    {{ __('candidate.applications.action_details') }}
                </button>
            </div>

            @include('filament.candidate.pages.partials.application-space-pipeline', [
                'steps' => $steps,
                'selectedOfferStep' => $selectedOfferStep,
            ])

            <div class="as-panel">
                @if ($this->isFinalDecisionStep((int) $selectedOfferStep, $selApp))
                    <h3 class="as-panel-title">{{ __('candidate.applications.panel_decision_title') }}</h3>
                    @if ($selApp->status === 'validated' && ! $selApp->offre_id)
                        <p class="as-panel-desc">{{ __('candidate.applications.panel_free_potential') }}</p>
                    @elseif ($selApp->status === 'validated')
                        <p class="as-panel-desc">{{ __('candidate.applications.panel_decision_validated') }}</p>
                    @elseif ($this->isFreeAwaitingTestAssignment($selApp))
                        <p class="as-panel-desc">{{ __('candidate.applications.panel_free_awaiting_test') }}</p>
                    @elseif ($selApp->status === 'rejected')
                        <p class="as-panel-desc">{{ __('candidate.applications.panel_decision_rejected') }}</p>
                    @else
                        <p class="as-panel-desc">{{ __('candidate.applications.panel_decision_waiting') }}</p>
                    @endif
                @elseif ((int) $selectedOfferStep === 1)
                    <h3 class="as-panel-title">{{ __('candidate.applications.panel_cv_title') }}</h3>
                    @if ($this->isFreeAwaitingTestAssignment($selApp))
                        <p class="as-panel-desc">{{ __('candidate.applications.panel_cv_accepted_awaiting_test') }}</p>
                    @else
                        <p class="as-panel-desc">{{ __('candidate.applications.panel_cv_desc') }}</p>
                    @endif
                    @php $cvUrl = $selApp->cvPublicUrl(); @endphp
                    @if ($cvUrl)
                        <div class="as-cv-actions">
                            <button type="button"
                                wire:click="showCvPreview"
                                class="as-cv-toggle-btn"
                                @disabled($cvPreviewVisible)
                                wire:loading.attr="disabled"
                                wire:target="showCvPreview">
                                {{ __('candidate.applications.show_cv') }}
                            </button>
                            <button type="button"
                                wire:click="hideCvPreview"
                                class="as-cv-toggle-btn as-cv-toggle-btn--muted"
                                @disabled(! $cvPreviewVisible)
                                wire:loading.attr="disabled"
                                wire:target="hideCvPreview">
                                {{ __('candidate.applications.hide_cv') }}
                            </button>
                        </div>

                        @if ($cvPreviewVisible)
                            <div class="as-cv-viewer" aria-label="{{ __('candidate.applications.open_cv') }}">
                                <iframe
                                    src="{{ $cvUrl }}#view=FitH"
                                    title="{{ __('candidate.applications.open_cv') }}"
                                    class="as-cv-viewer__frame"
                                ></iframe>
                            </div>
                            <p class="as-cv-viewer__hint">
                                <a href="{{ $cvUrl }}" target="_blank" rel="noopener noreferrer" class="as-primary-link">
                                    {{ __('candidate.applications.open_cv_new_tab') }}
                                </a>
                            </p>
                        @endif
                    @else
                        <p class="as-muted">{{ __('candidate.applications.no_cv') }}</p>
                    @endif
                @else
                    @php
                        $offerStep = (int) $selectedOfferStep;
                        $responseLevel = $this->responseLevelForOfferStep($offerStep);
                        $testNumber = $this->testNumberForOfferStep($offerStep);
                        $readonly = $this->hasTestResponse($selApp, $responseLevel);
                        $writable = $this->isTestStepWritable($selApp, $responseLevel);
                        $rows = $readonly ? $this->getTestReviewRows($selApp, $offerStep) : [];
                    @endphp
                    <h3 class="as-panel-title">
                        {{ __('candidate.applications.panel_test_title', ['n' => $testNumber]) }}
                    </h3>

                    @if ($readonly)
                        <p class="as-panel-desc">{{ __('candidate.applications.panel_test_readonly') }}</p>
                        @if (count($rows) > 0)
                            <div class="as-review-table-wrap">
                                <table class="as-review-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('candidate.applications.col_question') }}</th>
                                            <th>{{ __('candidate.applications.col_your_answer') }}</th>
                                            <th>{{ __('candidate.applications.col_score') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($rows as $row)
                                            <tr>
                                                <td>{{ $row['question'] }}</td>
                                                <td>{{ $row['answer'] }}</td>
                                                <td>{{ $row['score'] ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="as-muted">{{ __('candidate.applications.no_questions_loaded') }}</p>
                        @endif

                        @php
                            $publishedScoreLabel = $this->publishedMainScoreLabel($selApp);
                        @endphp
                        @if ($publishedScoreLabel)
                            <p class="as-score-banner">
                                {{ __('candidate.applications.published_score', ['score' => $publishedScoreLabel]) }}
                            </p>
                        @endif
                    @elseif ($writable)
                        <p class="as-panel-desc">{{ __('candidate.applications.panel_test_editable') }}</p>
                        <a href="{{ $this->takeTestUrl($selApp) }}" class="as-primary-btn">
                            {{ __('Take the test') }} →
                        </a>
                    @else
                        <p class="as-muted">{{ __('candidate.applications.panel_test_locked_msg') }}</p>
                    @endif
                @endif
            </div>
        @endif
    @else
        @include('filament.candidate.pages.partials.application-space-list')
    @endif
    </div>
</x-filament-panels::page>
