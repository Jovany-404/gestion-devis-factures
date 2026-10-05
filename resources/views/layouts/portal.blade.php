<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Espace client') · {{ $company->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <a class="brand" href="{{ route('portal.index') }}">
                <span class="brand-mark">SN</span>
                <span>{{ $company->name }}<span class="brand-subtitle">Espace client</span></span>
            </a>
            <div class="nav-label">VOTRE ESPACE</div>
            <nav class="primary-nav" aria-label="Navigation client">
                <a class="{{ request()->routeIs('portal.index') ? 'active' : '' }}" href="{{ route('portal.index') }}">
                    <span class="nav-icon">01</span> Mes documents
                </a>
            </nav>
            <div class="portal-contact">
                <span class="nav-label">UNE QUESTION ?</span>
                @if ($company->email)
                    <a href="mailto:{{ $company->email }}">{{ $company->email }}</a>
                @else
                    <span>Contactez votre interlocuteur habituel.</span>
                @endif
            </div>
        </aside>

        <div class="main-column">
            <header class="topbar">
                <div class="breadcrumb">{{ $company->name }} <span>/</span> @yield('breadcrumb', 'Mes documents')</div>
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
                @if ($errors->any())
                    <div class="flash-warning" role="alert">
                        <strong>Vérifiez les informations saisies.</strong>
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                @yield('content')
            </main>
            <footer class="footer">{{ $company->name }} · Documents et suivi client</footer>
        </div>
    </div>
</body>
</html>
