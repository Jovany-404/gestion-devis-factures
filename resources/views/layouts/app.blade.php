<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tableau de bord') · Gestion</title>
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
                    <span class="nav-icon">⌂</span> Tableau de bord
                </a>
                <a class="{{ request()->routeIs('clients.*') ? 'active' : '' }}" href="{{ route('clients.index') }}">
                    <span class="nav-icon">♙</span> Clients
                </a>
                <span class="nav-disabled"><span class="nav-icon">▤</span> Devis <small>Bientôt</small></span>
                <span class="nav-disabled"><span class="nav-icon">▧</span> Factures <small>Bientôt</small></span>
            </nav>
            <div class="sidebar-note">
                <span class="note-dot"></span>
                <div><strong>Votre espace</strong><br><span>Prêt à gérer votre activité</span></div>
            </div>
        </aside>

        <div class="main-column">
            <header class="topbar">
                <div class="breadcrumb">Mon espace <span>/</span> @yield('breadcrumb', 'Tableau de bord')</div>
                <div class="profile"><span class="profile-avatar">G</span><span>Gestionnaire</span></div>
            </header>
            <main class="page-content">
                @if (session('success'))
                    <div class="flash-success" role="status">{{ session('success') }}</div>
                @endif
                @yield('content')
            </main>
            <footer class="footer">Gestion · Votre activité, simplement.</footer>
        </div>
    </div>
</body>
</html>
