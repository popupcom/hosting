@props(['summary' => []])
<dl class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
    <div><dt class="text-gray-500 dark:text-gray-400">Neue Kund:innen</dt><dd class="font-medium">{{ $summary['clientsCreated'] ?? 0 }}</dd></div>
    <div><dt class="text-gray-500 dark:text-gray-400">Aktualisierte Kund:innen</dt><dd class="font-medium">{{ $summary['clientsUpdated'] ?? 0 }}</dd></div>
    <div><dt class="text-gray-500 dark:text-gray-400">Neue Projekte</dt><dd class="font-medium">{{ $summary['projectsCreated'] ?? 0 }}</dd></div>
    <div><dt class="text-gray-500 dark:text-gray-400">Aktualisierte Projekte</dt><dd class="font-medium">{{ $summary['projectsUpdated'] ?? 0 }}</dd></div>
    <div><dt class="text-gray-500 dark:text-gray-400">Neue Projekt-Leistungen</dt><dd class="font-medium">{{ $summary['projectServicesCreated'] ?? 0 }}</dd></div>
    <div><dt class="text-gray-500 dark:text-gray-400">Aktualisierte Projekt-Leistungen</dt><dd class="font-medium">{{ $summary['projectServicesUpdated'] ?? 0 }}</dd></div>
    <div><dt class="text-gray-500 dark:text-gray-400">Neue Wartungseinträge</dt><dd class="font-medium">{{ $summary['maintenanceHistoriesCreated'] ?? 0 }}</dd></div>
    <div><dt class="text-gray-500 dark:text-gray-400">Aktualisierte Wartungseinträge</dt><dd class="font-medium">{{ $summary['maintenanceHistoriesUpdated'] ?? 0 }}</dd></div>
    <div><dt class="text-gray-500 dark:text-gray-400">Neue Katalog-Leistungen</dt><dd class="font-medium">{{ $summary['catalogItemsCreated'] ?? 0 }}</dd></div>
    <div><dt class="text-gray-500 dark:text-gray-400">Fehler (Zeilen)</dt><dd class="font-medium">{{ $summary['errorCount'] ?? 0 }}</dd></div>
</dl>
@if (! empty($summary['errors']))
    <div class="mt-4">
        <p class="mb-2 text-sm font-medium text-danger-600 dark:text-danger-400">Zeilenfehler</p>
        <ul class="list-inside list-disc space-y-1 text-sm text-gray-800 dark:text-gray-200">
            @foreach ($summary['errors'] as $err)
                <li>Zeile {{ $err['row'] ?? '?' }}: {{ $err['message'] ?? '' }}</li>
            @endforeach
        </ul>
    </div>
@endif
