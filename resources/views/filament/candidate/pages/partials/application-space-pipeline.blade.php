<section class="as-pipeline" aria-label="{{ __('candidate.applications.pipeline_heading') }}">
    <p class="as-pipeline__hint">{{ __('candidate.applications.pipeline_subtitle') }}</p>
    <div
        class="as-pipeline__track-wrap"
        wire:key="pipeline-step-{{ $selectedOfferStep }}"
        x-data
        x-init="$nextTick(() => document.getElementById('as-step-{{ (int) $selectedOfferStep }}')?.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' }))"
    >
        @php
            $pipelineStepCount = count($steps);
        @endphp
        <div @class(['as-stepper', 'as-stepper--scroll' => $pipelineStepCount > 5])" role="list">
            <div class="as-stepper-node as-stepper-node--terminal" aria-hidden="true">
                <span class="as-step-caption as-step-caption--terminal">{{ __('candidate.applications.pipeline_start') }}</span>
                <span class="as-step-marker as-step-marker--dot" aria-hidden="true"></span>
            </div>

            @foreach ($steps as $step)
                @php
                    $isSelected = (int) $selectedOfferStep === (int) $step['offer_step'];
                    $stepState = $step['state'] ?? 'future';
                    if ($step['kind'] === 'cv') {
                        $stepLabel = __('candidate.applications.caption_cv');
                    } elseif ($step['kind'] === 'decision') {
                        $stepLabel = __('candidate.applications.caption_decision');
                    } else {
                        $stepLabel = __('candidate.applications.caption_test', [
                            'n' => (int) ($step['test_number'] ?? $step['offer_step'] - 1),
                        ]);
                    }
                @endphp
                <div class="as-stepper-node" role="listitem">
                    <button
                        type="button"
                        id="as-step-{{ (int) $step['offer_step'] }}"
                        wire:click="setSelectedOfferStep({{ (int) $step['offer_step'] }})"
                        @class([
                            'as-step-btn',
                            'as-step-btn--' . $stepState,
                            'as-step-btn--selected' => $isSelected,
                            'as-step-btn--focus' => in_array($stepState, ['current', 'waiting'], true),
                        ])
                        aria-current="{{ $isSelected ? 'step' : 'false' }}">
                        <span class="as-step-caption">{{ $stepLabel }}</span>
                        <span class="as-step-marker as-step-marker--circle" aria-hidden="true">
                            @if ($stepState === 'completed')
                                <svg class="as-step-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            @elseif ($stepState === 'waiting')
                                <svg class="as-step-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            @elseif ($stepState === 'rejected')
                                <svg class="as-step-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            @endif
                        </span>
                    </button>
                </div>
            @endforeach

            <div class="as-stepper-node as-stepper-node--terminal" aria-hidden="true">
                <span class="as-step-caption as-step-caption--terminal">{{ __('candidate.applications.pipeline_end') }}</span>
                <span class="as-step-marker as-step-marker--dot" aria-hidden="true"></span>
            </div>
        </div>
    </div>
</section>
