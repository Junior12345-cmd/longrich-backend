<x-mail::message>
@if($isAdmin)
# Bonjour,
Vous avez reçu une commande **#{{ $commande->id }}** sur **{{ $shop->title ?? config('app.name') }}**.

<x-mail::panel>
**Statut actuel :** 
@switch($commande->status)
    @case('approved')
        Approuvée
        @break
    @case('declined')
        Refusée
        @break
    @case('pending')
        En attente
        @break
    @default
        {{ ucfirst($commande->status) }}
@endswitch  
**Montant :** {{ number_format($commande->amount, 0, ',', ' ') }} FCFA  
**Référence :** {{ $commande->reference ?? 'N/A' }}  
**Client :** {{ json_decode($commande->customer, true)['name'] ?? 'N/A' }}  
**Date de commande :** {{ \Carbon\Carbon::parse($commande->created_at)->translatedFormat('d F Y H:i') }}
</x-mail::panel>

Merci de traiter cette commande rapidement.

@else
# Bonjour {{ json_decode($commande->customer, true)['name'] ?? '' }},

Votre commande **#{{ $commande->id }}** sur **{{ $shop->title ?? config('app.name') }}** a été mise à jour.

<x-mail::panel>
**Statut actuel :** 
@switch($commande->status)
    @case('approved')
        Approuvée
        @break
    @case('declined')
        Refusée
        @break
    @case('pending')
        En attente
        @break
    @default
        {{ ucfirst($commande->status) }}
@endswitch  
**Montant :** {{ number_format($commande->amount, 0, ',', ' ') }} FCFA  
**Référence :** {{ $commande->reference ?? 'N/A' }}  
**Date de commande :** {{ \Carbon\Carbon::parse($commande->created_at)->translatedFormat('d F Y H:i') }}
</x-mail::panel>

Merci d’avoir fait confiance à **{{ $shop->title ?? config('app.name') }}**.
@endif

Cordialement,  
**{{ $shop->title ?? config('app.name') }}**  
{{ $shop->email ?? config('mail.from.address') }}
</x-mail::message>
