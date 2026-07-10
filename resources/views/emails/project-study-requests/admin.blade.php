@component('mail::message')
# Nouvelle demande d'étude de projet

**Nom :** {{ $projectStudyRequest->name }}
@if($projectStudyRequest->company)
**Société :** {{ $projectStudyRequest->company }}
@endif
**Téléphone :** {{ $projectStudyRequest->phone }}
**Email :** {{ $projectStudyRequest->email }}
@if($projectStudyRequest->city)
**Ville :** {{ $projectStudyRequest->city }}
@endif
@if($projectStudyRequest->establishment_type)
**Type d'établissement :** {{ $projectStudyRequest->establishment_type }}
@endif
@if($projectStudyRequest->estimated_budget)
**Budget estimé :** {{ $projectStudyRequest->estimated_budget }}
@endif
@if($projectStudyRequest->desired_deadline)
**Délai souhaité :** {{ $projectStudyRequest->desired_deadline }}
@endif

## Description du projet
{{ $projectStudyRequest->description }}

@if($documents->isNotEmpty())
## Documents joints
@foreach($documents as $document)
- {{ $document->original_name }}
@endforeach
@endif

@component('mail::button', ['url' => config('app.url') . '/admin/etudes-projet/' . $projectStudyRequest->id])
Voir la demande dans le tableau de bord
@endcomponent

Merci,<br>
Site {{ config('app.name') }}
@endcomponent
