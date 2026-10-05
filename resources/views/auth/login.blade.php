@extends('layouts.auth')

@section('title', 'Connexion')

@section('content')
    <p class="eyebrow">ESPACE DE TRAVAIL</p>
    <h1>Connexion</h1>
    <p class="muted auth-intro">Accédez à vos clients, à vos devis et au suivi des règlements.</p>
    @if ($setupRequired)
        <div class="flash-warning">Première utilisation : créez le compte administrateur pour initialiser l’espace de travail.</div>
        <a class="button button-primary button-full" href="{{ route('register') }}">Créer l’espace de travail</a>
    @else
        <form action="{{ route('login.store') }}" method="POST" class="auth-form">
            @csrf
            <div class="field"><label for="email">Adresse e-mail</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"></div>
            <div class="field"><label for="password">Mot de passe</label><input id="password" type="password" name="password" required autocomplete="current-password"></div>
            <label class="checkbox-field"><input type="checkbox" name="remember" value="1"> Garder ma session ouverte</label>
            <button class="button button-primary button-full" type="submit">Se connecter</button>
        </form>
    @endif
    <p class="auth-footnote">Accès réservé aux membres de l’entreprise.</p>
@endsection
