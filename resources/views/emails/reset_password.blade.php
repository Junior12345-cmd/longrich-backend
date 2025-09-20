@component('mail::message')
# Réinitialisation du mot de passe

Vous avez demandé à réinitialiser votre mot de passe sur **Longrich Platform**.

Cliquez sur le bouton ci-dessous pour définir un nouveau mot de passe :

@component('mail::button', ['url' => url('/reset-password?token='.$token.'&email='.$email)])
Réinitialiser le mot de passe
@endcomponent

Si vous n'avez pas demandé ce changement, ignorez cet email.

Merci,<br>
L'équipe Longrich 🌍
@endcomponent
