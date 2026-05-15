<x-filament-panels::page class="popup-notifications-page">
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Persönliche Einstellungen</x-slot>
            <x-slot name="description">
                Diese Auswahl gilt nur für Ihren Account und ergänzt die Vorgaben Ihrer Benachrichtigungsgruppen.
            </x-slot>

            <div class="mt-2 grid gap-4 text-sm text-gray-600 sm:grid-cols-3 dark:text-gray-400">
                <div class="flex gap-3 rounded-xl bg-gray-50 p-3 outline outline-1 -outline-offset-1 outline-gray-200 dark:bg-white/5 dark:outline-white/10">
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                        <x-filament::icon icon="heroicon-o-bell" class="size-4" />
                    </span>
                    <span><strong class="text-gray-950 dark:text-white">Aktiv</strong> — Ereignis überhaupt empfangen</span>
                </div>
                <div class="flex gap-3 rounded-xl bg-gray-50 p-3 outline outline-1 -outline-offset-1 outline-gray-200 dark:bg-white/5 dark:outline-white/10">
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                        <x-filament::icon icon="heroicon-o-envelope" class="size-4" />
                    </span>
                    <span><strong class="text-gray-950 dark:text-white">E-Mail</strong> — Benachrichtigung per Mail</span>
                </div>
                <div class="flex gap-3 rounded-xl bg-gray-50 p-3 outline outline-1 -outline-offset-1 outline-gray-200 dark:bg-white/5 dark:outline-white/10">
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                        <x-filament::icon icon="heroicon-o-device-phone-mobile" class="size-4" />
                    </span>
                    <span><strong class="text-gray-950 dark:text-white">In-App</strong> — Hinweis im Tool</span>
                </div>
            </div>
        </x-filament::section>

        <div class="popup-notifications-toolbar">
            @include('filament.pages.partials.notification-category-pills', [
                'options' => $this->categoryOptions,
            ])

            <x-filament::button wire:click="savePreferences" icon="heroicon-o-check">
                Speichern
            </x-filament::button>
        </div>

        @if ($this->eventTypes->isEmpty())
            <x-filament::section>
                <div class="py-10 text-center">
                    <p class="text-sm font-medium text-gray-950 dark:text-white">Keine Ereignisse in dieser Kategorie</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Wählen Sie „Alle“ oder eine andere Kategorie.</p>
                </div>
            </x-filament::section>
        @else
            <div class="space-y-5">
                @foreach ($this->eventsGroupedByCategory as $category => $events)
                    <x-filament::section wire:key="pref-cat-{{ $category }}">
                        <x-slot name="heading">{{ $this->categoryLabel($category) }}</x-slot>
                        <x-slot name="description">
                            {{ $this->enabledCountForCategory($category) }} von {{ $events->count() }} aktiv
                        </x-slot>

                        <div class="mt-2 divide-y divide-gray-100 dark:divide-white/10">
                            @foreach ($events as $event)
                                @php
                                    $prefKey = (string) $event->id;
                                    $isEnabled = (bool) ($preferences[$prefKey]['enabled'] ?? false);
                                @endphp
                                <div
                                    wire:key="pref-{{ $event->id }}"
                                    @class([
                                        'popup-notifications-event-card',
                                        'my-3 first:mt-0' => true,
                                        'ring-1 ring-primary-200/80 dark:ring-primary-500/30' => $isEnabled,
                                    ])
                                >
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="min-w-0 flex-1">
                                            <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
                                                {{ $event->name }}
                                            </h3>
                                            @if (filled($event->description))
                                                <p class="mt-1 text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                                                    {{ $event->description }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="flex shrink-0 flex-wrap items-center gap-4 sm:gap-6">
                                            <div class="popup-notifications-channel">
                                                <span class="popup-notifications-channel-label">Aktiv</span>
                                                @include('filament.pages.partials.notification-toggle', [
                                                    'wireModel' => "preferences.{$prefKey}.enabled",
                                                    'label' => "{$event->name} aktivieren",
                                                ])
                                            </div>
                                            <div class="popup-notifications-channel">
                                                <span class="popup-notifications-channel-label">E-Mail</span>
                                                @include('filament.pages.partials.notification-toggle', [
                                                    'wireModel' => "preferences.{$prefKey}.email",
                                                    'label' => "{$event->name} per E-Mail",
                                                    'disabled' => ! $isEnabled,
                                                ])
                                            </div>
                                            <div class="popup-notifications-channel">
                                                <span class="popup-notifications-channel-label">In-App</span>
                                                @include('filament.pages.partials.notification-toggle', [
                                                    'wireModel' => "preferences.{$prefKey}.in_app",
                                                    'label' => "{$event->name} In-App",
                                                    'disabled' => ! $isEnabled,
                                                ])
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-filament::section>
                @endforeach
            </div>

            <div class="flex justify-end border-t border-gray-200 pt-4 dark:border-white/10">
                <x-filament::button wire:click="savePreferences" icon="heroicon-o-check" size="lg">
                    Einstellungen speichern
                </x-filament::button>
            </div>
        @endif
    </div>
</x-filament-panels::page>
