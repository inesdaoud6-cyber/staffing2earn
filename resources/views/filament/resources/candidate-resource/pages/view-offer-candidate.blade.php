<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">{{ __('admin.candidate_profile_heading') }}</x-slot>

            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.full_name') }}</dt>
                    <dd class="mt-1 text-base font-semibold text-gray-950 dark:text-white">{{ $this->candidate->full_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Status') }}</dt>
                    <dd class="mt-1 text-base font-semibold text-gray-950 dark:text-white">{{ $this->getStatusLabel() }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Email') }}</dt>
                    <dd class="mt-1 text-base text-gray-950 dark:text-white">{{ $this->candidate->user?->email ?? $this->candidate->email ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.phone') }}</dt>
                    <dd class="mt-1 text-base text-gray-950 dark:text-white">{{ $this->candidate->phone ?: '—' }}</dd>
                </div>
                @if ($rank = $this->getRank())
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.candidate_rank') }}</dt>
                        <dd class="mt-1 text-base font-semibold text-gray-950 dark:text-white">#{{ $rank }}</dd>
                    </div>
                @endif
            </dl>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">{{ __('admin.candidate_scores_heading') }}</x-slot>

            @if (count($rows = $this->getTestScoreRows()) > 0)
                <div class="mb-6 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-start font-semibold text-gray-950 dark:text-white">{{ __('admin.candidate_test_column') }}</th>
                                <th class="px-4 py-3 text-end font-semibold text-gray-950 dark:text-white">{{ __('admin.candidate_score') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                            @foreach ($rows as $row)
                                <tr>
                                    <td class="px-4 py-3 text-gray-950 dark:text-white">{{ $row['label'] }}</td>
                                    <td class="px-4 py-3 text-end font-medium text-gray-950 dark:text-white">{{ $row['score'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-violet-50 dark:bg-violet-950/40">
                            <tr>
                                <td class="px-4 py-3 font-semibold text-gray-950 dark:text-white">{{ __('admin.candidate_final_score') }}</td>
                                <td class="px-4 py-3 text-end text-lg font-bold text-violet-700 dark:text-violet-300">{{ $this->getFinalScoreLabel() }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">{{ __('admin.candidate_no_test_scores_yet') }}</p>
                <p class="text-base font-semibold text-gray-950 dark:text-white">
                    {{ __('admin.candidate_final_score') }}:
                    <span class="text-violet-700 dark:text-violet-300">{{ $this->getFinalScoreLabel() }}</span>
                </p>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">{{ __('admin.application_cv') }}</x-slot>

            <div class="flex flex-wrap items-center gap-3">
                <x-filament::button
                    wire:click="toggleCv"
                    :color="$showCv ? 'gray' : 'primary'"
                    :icon="$showCv ? 'heroicon-o-eye-slash' : 'heroicon-o-eye'"
                >
                    {{ $showCv ? __('admin.candidate_hide_cv') : __('admin.candidate_show_cv') }}
                </x-filament::button>

                @if ($cvUrl = $this->getCvUrl())
                    <x-filament::button
                        tag="a"
                        :href="$cvUrl"
                        target="_blank"
                        color="gray"
                        icon="heroicon-o-arrow-top-right-on-square"
                        outlined
                    >
                        {{ __('admin.application_view_cv_open') }}
                    </x-filament::button>
                @endif
            </div>

            @if ($showCv)
                @if ($cvUrl = $this->getCvUrl())
                    <div class="candidate-cv-preview">
                        <iframe
                            src="{{ $cvUrl }}"
                            class="candidate-cv-preview__frame"
                            title="{{ __('admin.application_cv') }}"
                        ></iframe>
                    </div>
                @else
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">{{ __('admin.application_no_cv') }}</p>
                @endif
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>

