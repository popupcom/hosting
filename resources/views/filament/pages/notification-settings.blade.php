<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Neue Gruppe</x-slot>
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <input
                        type="text"
                        wire:model="newGroup.name"
                        placeholder="Gruppenname"
                        class="fi-input block w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800"
                    />
                </div>
                <div class="md:col-span-2">
                    <input
                        type="text"
                        wire:model="newGroup.description"
                        placeholder="Beschreibung (optional)"
                        class="fi-input block w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800"
                    />
                </div>
            </div>
            <div class="mt-3">
                <x-filament::button wire:click="createGroup">Gruppe anlegen</x-filament::button>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Gruppen × Ereignisse</x-slot>
            <div class="mb-4 flex flex-wrap items-end gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Kategorie</label>
                    <select wire:model.live="categoryFilter" class="mt-1 block rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800">
                        <option value="">Alle</option>
                        @foreach($this->categoryOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <x-filament::button wire:click="saveMatrix">Speichern</x-filament::button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold">Ereignis</th>
                            @foreach($this->groups as $group)
                                <th class="px-2 py-2 text-center font-semibold" colspan="3">{{ $group->name }}</th>
                            @endforeach
                        </tr>
                        <tr class="text-xs text-gray-500">
                            <th></th>
                            @foreach($this->groups as $group)
                                <th class="px-1 py-1">An</th>
                                <th class="px-1 py-1">Mail</th>
                                <th class="px-1 py-1">App</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($this->eventTypes as $event)
                            <tr wire:key="event-{{ $event->id }}">
                                <td class="px-3 py-2">
                                    <div class="font-medium">{{ $event->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $event->key }}</div>
                                </td>
                                @foreach($this->groups as $group)
                                    @php $key = $group->id.'_'.$event->id; @endphp
                                    <td class="px-2 py-2 text-center">
                                        <input type="checkbox" wire:model="matrix.{{ $key }}.enabled" />
                                    </td>
                                    <td class="px-2 py-2 text-center">
                                        <input type="checkbox" wire:model="matrix.{{ $key }}.email" />
                                    </td>
                                    <td class="px-2 py-2 text-center">
                                        <input type="checkbox" wire:model="matrix.{{ $key }}.in_app" />
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
