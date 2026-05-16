@php
    $applyAction = $this->getOfferApplyAction($offreId);
    $applyLabel = $this->offerApplyButtonLabel($applyAction);
    $wireTarget = $offreId === null ? 'startApplyFree' : 'startApplyOffre('.$offreId.')';
    $wireClick = $offreId === null ? 'startApplyFree' : 'startApplyOffre('.$offreId.')';
    $btnClass = $this->offerApplyButtonClass($applyAction);
    $isDisabled = $this->offerApplyButtonIsDisabled($applyAction);
    $showArrow = in_array($applyAction, ['apply', 'reapply'], true);
@endphp

<button type="button"
    @class([$btnClass])
    @disabled($isDisabled)
    @if (! $isDisabled)
        wire:click="{{ $wireClick }}"
        wire:loading.attr="disabled"
        wire:target="{{ $wireTarget }}"
    @endif>
    @if ($isDisabled)
        {{ $applyLabel }}
    @else
        <span wire:loading.remove wire:target="{{ $wireTarget }}">{{ $applyLabel }}@if ($showArrow) →@endif</span>
        <span wire:loading wire:target="{{ $wireTarget }}">⏳</span>
    @endif
</button>
