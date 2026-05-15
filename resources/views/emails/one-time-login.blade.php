<x-mail::message>
# Einmal-Anmeldung

Du hast einen Anmeldelink für **{{ config('app.name') }}** angefordert.

<x-mail::button :url="$actionUrl">
Jetzt anmelden
</x-mail::button>

Dieser Link ist nur begrenzt gültig und kann **nur einmal** verwendet werden. Wenn du diese E-Mail nicht angefordert hast, kannst du sie ignorieren.

</x-mail::message>
