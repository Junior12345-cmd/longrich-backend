@component('mail::message')
# Bonjour {{ $shop->user->firstname }} 👋

Merci d'avoir créé votre boutique sur **Longrich Platform**.
Veuillez confirmer votre adresse email pour activer votre compte.

@component('mail::button', ['url' => "" . config('app.url') . "/dash/boutiques"])
Consulter votre boutique
@endcomponent

Si vous n'avez pas créé de boutique, ignorez simplement cet email.

Merci,<br>
L'équipe Longrich 🌍
@endcomponent
