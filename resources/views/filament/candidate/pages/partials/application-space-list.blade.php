<div class="as-list-section">
    <div class="as-list-toolbar">
        <div class="as-list-toolbar__intro">
            <div class="as-table-title">{{ __('candidate.applications.list_heading') }}</div>
            <p class="as-list-subtitle">{{ __('candidate.applications.list_subtitle') }}</p>
        </div>
        <div class="as-filter-bar">
            <label for="as-filter-status" class="as-filter-bar__label">{{ __('candidate.applications.filter_status') }}</label>
            <div class="as-filter-bar__control">
                <select id="as-filter-status" wire:model.live="filterStatus" class="as-filter-select">
                    <option value="">{{ __('candidate.applications.filter_all_statuses') }}</option>
                    <option value="pending">{{ __('Pending') }}</option>
                    <option value="in_progress">{{ __('In Progress') }}</option>
                    <option value="validated">{{ __('Validated') }}</option>
                    <option value="rejected">{{ __('Rejected') }}</option>
                </select>
            </div>
        </div>
    </div>

    @if ($this->applications->total() > 0)
        <div class="as-cards-grid">
            @foreach ($this->applications as $app)
                @php
                    $canCancel = $this->canCancelApplication($app);
                @endphp
                <article class="as-app-card as-app-card--{{ $app->status }}" wire:key="app-card-{{ $app->id }}">
                    <div class="as-app-card__head">
                        <span class="as-app-card__type {{ $app->offre_id ? 'as-app-card__type--job' : 'as-app-card__type--free' }}">
                            {{ $this->applicationCardTypeLabel($app) }}
                        </span>
                        <span class="badge badge-{{ $app->status }}">
                            {{ $this->applicationStatusLabel($app->status) }}
                        </span>
                    </div>

                    <h3 class="as-app-card__title" title="{{ $this->applicationCardTitle($app) }}">
                        {{ $this->applicationCardTitle($app) }}
                    </h3>

                    <ul class="as-app-card__facts">
                        <li>
                            <span class="as-app-card__fact-label">{{ __('Level') }}</span>
                            <span class="as-app-card__fact-value">{{ $app->current_level }}</span>
                        </li>
                        <li>
                            <span class="as-app-card__fact-label">{{ __('Score') }}</span>
                            <span class="as-app-card__fact-value">{{ $this->publishedMainScoreLabel($app) ?? '—' }}</span>
                        </li>
                        @if ($app->test)
                            <li class="as-app-card__facts--full">
                                <span class="as-app-card__fact-label">{{ __('Test') }}</span>
                                <span class="as-app-card__fact-value" title="{{ $app->test->name }}">{{ $app->test->name }}</span>
                            </li>
                        @endif
                    </ul>

                    @can('view-candidate-scores')
                        <div class="as-app-card__admin-scores">
                            {{ __('Primary score') }}: {{ $app->main_score }} ·
                            {{ __('Secondary') }}: {{ $app->secondary_score ?? '—' }}
                        </div>
                    @endcan

                    <p class="as-app-card__date">
                        {{ __('candidate.applications.updated') }} {{ $app->updated_at->diffForHumans() }}
                    </p>

                    <div @class(['as-app-card__actions', 'as-app-card__actions--two' => ! $canCancel])>
                        <button type="button" wire:click="showApplicationDetails({{ $app->id }})"
                            class="as-card-action as-card-action--muted">
                            {{ __('candidate.applications.action_details') }}
                        </button>
                        <button type="button" wire:click="showApplicationProgress({{ $app->id }})"
                            class="as-card-action as-card-action--primary">
                            {{ __('candidate.applications.action_progress') }}
                        </button>
                        @if ($canCancel)
                            <button type="button"
                                wire:click="cancelApplication({{ $app->id }})"
                                wire:loading.attr="disabled"
                                wire:target="cancelApplication({{ $app->id }})"
                                onclick="return confirm(@js(__('Cancel this application? This cannot be undone.')))"
                                class="as-card-action as-card-action--danger">
                                <span wire:loading.remove wire:target="cancelApplication({{ $app->id }})">
                                    {{ __('candidate.applications.action_cancel') }}
                                </span>
                                <span wire:loading wire:target="cancelApplication({{ $app->id }})">
                                    {{ __('Cancelling') }}
                                </span>
                            </button>
                        @endif
                    </div>

                    @can('view-candidate-scores')
                        <a href="/admin/application-progresses/{{ $app->id }}/edit" class="as-app-card__admin-link">
                            {{ __('Edit') }} (admin)
                        </a>
                    @endcan
                </article>
            @endforeach
        </div>

        {{ $this->applications->links('filament.candidate.pages.partials.application-space-pagination') }}
    @else
        <div class="empty-state">
            @if ($filterStatus)
                {{ __('Aucune candidature avec ce statut.') }}
            @else
                {{ __('No applications yet.') }}
            @endif
        </div>
    @endif
</div>
