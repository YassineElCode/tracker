<x-mail::message>
Hallo, {{ $user->voornaam }} {{ $user->achternaam }}

U bent ingeschreven voor automatisch inschrijven voor het praktijkexamen.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
