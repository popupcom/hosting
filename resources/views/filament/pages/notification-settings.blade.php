<x-filament-panels::page class="popup-notifications-page">
    <div class="space-y-6">
        <div class="grid gap-6 lg:grid-cols-5">
            <x-filament::section class="lg:col-span-2">
                <x-slot name="heading">Überblick</x-slot>
                <x-slot name="description">
                    Gruppen bündeln Personen. Pro Zelle legen Sie fest, ob ein Ereignis für die Gruppe aktiv ist und über welchen Kanal es zugestellt wird.
                </x-slot>

                <div class="mt-2 space-y-3 text-sm text-gray-600 dark:text-gray-400">
                    <p>
                        <strong class="font-medium text-gray-950 dark:text-white">An</strong> schaltet das Ereignis für die Gruppe frei.
                        <strong class="font-medium text-gray-950 dark:text-white">Mail</strong> und
                        <strong class="font-medium text-gray-950 dark:text-white">App</strong> steuern die Kanäle.
                    </p>
                    <p>
                        Persönliche Overrides legen Benutzer:innen unter
                        <a
                            href="{{ \App\Filament\Pages\MyNotificationPreferencesPage::getUrl() }}"
                            class="font-medium text-primary-600 underline decoration-primary-600/30 underline-offset-2 hover:decoration-primary-600 dark:text-primary-400"
                        >
                            Meine Benachrichtigungen
                        </a>
                        fest.
                    </p>
                </div>

                @if ($this->groups->isNotEmpty())
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($this->groups as $group)
                            <span class="inline-flex items-center rounded-lg bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-700 outline outline-1 -outline-offset-1 outline-gray-200 dark:bg-white/5 dark:text-gray-200 dark:outline-white/10">
                                {{ $group->name }}
                                @if (filled($group->description))
                                    <span class="ms-1 font-normal text-gray-500 dark:text-gray-400">· {{ \Illuminate\Support\Str::limit($group->description, 40) }}</span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                @endif
            </x-filament::section>

            <x-filament::section class="lg:col-span-3">
                <x-slot name="heading">Neue Gruppe</x-slot>
                <x-slot name="description">Legen Sie eine Benachrichtigungsgruppe für Team oder Rolle an.</x-slot>

                <div class="mt-1 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="new-group-name" class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">
                            Gruppenname
                        </label>
                        <input
                            id="new-group-name"
                            type="text"
                            wire:model="newGroup.name"
                            placeholder="z. B. Technik"
                            class="fi-input mt-1 block w-full rounded-lg border-none bg-white py-2 text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 placeholder:text-gray-400 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:placeholder:text-gray-500"
                        />
                    </div>
                    <div class="sm:col-span-2">
                        <label for="new-group-description" class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">
                            Beschreibung <span class="font-normal text-gray-500">(optional)</span>
                        </label>
                        <textarea
                            id="new-group-description"
                            wire:model="newGroup.description"
                            rows="2"
                            placeholder="Kurz beschreiben, wofür die Gruppe gedacht ist …"
                            class="fi-input mt-1 block w-full rounded-lg border-none bg-white py-2 text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 placeholder:text-gray-400 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:placeholder:text-gray-500"
                        ></textarea>
                    </div>
                </div>

                <div class="mt-4">
                    <x-filament::button wire:click="createGroup" icon="heroicon-o-plus">
                        Gruppe anlegen
                    </x-filament::button>
                </div>
            </x-filament::section>
        </div>

        <x-filament::section>
            <x-slot name="heading">Gruppen × Ereignisse</x-slot>
            <x-slot name="description">
                {{ $this->eventTypes->count() }} Ereignisse
                @if ($this->groups->isNotEmpty())
                    · {{ $this->groups->count() }} Gruppen
                @endif
            </x-slot>

            <div class="mt-4 space-y-4">
                <div class="popup-notifications-toolbar">
                    @include('filament.pages.partials.notification-category-pills', [
                        'options' => $this->categoryOptions,
                    ])

                    <div class="flex flex-wrap items-center gap-3">
                        <div class="popup-notifications-legend hidden sm:flex">
                            <span><kbd>An</kbd> aktiv</span>
                            <span><kbd>Mail</kbd> E-Mail</span>
                            <span><kbd>App</kbd> In-App</span>
                        </div>
                        <x-filament::button wire:click="saveMatrix" icon="heroicon-o-check">
                            Speichern
                        </x-filament::button>
                    </div>
                </div>

                @if ($this->groups->isEmpty())
                    <div class="rounded-xl border border-dashed border-gray-300 px-6 py-12 text-center dark:border-gray-600">
                        <p class="text-sm font-medium text-gray-950 dark:text-white">Noch keine Gruppen vorhanden</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Legen Sie oben eine Gruppe an, um die Matrix zu befüllen.</p>
                    </div>
                @elseif ($this->eventTypes->isEmpty())
                    <div class="rounded-xl border border-dashed border-gray-300 px-6 py-12 text-center dark:border-gray-600">
                        <p class="text-sm font-medium text-gray-950 dark:text-white">Keine Ereignisse in dieser Kategorie</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Wählen Sie eine andere Kategorie oder „Alle“.</p>
                    </div>
                @else
                    <div class="popup-notifications-matrix-wrap">
                        <table class="popup-notifications-matrix">
                            <thead>
                                <tr>
                                    <th class="sticky-col px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        Ereignis
                                    </th>
                                    @foreach ($this->groups as $group)
                                        <th class="group-block px-2 py-3 text-center" colspan="3">
                                            <div class="text-sm font-semibold text-gray-950 dark:text-white">{{ $group->name }}</div>
                                            @if (filled($group->description))
                                                <div class="mt-0.5 text-xs font-normal text-gray-500 dark:text-gray-400">{{ $group->description }}</div>
                                            @endif
                                        </th>
                                    @endforeach
                                </tr>
                                <tr class="text-xs text-gray-500 dark:text-gray-400">
                                    <th class="sticky-col"></th>
                                    @foreach ($this->groups as $group)
                                        <th class="group-block px-1 py-2 font-medium">An</th>
                                        <th class="px-1 py-2 font-medium">Mail</th>
                                        <th class="px-1 py-2 font-medium">App</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->eventsGroupedByCategory as $category => $events)
                                    <tr class="category-row" wire:key="cat-{{ $category }}">
                                        <td class="sticky-col" colspan="{{ 1 + ($this->groups->count() * 3) }}">
                                            {{ $this->categoryLabel($category) }}
                                        </td>
                                    </tr>
                                    @foreach ($events as $event)
                                        <tr wire:key="event-{{ $event->id }}" class="bg-white dark:bg-transparent">
                                            <td class="sticky-col px-4 py-3 align-top">
                                                <div class="font-medium text-gray-950 dark:text-white">{{ $event->name }}</div>
                                                @if (filled($event->description))
                                                    <div class="mt-0.5 text-xs leading-relaxed text-gray-500 dark:text-gray-400">{{ $event->description }}</div>
                                                @endif
                                            </td>
                                            @foreach ($this->groups as $group)
                                                @php $key = $group->id.'_'.$event->id; @endphp
                                                <td class="group-block px-2 py-3 text-center align-middle">
                                                    @include('filament.pages.partials.notification-toggle', [
                                                        'wireModel' => "matrix.{$key}.enabled",
                                                        'label' => "{$event->name} für {$group->name} aktivieren",
                                                    ])
                                                </td>
                                                <td class="px-2 py-3 text-center align-middle">
                                                    @include('filament.pages.partials.notification-toggle', [
                                                        'wireModel' => "matrix.{$key}.email",
                                                        'label' => "{$event->name} per E-Mail für {$group->name}",
                                                        'disabled' => ! ($matrix[$key]['enabled'] ?? false),
                                                    ])
                                                </td>
                                                <td class="px-2 py-3 text-center align-middle">
                                                    @include('filament.pages.partials.notification-toggle', [
                                                        'wireModel' => "matrix.{$key}.in_app",
                                                        'label' => "{$event->name} In-App für {$group->name}",
                                                        'disabled' => ! ($matrix[$key]['enabled'] ?? false),
                                                    ])
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
