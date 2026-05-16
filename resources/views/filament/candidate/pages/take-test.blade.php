@php
    $locale = app()->getLocale();
@endphp

<x-filament-panels::page>
    @vite('resources/css/candidate-application-space.css')

    @if ($this->pageStatus === 'test' && $this->wholeTestExpiresAtUnix)
        <div wire:poll.30s="pollTestTimer" class="hidden" aria-hidden="true"></div>
    @endif

    @if ($this->pageStatus === 'no_application')
        <div class="rounded-xl border border-violet-200 bg-violet-50 p-10 text-center dark:border-violet-900 dark:bg-violet-950/40">
            <div class="mb-4 text-4xl">📋</div>
            <h2 class="mb-2 text-xl font-bold text-violet-950 dark:text-violet-100">{{ __('candidate.take_test_no_application_title') }}</h2>
            <p class="mb-6 text-gray-600 dark:text-gray-400">{{ __('candidate.take_test_no_application_body') }}</p>
            <x-filament::button tag="a" href="/candidate/choix-candidature" color="primary">
                {{ __('candidate.take_test_new_application') }}
            </x-filament::button>
        </div>

    @elseif ($this->pageStatus === 'waiting_admin')
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-10 text-center dark:border-amber-900 dark:bg-amber-950/40">
            <div class="mb-4 text-4xl">⏳</div>
            <h2 class="mb-2 text-xl font-bold text-amber-950 dark:text-amber-100">{{ __('candidate.take_test_waiting_admin_title') }}</h2>
            <p class="text-amber-900/80 dark:text-amber-200/80">{{ __('candidate.take_test_waiting_admin_body') }}</p>
        </div>

    @elseif ($this->pageStatus === 'no_questions')
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-10 text-center dark:border-amber-900 dark:bg-amber-950/40">
            <div class="mb-4 text-4xl">⚠️</div>
            <h2 class="mb-2 text-xl font-bold text-amber-950 dark:text-amber-100">{{ __('candidate.take_test_no_questions_title') }}</h2>
            <p class="mb-6 text-amber-900/80 dark:text-amber-200/80">{{ __('candidate.take_test_no_questions_body') }}</p>
            <x-filament::button tag="a" href="/candidate/applications" color="gray">
                {{ __('nav.my_applications') }}
            </x-filament::button>
        </div>

    @elseif ($this->pageStatus === 'waiting_test_assignment')
        <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-10 text-center dark:border-indigo-900 dark:bg-indigo-950/40">
            <div class="mb-4 text-4xl">📎</div>
            <h2 class="mb-2 text-xl font-bold text-indigo-950 dark:text-indigo-100">{{ __('candidate.take_test_waiting_assignment_title') }}</h2>
            <p class="text-indigo-900/80 dark:text-indigo-200/80">{{ __('candidate.take_test_waiting_assignment_body') }}</p>
        </div>

    @elseif ($this->pageStatus === 'level_eligibility_failed')
        <div class="rounded-xl border border-red-200 bg-red-50 p-10 text-center dark:border-red-900 dark:bg-red-950/40">
            <div class="mb-4 text-4xl">❌</div>
            <h2 class="mb-2 text-xl font-bold text-red-950 dark:text-red-100">{{ __('candidate.take_test_eligibility_failed_title') }}</h2>
            @include('filament.candidate.pages.partials.take-test-result-summary', [
                'testScore' => $this->getSubmittedTestScorePercent(),
                'eligibilityPassed' => false,
                'eligibilityThreshold' => $this->getEligibilityThresholdPercent(),
                'applicationScore' => null,
                'pendingManual' => false,
                'showAutoEligibility' => true,
            ])
            <p class="mt-4 text-red-900/80 dark:text-red-200/80">{{ __('candidate.take_test_eligibility_failed_help') }}</p>
        </div>

    @elseif ($this->pageStatus === 'awaiting_final_validation')
        <div class="rounded-xl border border-green-200 bg-green-50 p-10 text-center dark:border-green-900 dark:bg-green-950/40">
            <div class="mb-4 text-4xl">✅</div>
            <h2 class="mb-2 text-xl font-bold text-green-950 dark:text-green-100">{{ __('candidate.take_test_eligibility_passed_title') }}</h2>
            @include('filament.candidate.pages.partials.take-test-result-summary', [
                'testScore' => $this->getSubmittedTestScorePercent(),
                'eligibilityPassed' => true,
                'eligibilityThreshold' => $this->getEligibilityThresholdPercent(),
                'applicationScore' => $this->getApplicationScorePercent(),
                'pendingManual' => false,
                'showAutoEligibility' => true,
            ])
            <p class="mt-4 text-green-900/80 dark:text-green-200/80">{{ __('candidate.take_test_awaiting_final_validation_body') }}</p>
        </div>

    @elseif ($this->pageStatus === 'waiting_level_validation')
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-10 text-center dark:border-blue-900 dark:bg-blue-950/40">
            <div class="mb-4 text-4xl">🎯</div>
            <h2 class="mb-2 text-xl font-bold text-blue-950 dark:text-blue-100">
                {{ __('candidate.take_test_level_submitted_title', ['level' => $this->currentLevel]) }}
            </h2>
            @include('filament.candidate.pages.partials.take-test-result-summary', [
                'testScore' => $this->getSubmittedTestScorePercent(),
                'eligibilityPassed' => $this->isFullyAutoScoredLevel() ? $this->didPassEligibility() : null,
                'eligibilityThreshold' => $this->getEligibilityThresholdPercent(),
                'applicationScore' => $this->getApplicationScorePercent(),
                'pendingManual' => $this->hasPendingManualReview(),
                'showAutoEligibility' => $this->isFullyAutoScoredLevel() && $this->didPassEligibility() !== null,
            ])
            <p class="text-blue-900/80 dark:text-blue-200/80">{{ __('candidate.take_test_level_submitted_body') }}</p>
        </div>

    @elseif ($this->pageStatus === 'all_validated')
        <div class="rounded-xl border border-green-200 bg-green-50 p-10 text-center dark:border-green-900 dark:bg-green-950/40">
            <div class="mb-4 text-4xl">🏆</div>
            <h2 class="mb-2 text-xl font-bold text-green-950 dark:text-green-100">{{ __('candidate.take_test_all_validated_title') }}</h2>
            <p class="text-green-900/80 dark:text-green-200/80">{{ __('candidate.take_test_all_validated_body') }}</p>
        </div>

    @elseif ($this->pageStatus === 'test')
        @if ($this->wholeTestExpiresAtUnix)
            <div
                wire:ignore
                class="fixed inset-x-0 z-50 border-b border-amber-500/30 bg-amber-950 px-4 py-3 text-center shadow-lg dark:bg-amber-950"
                style="top: 3.5rem;"
                x-data="{
                    expiresAt: {{ $this->wholeTestExpiresAtUnix }},
                    tick: Math.floor(Date.now() / 1000),
                    interval: null,
                    fired: false,
                    init() {
                        this.interval = setInterval(() => {
                            this.tick = Math.floor(Date.now() / 1000);
                            const left = Math.max(0, this.expiresAt - this.tick);
                            if (left <= 0 && !this.fired) {
                                this.fired = true;
                                clearInterval(this.interval);
                                $wire.handleTestTimeout();
                            }
                        }, 1000);
                    },
                    get left() {
                        return Math.max(0, this.expiresAt - this.tick);
                    },
                    get hourMin() {
                        const s = this.left;
                        if (s <= 0) {
                            return '0:00';
                        }
                        const totalMin = Math.ceil(s / 60);
                        const h = Math.floor(totalMin / 60);
                        const m = totalMin % 60;
                        return String(h) + ':' + String(m).padStart(2, '0');
                    },
                    get subline() {
                        const s = this.left;
                        if (s <= 0 || s >= 60) {
                            return '';
                        }
                        return @json(__('candidate.timer_detail_seconds')).replace(':seconds', String(s));
                    },
                    get urgent() {
                        return this.left <= 120;
                    },
                }"
                x-bind:class="urgent ? '!bg-red-950 !border-red-500/40' : ''"
            >
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-100/90">
                    {{ __('candidate.whole_test_timer_banner_label') }}
                </p>
                <p class="font-mono text-2xl font-bold text-white tabular-nums" x-text="hourMin"></p>
                <p class="text-sm text-amber-100/95" x-show="subline.length" x-text="subline"></p>
            </div>
        @endif

        <div @class(['candidate-application-space tt-test-wrap', 'pt-28' => $this->wholeTestExpiresAtUnix, 'pt-0' => ! $this->wholeTestExpiresAtUnix])>
            <div class="tt-hero">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="tt-hero__title">
                            🎯 {{ __('candidate.take_test_level_header', ['level' => $this->currentLevel]) }}
                        </h2>
                        <p class="tt-hero__desc">{{ __('candidate.take_test_instructions') }}</p>
                    </div>
                    <div class="tt-hero__progress">
                        <div class="tt-hero__count">{{ $this->answeredCount }}/{{ $this->totalQuestions }}</div>
                        <div class="tt-hero__count-label">{{ __('candidate.take_test_answered_label') }}</div>
                    </div>
                </div>
                @if ($this->totalQuestions > 0)
                    <div class="tt-hero__bar">
                        <div
                            class="tt-hero__bar-fill"
                            style="width: {{ round(($this->answeredCount / $this->totalQuestions) * 100) }}%;"
                        ></div>
                    </div>
                @endif
            </div>

            @foreach ($this->getQuestions() as $question)
                @php($mcqOptions = $this->mcqOptionsForQuestion($question))
                <div wire:key="take-test-question-{{ $question->id }}" class="tt-question">
                    <p class="tt-question__text">
                        {{ $loop->iteration }}.
                        @if ($locale === 'ar' && $question->question_ar)
                            {{ $question->question_ar }}
                        @elseif ($locale === 'en' && $question->question_en)
                            {{ $question->question_en }}
                        @else
                            {{ $question->question_fr }}
                        @endif
                        @if ($question->max_note > 0)
                            <span class="tt-question__points">({{ $question->max_note }} pts)</span>
                        @endif
                    </p>

                    @if ($question->component === 'radio' && count($mcqOptions) > 0)
                        <div class="tt-options">
                            @foreach ($mcqOptions as $option)
                                <label wire:key="take-test-q{{ $question->id }}-r{{ $loop->index }}" class="tt-option">
                                    <input
                                        type="radio"
                                        wire:model.live="answers.{{ $question->id }}"
                                        value="{{ $option }}"
                                        @disabled($this->alreadySubmitted)
                                    />
                                    <span class="tt-option__label">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    @elseif ($question->component === 'checkbox' && count($mcqOptions) > 0)
                        <div class="tt-options">
                            <p class="tt-options__hint">{{ __('candidate.qcm_select_all_that_apply') }}</p>
                            @foreach ($mcqOptions as $option)
                                <label wire:key="take-test-q{{ $question->id }}-c{{ $loop->index }}" class="tt-option">
                                    <input
                                        type="checkbox"
                                        wire:model.live="answers.{{ $question->id }}"
                                        value="{{ $option }}"
                                        @disabled($this->alreadySubmitted)
                                    />
                                    <span class="tt-option__label">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    @elseif ($question->component === 'text')
                        <textarea
                            wire:model.live.debounce.500ms="answers.{{ $question->id }}"
                            rows="3"
                            placeholder="{{ __('candidate.your_answer_placeholder') }}"
                            @disabled($this->alreadySubmitted)
                            class="tt-field"
                        ></textarea>
                    @elseif ($question->component === 'date')
                        <input
                            type="date"
                            wire:model.live="answers.{{ $question->id }}"
                            @disabled($this->alreadySubmitted)
                            class="tt-field"
                        />
                    @elseif ($question->component === 'photo')
                        <input
                            type="file"
                            wire:model="answers.{{ $question->id }}"
                            accept="image/*"
                            @disabled($this->alreadySubmitted)
                            class="tt-field"
                        />
                    @elseif ($question->component === 'list')
                        <select
                            wire:model.live="answers.{{ $question->id }}"
                            @disabled($this->alreadySubmitted)
                            class="tt-field"
                        >
                            <option value="">-- {{ __('candidate.choose_option') }} --</option>
                            @foreach ($mcqOptions as $option)
                                <option value="{{ $option }}">
                                    {{ $option }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>
            @endforeach

            @if (! $this->alreadySubmitted)
                <div class="tt-actions">
                    <x-filament::button type="button" wire:click="saveAnswers" wire:loading.attr="disabled" color="gray" outlined>
                        <span wire:loading.remove wire:target="saveAnswers">💾 {{ __('Save') }}</span>
                        <span wire:loading wire:target="saveAnswers">…</span>
                    </x-filament::button>
                    <x-filament::button
                        type="button"
                        wire:click="submitLevel"
                        wire:loading.attr="disabled"
                        color="primary"
                        wire:confirm="{{ __('candidate.submit_level_confirm', ['level' => $this->currentLevel]) }}"
                    >
                        <span wire:loading.remove wire:target="submitLevel">✅ {{ __('Submit Level') }} {{ $this->currentLevel }}</span>
                        <span wire:loading wire:target="submitLevel">…</span>
                    </x-filament::button>
                </div>
            @endif
        </div>
    @endif
</x-filament-panels::page>
