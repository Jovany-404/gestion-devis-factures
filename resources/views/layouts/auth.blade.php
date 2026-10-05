<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="auth-page">
    <main class="auth-card">
        <a class="brand auth-brand" href="{{ route('login') }}">
            <span class="brand-mark">GF</span>
            <span>{{ config('app.name') }}<span class="brand-subtitle">DEVIS · FACTURES · SUIVI</span></span>
        </a>
        @if (session('success'))<div class="flash-success">{{ session('success') }}</div>@endif
        @if ($errors->any())
            <div class="flash-warning" role="alert">
                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>
