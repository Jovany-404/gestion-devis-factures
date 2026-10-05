@extends('layouts.app')

@php($title = $type === 'quote' ? 'Devis' : 'Factures')
@section('title', $title)
@section('breadcrumb', $title)

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">SUIVI COMMERCIAL</p>
            <h1>{{ $title }}</h1>
            <p class="muted">Numérotation automatique annuelle · montants et statuts conservés dans l’historique.</p>
        </div>
        <div class="heading-actions">
            <a class="button button-quiet" href="{{ route('documents.export', ['type' => $type]) }}">Exporter la liste CSV</a>
            @if ($type === 'quote')
                @can('manage-documents')<a class="button button-primary" href="{{ route('quotes.create') }}">Créer un devis</a>@endcan
            @endif
        </div>
    </div>
    <section class="panel">
        <form class="search-form" action="{{ route($type === 'quote' ? 'quotes.index' : 'invoices.index') }}" method="GET">
            <label class="search-box"><span aria-hidden="true">⌕</span><input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Rechercher par numéro ou client…" aria-label="Rechercher un document"></label>
            <select class="filter-select" name="status" aria-label="Filtrer par statut">
                <option value="">Tous les statuts</option>
                @foreach ($type === 'quote' ? ['draft', 'sent', 'accepted', 'rejected'] : ['draft', 'sent', 'paid', 'overdue', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ match($status) { 'draft' => 'Brouillon', 'sent' => 'Envoyé', 'accepted' => 'Accepté', 'rejected' => 'Refusé', 'paid' => 'Payé', 'overdue' => 'En retard', default => 'Annulé' }}</option>
                @endforeach
            </select>
            <button class="button button-secondary" type="submit">Filtrer</button>
            @if ($filters)<a class="clear-search" href="{{ route($type === 'quote' ? 'quotes.index' : 'invoices.index') }}">Effacer</a>@endif
        </form>
        @if ($documents->isEmpty())
            <div class="empty-state"><span class="empty-icon">{{ $type === 'quote' ? '03' : '04' }}</span><h3>{{ $type === 'quote' ? 'Aucun devis correspondant' : 'Aucune facture correspondante' }}</h3><p>{{ $type === 'quote' ? 'Créez un devis à partir des coordonnées d’un client et de votre catalogue.' : 'Une facture est créée à partir d’un devis accepté.' }}</p>@if ($type === 'quote')@can('manage-documents')<a class="button button-primary" href="{{ route('quotes.create') }}">Créer un devis</a>@endcan@endif</div>
        @else
            <div class="table-wrap"><table class="data-table">
                <thead><tr><th>NUMÉRO</th><th>CLIENT</th><th>DATE</th><th>ÉCHÉANCE</th><th>STATUT</th><th class="text-right">TOTAL TTC</th><th></th></tr></thead>
                <tbody>@foreach ($documents as $document)
                    <tr>
                        <td><a class="table-primary" href="{{ route($type === 'quote' ? 'quotes.show' : 'invoices.show', $document) }}">{{ $document->number }}</a><span class="table-secondary">{{ $type === 'quote' ? 'Devis' : 'Facture' }}</span></td>
                        <td>{{ $document->client_snapshot['company'] ?: $document->client_snapshot['name'] }}</td>
                        <td>{{ $document->issue_date->format('d/m/Y') }}</td>
                        <td>{{ ($document->due_date ?? $document->valid_until)?->format('d/m/Y') ?? '—' }}</td>
                        <td><span class="status-badge status-{{ $document->displayStatus() }}">{{ $document->statusLabel() }}</span></td>
                        <td class="amount-cell">{{ number_format((float) $document->total, 2, ',', ' ') }} €</td>
                        <td><a class="row-action" href="{{ route($type === 'quote' ? 'quotes.show' : 'invoices.show', $document) }}">Ouvrir →</a></td>
                    </tr>
                @endforeach</tbody>
            </table></div>
            <div class="pagination">{{ $documents->links() }}</div>
        @endif
    </section>
@endsection
