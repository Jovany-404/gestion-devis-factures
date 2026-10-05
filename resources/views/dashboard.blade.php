@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">GESTION COMMERCIALE</p>
            <h1>Suivi de l’activité</h1>
            <p class="muted">De la proposition commerciale à l’encaissement, retrouvez l’état de votre facturation.</p>
        </div>
        @can('manage-documents')
            <a class="button button-primary" href="{{ route('quotes.create') }}">Créer un devis</a>
        @endcan
    </div>

    @if (! $profileReady && auth()->user()->isAdministrator())
        <div class="flash-warning">
            <strong>Configuration requise :</strong> renseignez les coordonnées de votre entreprise avant de créer votre premier devis.
            <a class="text-link" href="{{ route('company.edit') }}">Compléter le profil entreprise →</a>
        </div>
    @endif

    <section class="stats-grid" aria-label="Résumé de l'activité">
        <a class="stat-card stat-link" href="{{ route('clients.index') }}">
            <div class="stat-top"><span class="stat-icon icon-purple">01</span><span class="stat-caption">Carnet d’adresses</span></div>
            <strong class="stat-number">{{ $clientCount }}</strong>
            <span class="stat-label">Client{{ $clientCount === 1 ? '' : 's' }} actif{{ $clientCount === 1 ? '' : 's' }}</span>
        </a>
        <a class="stat-card stat-link" href="{{ route('quotes.index') }}">
            <div class="stat-top"><span class="stat-icon icon-blue">03</span><span class="stat-caption">Devis à suivre</span></div>
            <strong class="stat-number">{{ $openQuotes }}</strong>
            <span class="stat-label">En attente · {{ number_format((float) $quotePipeline, 2, ',', ' ') }} € HT</span>
        </a>
        <a class="stat-card stat-link" href="{{ route('invoices.index', ['status' => 'sent']) }}">
            <div class="stat-top"><span class="stat-icon icon-amber">04</span><span class="stat-caption">Factures à encaisser</span></div>
            <strong class="stat-number">{{ number_format((float) $outstandingInvoices, 2, ',', ' ') }} €</strong>
            <span class="stat-label">Montant TTC en attente</span>
        </a>
        <div class="stat-card">
            <div class="stat-top"><span class="stat-icon icon-green">€</span><span class="stat-caption">Encaissements du mois</span></div>
            <strong class="stat-number">{{ number_format((float) $paidThisMonth, 2, ',', ' ') }} €</strong>
            <span class="stat-label">Factures réglées ce mois-ci</span>
        </div>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <div>
                <h2>Documents récents</h2>
                <p class="muted">Derniers devis et dernières factures émis.</p>
            </div>
            <a class="text-link" href="{{ route('documents.export') }}">Exporter en CSV →</a>
        </div>
        @if ($recentDocuments->isEmpty())
            <div class="empty-state">
                <span class="empty-icon">01</span>
                <h3>Aucun document commercial</h3>
                <p>Créez un client et ajoutez vos prestations au catalogue pour préparer votre premier devis.</p>
                <div class="heading-actions">
                    <a class="button button-secondary" href="{{ route('clients.index') }}">Voir les clients</a>
                    <a class="button button-secondary" href="{{ route('articles.index') }}">Gérer le catalogue</a>
                </div>
            </div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>DOCUMENT</th><th>CLIENT</th><th>DATE</th><th>STATUT</th><th>TOTAL TTC</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($recentDocuments as $document)
                        @php($route = $document->type === \App\Models\Document::TYPE_QUOTE ? 'quotes.show' : 'invoices.show')
                        <tr>
                            <td><a class="table-primary" href="{{ route($route, $document) }}">{{ $document->number }}</a><span class="table-secondary">{{ $document->type === 'quote' ? 'Devis' : 'Facture' }}</span></td>
                            <td>{{ $document->client_snapshot['company'] ?: $document->client_snapshot['name'] }}</td>
                            <td>{{ $document->issue_date->format('d/m/Y') }}</td>
                            <td><span class="status-badge status-{{ $document->displayStatus() }}">{{ $document->statusLabel() }}</span></td>
                            <td class="amount-cell">{{ number_format((float) $document->total, 2, ',', ' ') }} €</td>
                            <td><a class="row-action" href="{{ route($route, $document) }}" aria-label="Ouvrir {{ $document->number }}">→</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="dashboard-secondary">
        <div class="panel panel-heading">
            <div><h2>Clients</h2><p class="muted">Coordonnées et historique des clients.</p></div>
            <a class="text-link" href="{{ route('clients.index') }}">Ouvrir le carnet →</a>
        </div>
        <div class="panel panel-heading">
            <div><h2>Catalogue</h2><p class="muted">Prestations, prix unitaires et TVA appliquée.</p></div>
            <a class="text-link" href="{{ route('articles.index') }}">Gérer les articles →</a>
        </div>
    </section>
@endsection
