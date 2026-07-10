@component('mail::message')
# Bonjour {{ $quoteRequest->first_name }},

Nous avons bien reçu votre demande de devis. Notre équipe l'examine et **reviendra vers vous très prochainement**.

## Récapitulatif de votre demande

@component('mail::table')
| Référence | Produit | Quantité |
|:----------|:--------|:--------:|
@foreach($items as $item)
| {{ $item->product_reference }} | {{ $item->product_name }} | {{ $item->quantity }} |
@endforeach
@endcomponent

Si vous avez une question urgente, n'hésitez pas à nous contacter directement.

Merci de votre confiance,<br>
**AMEG International**
@endcomponent
