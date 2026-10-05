@extends('layouts.app')

@section('title', $client->name)
@section('breadcrumb', 'Clients / Fiche')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">RÉFÉRENTIEL CLIENTS</p>
            <h1>{{ $client->company ?: $client->name }}</h1>
            <p class="muted">{{ $client->company ? $client->name : 'Client particulier' }}</p>
        </div>
        <div class="heading-actions">
            <a class="button button-quiet" href="{{ route('clients.index') }}">← Tous les clients</a>
            @can('manage-documents')
                <a class="button button-secondary" href="{{ route('quotes.create', ['client_id' => $client->id]) }}">Créer un devis</a>
                <a class="button button-primary" href="{{ route('clients.edit', $client) }}">Modifier</a>
            @endcan
        </div>
    </div>

    <section class="panel detail-panel">
        <h2>Coordonnées</h2>
        <dl class="details-grid">
            <div><dt>Adresse e-mail</dt><dd>{{ $client->email ?: '—' }}</dd></div>
            <div><dt>Téléphone</dt><dd>{{ $client->phone ?: '—' }}</dd></div>
            <div><dt>Adresse de facturation</dt><dd>{{ $client->address ?: '—' }}</dd></div>
            <div><dt>Code postal et ville</dt><dd>{{ trim(($client->postal_code ?? '').' '.($client->city ?? '')) ?: '—' }}</dd></div>
            @if ($client->notes)
                <div class="detail-wide"><dt>Notes</dt><dd class="notes-text">{{ $client->notes }}</dd></div>
            @endif
        </dl>
        <div class="document-note">
            <h3>Historique commercial</h3>
            @if ($client->documents->isEmpty())
                <p class="muted">Aucun devis ou facture enregistré pour ce client.</p>
            @else
                <div class="table-wrap"><table class="data-table"><thead><tr><th>DOCUMENT</th><th>DATE</th><th>STATUT</th><th>TOTAL TTC</th></tr></thead><tbody>
                    @foreach ($client->documents as $document)
                        @php($documentRoute = $document->type === \App\Models\Document::TYPE_QUOTE ? 'quotes.show' : 'invoices.show')
                        <tr><td><a class="table-primary" href="{{ route($documentRoute, $document) }}">{{ $document->number }}</a></td><td>{{ $document->issue_date->format('d/m/Y') }}</td><td>{{ $document->statusLabel() }}</td><td class="amount-cell">{{ $document->formatAmount($document->total) }}</td></tr>
                    @endforeach
                </tbody></table></div>
            @endif
        </div>
        <div class="detail-footer">
            <span class="muted">Fiche créée le {{ $client->created_at->format('d/m/Y') }}</span>
            @can('manage-documents')
                @if ($client->documents->isEmpty())
                    <form action="{{ route('clients.destroy', $client) }}" method="POST" onsubmit="return confirm('Supprimer cette fiche client ?')">
                        @csrf
                        @method('DELETE')
                        <button class="button button-danger-quiet" type="submit">Supprimer ce client</button>
                    </form>
                @else
                    <span class="muted">La fiche ne peut pas être supprimée car elle possède un historique.</span>
                @endif
            @endcan
        </div>
    </section>
@endsection
