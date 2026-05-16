@php
    use Illuminate\View\ComponentAttributeBag;
@endphp

<div class="s2e-groups-list-toolbar flex w-full flex-row flex-wrap items-center justify-between gap-4 gap-y-3">
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
                        'placeholder' => __('admin.search_groups_placeholder'),
                        'type' => 'search',
                        'wire:key' => 'groups-list-table-search-input',
                        'wire:model.live.debounce.300ms' => 'tableSearch',
                        'x-bind:id' => '$id(\'input\')',
                    ])
                "
            />
        </x-filament::input.wrapper>
    </div>

    <div class="flex shrink-0 items-center gap-x-2">
        <span class="whitespace-nowrap text-sm font-medium text-gray-950 dark:text-white">
            {{ __('admin.filter') }}
        </span>
        <div class="w-44 sm:w-52">
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
</div>
