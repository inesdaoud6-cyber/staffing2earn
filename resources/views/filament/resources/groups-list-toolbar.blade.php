@php
    use Illuminate\View\ComponentAttributeBag;
@endphp

<div class="s2e-groups-list-toolbar flex w-full flex-row flex-wrap items-center gap-x-6 gap-y-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 dark:border-white/10 dark:bg-white/5">
    <div class="flex min-w-0 flex-1 flex-wrap items-center gap-x-3 gap-y-2 sm:flex-none sm:basis-auto">
        <span class="shrink-0 text-sm font-semibold text-gray-950 dark:text-white">
            {{ __('admin.filter') }}
        </span>
        <div class="min-w-[12rem] flex-1 sm:w-52 sm:flex-none">
            <x-filament::input.wrapper>
                <x-filament::input.select
                    :attributes="
                        (new ComponentAttributeBag)->merge([
                            'wire:model.live' => 'blockFilter',
                            'wire:key' => 'groups-list-block-filter',
                        ])
                    "
                >
                    <option value="">{{ __('admin.filter_all_blocks') }}</option>
                    @foreach ($this->getBlockFilterOptions() as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>
    </div>

    <div class="flex min-w-0 flex-1 flex-wrap items-center gap-x-3 gap-y-2 sm:flex-none sm:basis-auto">
        <span class="shrink-0 text-sm font-semibold text-gray-950 dark:text-white">
            {{ __('admin.filter') }}
        </span>
        <div class="min-w-[12rem] flex-1 sm:w-52 sm:flex-none">
            <x-filament::input.wrapper>
                <x-filament::input.select
                    :attributes="
                        (new ComponentAttributeBag)->merge([
                            'wire:model.live' => 'questionsCountFilter',
                            'wire:key' => 'groups-list-questions-filter',
                        ])
                    "
                >
                    <option value="">{{ __('admin.questions_count_all') }}</option>
                    <option value="none">{{ __('admin.questions_count_none') }}</option>
                    <option value="1_5">{{ __('admin.questions_count_1_5') }}</option>
                    <option value="6_plus">{{ __('admin.questions_count_6_plus') }}</option>
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>
    </div>
</div>
