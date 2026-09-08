<x-mail::message>
# Succesvol ingeschreven voor praktijkexamen!

Beste {{ $user->voornaam }} {{ $user->achternaam }},

Je hebt succesvol ingeschreven voor het praktijkexamen.



Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
