@component('mail::message')
# Bonjour {{ $projectStudyRequest->name }},

Nous avons bien reçu votre demande d'étude de projet. Notre équipe l'étudie et **reviendra vers vous très prochainement** pour échanger sur vos besoins.

## Récapitulatif

@if($projectStudyRequest->establishment_type)
**Type d'établissement :** {{ $projectStudyRequest->establishment_type }}
@endif

{{ $projectStudyRequest->description }}

Merci de votre confiance,<br>
**AMEG International**

@include('emails.partials.signature')
@endcomponent
