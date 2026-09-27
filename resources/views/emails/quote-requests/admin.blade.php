@component('mail::message')
# Nouvelle demande de devis

Une nouvelle demande de devis vient d'être soumise sur le site.

**Client :** {{ $quoteRequest->first_name }} {{ $quoteRequest->last_name }} <br>
@if($quoteRequest->company)
**Société :** {{ $quoteRequest->company }} <br>
@endif
**Téléphone :** {{ $quoteRequest->phone }} <br>
**Email :** {{ $quoteRequest->email }}  <br>
@if($quoteRequest->city)
**Ville :** {{ $quoteRequest->city }}  <br>
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

@include('emails.partials.signature')
@endcomponent
