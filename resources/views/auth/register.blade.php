@extends('layouts.auth')

@section('title', 'Créer l’espace de travail')

@section('content')
    <p class="eyebrow">CONFIGURATION INITIALE</p>
    <h1>Créer l’espace de travail</h1>
    <p class="muted auth-intro">Le premier compte devient administrateur de l’entreprise. L’inscription est désactivée dès sa création.</p>
    <form action="{{ route('register.store') }}" method="POST" class="auth-form">
        @csrf
        <div class="field"><label for="name">Nom complet</label><input id="name" name="name" value="{{ old('name') }}" required maxlength="150" autocomplete="name"></div>
        <div class="field"><label for="email">Adresse e-mail professionnelle</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></div>
        <div class="field"><label for="password">Mot de passe (12 caractères minimum)</label><input id="password" type="password" name="password" required minlength="12" autocomplete="new-password"></div>
        <div class="field"><label for="password_confirmation">Confirmer le mot de passe</label><input id="password_confirmation" type="password" name="password_confirmation" required minlength="12" autocomplete="new-password"></div>
        <button class="button button-primary button-full" type="submit">Créer le compte administrateur</button>
    </form>
    <p class="auth-footnote"><a class="text-link" href="{{ route('login') }}">Retour à la connexion</a></p>
@endsection
