@component('mail::message')
# Bonjour {{ $contactMessage->name }},

Nous avons bien reçu votre message et **nous reviendrons vers vous très prochainement**.

## Votre message
{{ $contactMessage->message }}

Merci de votre confiance,<br>
**AMEG International**
@endcomponent
