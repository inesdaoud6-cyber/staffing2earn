<x-filament-panels::page>
    <x-filament::section
        :heading="__('admin.application_section_job_offers')"
        :description="__('admin.application_section_job_offers_desc')"
    >
        {{ $this->table }}
    </x-filament::section>
</x-filament-panels::page>
