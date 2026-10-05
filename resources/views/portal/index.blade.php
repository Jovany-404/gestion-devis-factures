@extends('layouts.portal')

@section('title', 'Mes documents')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">ESPACE CLIENT</p>
            <h1>Bonjour {{ $client->name }}</h1>
            <p class="muted">Retrouvez ici vos devis, factures et prestations avec {{ $company->name }}.</p>
        </div>
    </div>

    <section class="panel">
        <div class="panel-heading">
            <div>
                <h2>Vos documents</h2>
                <p class="muted">Seuls les documents associés à votre compte sont affichés.</p>
            </div>
        </div>
        @if ($documents->isEmpty())
            <div class="empty-state">
                <span class="empty-icon">01</span>
                <h3>Aucun document pour le moment</h3>
                <p>Lorsqu’un devis ou une facture vous sera adressé, il apparaîtra dans cet espace.</p>
            </div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr><th>DOCUMENT</th><th>PRESTATION</th><th>DATE</th><th>STATUT</th><th class="text-right">TOTAL TTC</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($documents as $document)
                            <tr>
                                <td>
                                    <a class="table-primary" href="{{ route('portal.documents.show', $document) }}">{{ $document->number }}</a>
                                    <span class="table-secondary">{{ $document->type === \App\Models\Document::TYPE_QUOTE ? 'Devis' : 'Facture' }}</span>
                                </td>
                                <td>
                                    @foreach ($document->lines as $line)
                                        <span class="table-primary">{{ $line->description }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $document->issue_date->format('d/m/Y') }}</td>
                                <td><span class="status-badge status-{{ $document->displayStatus() }}">{{ $document->statusLabel() }}</span></td>
                                <td class="amount-cell">{{ number_format((float) $document->total, 2, ',', ' ') }} €</td>
                                <td><a class="row-action" href="{{ route('portal.documents.show', $document) }}" aria-label="Consulter {{ $document->number }}">→</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="portal-pagination">{{ $documents->links() }}</div>
        @endif
    </section>
@endsection
