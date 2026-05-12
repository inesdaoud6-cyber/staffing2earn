<x-filament-panels::page>
    <div class="space-y-6">

        @if($existing && $existing->is_approved)
            <div class="p-4 rounded-xl flex items-center gap-3"
                style="background:#f0fdf4;border:1px solid #86efac;">
                <span style="font-size:1.5rem;">✅</span>
                <div>
                    <p class="font-bold" style="color:#065f46;">{{ __('temoignage.approved') }}</p>
                    <p class="text-sm" style="color:#047857;">{{ __('temoignage.approved_msg') }}</p>
                </div>
            </div>
        @elseif($existing)
            <div class="p-4 rounded-xl flex items-center gap-3"
                style="background:#fffbeb;border:1px solid #fcd34d;">
                <span style="font-size:1.5rem;">⏳</span>
                <div>
                    <p class="font-bold" style="color:#92400e;">{{ __('temoignage.pending_approval') }}</p>
                    <p class="text-sm" style="color:#78350f;">{{ __('temoignage.pending_msg') }}</p>
                </div>
            </div>
        @endif

        <div class="p-6 rounded-xl" style="background:white;border:1px solid #ede9fe;box-shadow:0 2px 12px rgba(26,26,140,0.06);">
            <h2 class="font-bold text-lg mb-1" style="color:#1a1a8c;">
                💬 {{ __('temoignage.share_title') }}
            </h2>
            <p class="text-sm mb-4" style="color:#6b7280;">{{ __('temoignage.share_desc') }}</p>

            <form wire:submit="submit">
                {{ $this->form }}
                <div class="mt-4">
                    <x-filament::button type="submit" color="primary" size="lg">
                        {{ $existing ? __('temoignage.update') : __('temoignage.submit') }}
                    </x-filament::button>
                </div>
            </form>
        </div>

    </div>
</x-filament-panels::page>