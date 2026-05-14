@php
    use Illuminate\View\ComponentAttributeBag;
@endphp

<div
    class="fi-user-list-toolbar flex w-full flex-row flex-wrap items-center gap-x-4 gap-y-3"
>
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
                        'placeholder' => __('admin.search_users_placeholder'),
                        'type' => 'search',
                        'wire:key' => 'users-list-table-search-input',
                        'wire:model.live.debounce.150ms' => 'tableSearch',
                        'x-bind:id' => '$id(\'input\')',
                        'x-on:keyup' => 'if ($event.key === \'Enter\') { $wire.$refresh() }',
                    ])
                "
            />
        </x-filament::input.wrapper>
    </div>

    <div class="fi-ta-role-filter flex shrink-0 items-center gap-x-2">
        <span class="fi-fo-field-wrp-label whitespace-nowrap text-sm font-medium leading-6 text-gray-950 dark:text-white">
            {{ __('admin.role_filter_label') }}
        </span>

        <div class="w-44 sm:w-48">
            <x-filament::input.wrapper>
                <x-filament::input.select
                    :attributes="
                        (new ComponentAttributeBag)->merge([
                            'wire:model.live' => 'roleFilter',
                            'wire:key' => 'user-table-role-filter',
                        ])
                    "
                >
                    <option value="">{{ __('admin.all_roles') }}</option>
                    <option value="admin">{{ __('nav.role_admin') }}</option>
                    <option value="candidate">{{ __('nav.role_candidate') }}</option>
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>
    </div>
</div>
