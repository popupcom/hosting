@props([
    'wireModel' => null,
    'label' => '',
    'disabled' => false,
])

<label class="popup-notification-toggle" @if ($disabled) title="Zuerst aktivieren" @endif>
    <input
        type="checkbox"
        @if ($wireModel) wire:model.live="{{ $wireModel }}" @endif
        @disabled($disabled)
        aria-label="{{ $label }}"
    />
    <span aria-hidden="true"></span>
</label>
