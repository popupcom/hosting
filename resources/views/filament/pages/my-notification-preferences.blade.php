<x-filament-panels::page>
    <div class="space-y-4">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Kategorie</label>
                <select wire:model.live="categoryFilter" class="mt-1 block rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800">
                    <option value="">Alle</option>
                    @foreach($this->categoryOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <x-filament::button wire:click="savePreferences">Speichern</x-filament::button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-3 py-2 text-left">Ereignis</th>
                        <th class="px-2 py-2 text-center">Aktiv</th>
                        <th class="px-2 py-2 text-center">E-Mail</th>
                        <th class="px-2 py-2 text-center">In-App</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($this->eventTypes as $event)
                        <tr wire:key="pref-{{ $event->id }}">
                            <td class="px-3 py-2">
                                <div class="font-medium">{{ $event->name }}</div>
                                <div class="text-xs text-gray-500">{{ $event->description }}</div>
                            </td>
                            <td class="px-2 py-2 text-center">
                                <input type="checkbox" wire:model="preferences.{{ $event->id }}.enabled" />
                            </td>
                            <td class="px-2 py-2 text-center">
                                <input type="checkbox" wire:model="preferences.{{ $event->id }}.email" />
                            </td>
                            <td class="px-2 py-2 text-center">
                                <input type="checkbox" wire:model="preferences.{{ $event->id }}.in_app" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
