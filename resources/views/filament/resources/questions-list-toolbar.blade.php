@php
    use Illuminate\View\ComponentAttributeBag;
@endphp

<div class="s2e-questions-list-toolbar flex w-full flex-row flex-wrap items-center justify-between gap-4 gap-y-3">
    <div class="fi-ta-search-field min-w-0 flex-1 basis-0 sm:max-w-md" x-id="['input']">
        <label x-bind:for="$id('input')" class="sr-only">
            {{ __('filament-tables::table.fields.search.label') }}
        </label>

        <x-filament::input.wrapper
            inline-prefix
            prefix-icon="heroicon-m-magnifying-glass"
            prefix-icon-alias="tables::search-field"
            wire:target="tableSearch"
        >
            <x-filament::input
                :attributes="
                    (new ComponentAttributeBag)->merge([
                        'autocomplete' => 'off',
                        'inlinePrefix' => true,
                        'maxlength' => 1000,
                        'placeholder' => __('admin.search_questions_placeholder'),
                        'type' => 'search',
                        'wire:key' => 'questions-list-table-search-input',
                        'wire:model.live.debounce.300ms' => 'tableSearch',
                        'x-bind:id' => '$id(\'input\')',
                    ])
                "
            />
        </x-filament::input.wrapper>
    </div>

    <div class="flex shrink-0 flex-wrap items-center gap-x-4 gap-y-2">
        <div class="flex items-center gap-x-2">
            <span class="whitespace-nowrap text-sm font-medium text-gray-950 dark:text-white">
                {{ __('admin.filter_level') }}
            </span>
            <div class="w-28 sm:w-32">
                <x-filament::input.wrapper>
                    <x-filament::input.select
                        :attributes="
                            (new ComponentAttributeBag)->merge([
                                'wire:model.live' => 'levelFilter',
                                'wire:key' => 'questions-list-level-filter',
                            ])
                        "
                    >
                        <option value="">{{ __('admin.filter_all_levels') }}</option>
                        @foreach ($this->getLevelFilterOptions() as $level => $label)
                            <option value="{{ $level }}">{{ __('admin.level-prefix') }} {{ $level }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
        </div>

        <div class="flex items-center gap-x-2">
            <span class="whitespace-nowrap text-sm font-medium text-gray-950 dark:text-white">
                {{ __('admin.filter_type') }}
            </span>
            <div class="w-40 sm:w-48">
                <x-filament::input.wrapper>
                    <x-filament::input.select
                        :attributes="
                            (new ComponentAttributeBag)->merge([
                                'wire:model.live' => 'componentFilter',
                                'wire:key' => 'questions-list-type-filter',
                            ])
                        "
                    >
                        <option value="">{{ __('admin.filter_all_types') }}</option>
                        @foreach ($this->getComponentFilterOptions() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
        </div>
    </div>
</div>
