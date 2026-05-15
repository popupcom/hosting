@props(['rows' => []])
@if ($rows === [])
    <p class="text-sm text-gray-500 dark:text-gray-400">Keine Daten.</p>
@else
    <div class="fi-section-content-ctn overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-white/10">
                    @foreach (array_keys($rows[0] ?? []) as $h)
                        <th class="px-2 py-1 text-left font-medium text-gray-600 dark:text-gray-300">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr class="border-b border-gray-100 dark:border-white/5">
                        @foreach ($row as $cell)
                            <td class="px-2 py-1 align-top text-gray-900 dark:text-gray-100">{{ $cell ?? '—' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
