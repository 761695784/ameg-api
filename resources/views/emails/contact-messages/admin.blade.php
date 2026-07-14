@component('mail::message')
# Nouveau message de contact

**Nom :** {{ $contactMessage->name }} <br>
**Email :** {{ $contactMessage->email }} <br>
@if($contactMessage->phone)
**Téléphone :** {{ $contactMessage->phone }} <br>
@endif
@if($contactMessage->subject)
**Sujet :** {{ $contactMessage->subject }} <br>
@endif

## Message
{{ $contactMessage->message }}

@component('mail::button', ['url' => config('app.url') . '/admin/messages/' . $contactMessage->id])
Voir le message dans le tableau de bord
@endcomponent

Merci,<br>
Site {{ config('app.name') }}
@endcomponent
