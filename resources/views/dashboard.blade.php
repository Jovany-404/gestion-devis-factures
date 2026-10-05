@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">VUE D'ENSEMBLE</p>
            <h1>Bonjour, bienvenue 👋</h1>
            <p class="muted">Retrouvez ici les informations clés de votre activité.</p>
        </div>
        <a class="button button-primary" href="{{ route('clients.create') }}"><span>＋</span> Ajouter un client</a>
    </div>

    <section class="stats-grid" aria-label="Résumé de l'activité">
        <a class="stat-card stat-link" href="{{ route('clients.index') }}">
            <div class="stat-top"><span class="stat-icon icon-purple">♙</span><span class="stat-caption">Votre fichier</span></div>
            <strong class="stat-number">{{ $clientCount }}</strong>
            <span class="stat-label">Client{{ $clientCount > 1 ? 's' : '' }} enregistré{{ $clientCount > 1 ? 's' : '' }}</span>
        </a>
        <div class="stat-card">
            <div class="stat-top"><span class="stat-icon icon-blue">▤</span><span class="stat-caption">Gestion commerciale</span></div>
            <strong class="stat-number">—</strong>
            <span class="stat-label">Devis · bientôt disponible</span>
        </div>
        <div class="stat-card">
            <div class="stat-top"><span class="stat-icon icon-green">▧</span><span class="stat-caption">Suivi des paiements</span></div>
            <strong class="stat-number">—</strong>
            <span class="stat-label">Factures · bientôt disponible</span>
        </div>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <div>
                <h2>Clients récents</h2>
                <p class="muted">Les dernières fiches ajoutées à votre espace.</p>
            </div>
            <a class="text-link" href="{{ route('clients.index') }}">Voir tous les clients <span>→</span></a>
        </div>
        @if ($recentClients->isEmpty())
            <div class="empty-state">
                <span class="empty-icon">♙</span>
                <h3>Votre carnet de clients est vide</h3>
                <p>Ajoutez votre premier client pour préparer vos futurs devis et factures.</p>
                <a class="button button-secondary" href="{{ route('clients.create') }}">Créer une fiche client</a>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>CLIENT</th><th>COORDONNÉES</th><th>VILLE</th><th>AJOUTÉ LE</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($recentClients as $client)
                        <tr>
                            <td><a class="table-primary" href="{{ route('clients.show', $client) }}">{{ $client->name }}</a><span class="table-secondary">{{ $client->company ?: 'Particulier' }}</span></td>
                            <td>{{ $client->email ?: $client->phone ?: '—' }}</td>
                            <td>{{ $client->city ?: '—' }}</td>
                            <td>{{ $client->created_at->format('d/m/Y') }}</td>
                            <td><a class="row-action" href="{{ route('clients.show', $client) }}" aria-label="Voir {{ $client->name }}">→</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
