@props([
    'options' => [],
])

<div class="popup-notifications-pills" role="tablist" aria-label="Kategorie filtern">
    <button
        type="button"
        wire:click="$set('categoryFilter', null)"
        @class([
            'popup-notifications-pill',
            'popup-notifications-pill--active' => blank($categoryFilter),
        ])
        role="tab"
        @if (blank($categoryFilter)) aria-selected="true" @endif
    >
        Alle
    </button>
    @foreach ($options as $value => $label)
        <button
            type="button"
            wire:click="$set('categoryFilter', @js($value))"
            @class([
                'popup-notifications-pill',
                'popup-notifications-pill--active' => $categoryFilter === $value,
            ])
            role="tab"
            @if ($categoryFilter === $value) aria-selected="true" @endif
        >
            {{ $label }}
        </button>
    @endforeach
</div>
