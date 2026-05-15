<x-mail::message>
# {{ $headline }}

@if(filled($intro))
{{ $intro }}
@endif

@if(filled($clientName) || filled($projectName))
**Kund:in:** {{ $clientName ?? '—' }}

**Projekt:** {{ $projectName ?? '—' }}
@endif

@if(filled($itemLabel))
**Betroffen:** {{ $itemLabel }}
@endif

@if(count($changes) > 0)
<x-mail::table>
| Feld | Bisher | Neu |
|:-----|:-------|:----|
@foreach($changes as $change)
| {{ $change['label'] ?? $change['field'] }} | {{ $change['old'] ?? '—' }} | **{{ $change['new'] ?? '—' }}** |
@endforeach
</x-mail::table>
@endif

Bearbeitet von: **{{ $changedBy ?? 'System' }}**

Zeitpunkt: {{ $changedAt }}

@if(filled($actionUrl))
<x-mail::button :url="$actionUrl">
Zum Projekt
</x-mail::button>
@endif

Mit freundlichen Grüßen,<br>
{{ config('app.name') }}
</x-mail::message>
