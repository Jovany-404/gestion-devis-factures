<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tableau de bord') · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <a class="brand" href="{{ route('dashboard') }}">
                <span class="brand-mark">G</span>
                <span>Gestion<span class="brand-subtitle">Devis & factures</span></span>
            </a>
            <div class="nav-label">ESPACE DE TRAVAIL</div>
            <nav class="primary-nav" aria-label="Navigation principale">
                <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <span class="nav-icon">⌂</span> Vue d’ensemble
                </a>
                <a class="{{ request()->routeIs('clients.*') ? 'active' : '' }}" href="{{ route('clients.index') }}">
                    <span class="nav-icon">01</span> Clients
                </a>
                <a class="{{ request()->routeIs('articles.*') ? 'active' : '' }}" href="{{ route('articles.index') }}">
                    <span class="nav-icon">02</span> Catalogue
                </a>
                <a class="{{ request()->routeIs('quotes.*') ? 'active' : '' }}" href="{{ route('quotes.index') }}">
                    <span class="nav-icon">03</span> Devis
                </a>
                <a class="{{ request()->routeIs('invoices.*') ? 'active' : '' }}" href="{{ route('invoices.index') }}">
                    <span class="nav-icon">04</span> Factures
                </a>
            </nav>
            <div class="nav-label nav-label-settings">ADMINISTRATION</div>
            <nav class="primary-nav" aria-label="Administration">
                @can('manage-company')
                    <a class="{{ request()->routeIs('company.*') ? 'active' : '' }}" href="{{ route('company.edit') }}"><span class="nav-icon">05</span> Entreprise</a>
                    <a class="{{ request()->routeIs('staff.*') ? 'active' : '' }}" href="{{ route('staff.index') }}"><span class="nav-icon">06</span> Équipe</a>
                @endcan
                <a class="{{ request()->routeIs('api-tokens.*') ? 'active' : '' }}" href="{{ route('api-tokens.index') }}"><span class="nav-icon">API</span> Accès API</a>
            </nav>
        </aside>

        <div class="main-column">
            <header class="topbar">
                <div class="breadcrumb">Mon espace <span>/</span> @yield('breadcrumb', 'Tableau de bord')</div>
                <div class="profile">
                    <span class="profile-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                    <span>{{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST">@csrf<button class="logout-button" type="submit">Déconnexion</button></form>
                </div>
            </header>
            <main class="page-content">
                @if (session('success'))
                    <div class="flash-success" role="status">{{ session('success') }}</div>
                @endif
                @if (session('warning'))
                    <div class="flash-warning" role="status">{{ session('warning') }}</div>
                @endif
                @if ($errors->any())
                    <div class="flash-warning" role="alert">
                        <strong>Vérifiez les informations saisies.</strong>
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                @yield('content')
            </main>
            <footer class="footer">{{ config('app.name') }}</footer>
        </div>
    </div>
</body>
</html>
