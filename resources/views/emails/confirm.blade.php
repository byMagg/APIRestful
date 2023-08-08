@component('mail::message')

@component('mail::button', ['url' => route('verify', $user->verification_token)])
Confirmar mi cuenta
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent

<x-mail::message>
  # Hola {{$user->name}}

  Has cambiado tu correo electrónico. Por favor verifica la nueva dirección usando el siguiente botón:

  <x-mail::button :url="route('verify', $user->verification_token)">
    Confirmar mi cuenta
  </x-mail::button>

  Gracias,<br>
  {{ config('app.name') }}
</x-mail::message>