@component('mail::message')
# Bonjour {{ $user->firstname }} 👋

Merci de vous être inscrit sur **Longrich Platform**.
Veuillez confirmer votre adresse email pour activer votre compte.

@component('mail::button', ['url' => $verificationUrl])
Activer mon compte
@endcomponent

Si vous n'avez pas créé de compte, ignorez simplement cet email.

Merci,<br>
L'équipe Longrich 🌍
@endcomponent
