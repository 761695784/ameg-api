@component('mail::message')
# Nouvelle demande de devis

Une nouvelle demande de devis vient d'être soumise sur le site.

**Client :** {{ $quoteRequest->first_name }} {{ $quoteRequest->last_name }}
@if($quoteRequest->company)
**Société :** {{ $quoteRequest->company }}
@endif
**Téléphone :** {{ $quoteRequest->phone }}
**Email :** {{ $quoteRequest->email }}
@if($quoteRequest->city)
**Ville :** {{ $quoteRequest->city }}
@endif

## Produits demandés

@component('mail::table')
| Référence | Produit | Quantité |
|:----------|:--------|:--------:|
@foreach($items as $item)
| {{ $item->product_reference }} | {{ $item->product_name }} | {{ $item->quantity }} |
@endforeach
@endcomponent

@if($quoteRequest->comment)
## Commentaire du client
{{ $quoteRequest->comment }}
@endif

@component('mail::button', ['url' => config('app.url') . '/admin/devis/' . $quoteRequest->id])
Voir la demande dans le tableau de bord
@endcomponent

Merci,<br>
Site {{ config('app.name') }}
@endcomponent
