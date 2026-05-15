@php
    /** @var array<string, string> $tokens */
    $tokens = $tokens ?? [];
@endphp
<div
    class="popup-notifications-page"
    style="
        --popup-notifications-toolbar-bg: {{ $tokens['toolbar_bg'] ?? '#f9fafb' }};
        --popup-notifications-pill-text: {{ $tokens['pill_text'] ?? '#4b5563' }};
        --popup-notifications-pill-bg: {{ $tokens['pill_bg'] ?? '#ffffff' }};
        --popup-notifications-pill-border: {{ $tokens['pill_border'] ?? '#e5e7eb' }};
        --popup-notifications-pill-active-text: {{ $tokens['pill_active_text'] ?? '#b91c1c' }};
        --popup-notifications-pill-active-bg: {{ $tokens['pill_active_bg'] ?? '#fef2f2' }};
        --popup-notifications-pill-active-border: {{ $tokens['pill_active_border'] ?? '#fecaca' }};
        --popup-notifications-toggle-off: {{ $tokens['toggle_off'] ?? '#e5e7eb' }};
        --popup-notifications-toggle-on: {{ $tokens['toggle_on'] ?? '#dc2626' }};
        --popup-notifications-category-text: {{ $tokens['category_text'] ?? '#b91c1c' }};
        --popup-notifications-category-bg: {{ $tokens['category_bg'] ?? 'rgba(254, 242, 242, 0.65)' }};
        --popup-notifications-event-card-bg: {{ $tokens['event_card_bg'] ?? '#ffffff' }};
        --popup-notifications-event-card-active-outline: {{ $tokens['event_card_active_outline'] ?? '#fecaca' }};
        --popup-notifications-channel-label: {{ $tokens['channel_label'] ?? '#6b7280' }};
        --popup-section-ring: rgba(9, 9, 11, 0.05);
        --popup-section-radius: 1rem;
    "
>
    <div class="space-y-4">
        <div class="popup-notifications-toolbar">
            <div class="popup-notifications-pills">
                <span class="popup-notifications-pill">Alle</span>
                <span class="popup-notifications-pill popup-notifications-pill--active">Abrechnung</span>
                <span class="popup-notifications-pill">ToDos</span>
            </div>
            <div class="popup-notifications-legend">
                <span><kbd>An</kbd> aktiv</span>
                <span><kbd>Mail</kbd> E-Mail</span>
            </div>
        </div>

        <div
            style="border-radius: var(--popup-section-radius); outline: 1px solid var(--popup-section-ring); overflow: hidden;"
        >
            <div
                class="category-row"
                style="padding: 0.5rem 0.75rem; font-size: 0.6875rem; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; color: var(--popup-notifications-category-text); background: var(--popup-notifications-category-bg);"
            >
                Beispiel-Kategorie
            </div>
            <div
                class="popup-notifications-event-card"
                style="margin: 0.75rem; outline-color: var(--popup-notifications-event-card-active-outline);"
            >
                <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
                    <div>
                        <div style="font-size: 0.875rem; font-weight: 600; color: #18181b;">Neues ToDo erhalten</div>
                        <p style="margin: 0.25rem 0 0; font-size: 0.8125rem; color: #6b7280;">Kurzbeschreibung des Ereignistyps</p>
                    </div>
                    <div style="display: flex; gap: 1rem;">
                        <div class="popup-notifications-channel">
                            <span class="popup-notifications-channel-label">Aktiv</span>
                            <label class="popup-notification-toggle">
                                <input type="checkbox" checked disabled />
                                <span></span>
                            </label>
                        </div>
                        <div class="popup-notifications-channel">
                            <span class="popup-notifications-channel-label">E-Mail</span>
                            <label class="popup-notification-toggle">
                                <input type="checkbox" checked disabled />
                                <span></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
