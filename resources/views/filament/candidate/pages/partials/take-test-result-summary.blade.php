@props([
    'testScore' => null,
    'eligibilityPassed' => null,
    'eligibilityThreshold' => null,
    'applicationScore' => null,
    'pendingManual' => false,
    'showAutoEligibility' => false,
])

@if ($testScore !== null)
    <p class="mb-2 text-2xl font-bold">
        {{ __('candidate.take_test_score', ['score' => number_format($testScore, 2)]) }}
    </p>
@endif

@if ($showAutoEligibility && $eligibilityPassed !== null)
    <p class="mb-3 text-lg font-semibold {{ $eligibilityPassed ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200' }}">
        {{ $eligibilityPassed
            ? __('candidate.take_test_eligibility_passed', ['threshold' => number_format((float) $eligibilityThreshold, 2)])
            : __('candidate.take_test_eligibility_failed_inline', ['threshold' => number_format((float) $eligibilityThreshold, 2)]) }}
    </p>
@endif

@if ($pendingManual)
    <p class="mt-3 text-sm opacity-90">{{ __('candidate.take_test_pending_manual_review') }}</p>
@endif

@if ($applicationScore !== null)
    <p class="mt-4 text-sm font-medium">
        {{ __('candidate.take_test_application_score', ['score' => number_format($applicationScore, 2)]) }}
    </p>
@endif
