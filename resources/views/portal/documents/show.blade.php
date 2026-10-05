@extends('layouts.portal')

@section('title', $document->number)
@section('breadcrumb', 'Mes documents / '.$document->number)

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">{{ $document->type === \App\Models\Document::TYPE_QUOTE ? 'DEVIS' : 'FACTURE' }}</p>
            <h1>{{ $document->number }}</h1>
            <p class="muted">{{ $document->issue_date->format('d/m/Y') }} · {{ $document->client_snapshot['company'] ?: $document->client_snapshot['name'] }}</p>
        </div>
        <div class="heading-actions">
            <a class="button button-quiet" href="{{ route('portal.index') }}">← Mes documents</a>
            <a class="button button-secondary" href="{{ route('portal.documents.pdf', $document) }}">Télécharger le PDF</a>
        </div>
    </div>

    <div class="document-detail-grid">
        <section class="panel detail-panel">
            <div class="document-status-line">
                <span class="status-badge status-{{ $document->displayStatus() }}">{{ $document->statusLabel() }}</span>
                <span class="muted">Émis le {{ $document->issue_date->format('d/m/Y') }}</span>
            </div>
            <h2>Prestations prévues</h2>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>DÉSIGNATION</th><th>QTÉ</th><th>PRIX HT</th><th>TVA</th><th class="text-right">TOTAL TTC</th></tr></thead>
                    <tbody>
                        @foreach ($document->lines as $line)
                            <tr>
                                <td><span class="table-primary">{{ $line->description }}</span><span class="table-secondary">Unité : {{ $line->unit }}</span></td>
                                <td>{{ number_format((float) $line->quantity, 2, ',', ' ') }}</td>
                                <td>{{ $document->formatAmount($line->unit_price) }}</td>
                                <td>{{ number_format((float) $line->tax_rate, 2, ',', ' ') }} %</td>
                                <td class="amount-cell">{{ $document->formatAmount($line->total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="line-summary document-totals">
                <div><span>Sous-total HT</span><strong>{{ $document->formatAmount($document->subtotal) }}</strong></div>
                <div><span>TVA</span><strong>{{ $document->formatAmount($document->tax_total) }}</strong></div>
                <div class="summary-total"><span>Total TTC</span><strong>{{ $document->formatAmount($document->total) }}</strong></div>
            </div>
            @if ($document->notes)
                <div class="document-note"><h3>Note</h3><p>{{ $document->notes }}</p></div>
            @endif
            @if ($document->terms)
                <div class="document-note"><h3>Conditions</h3><p>{{ $document->terms }}</p></div>
            @endif
        </section>

        <aside class="document-sidebar">
            <section class="panel detail-panel">
                <h2>Suivi</h2>
                <dl class="details-grid details-single">
                    @if ($document->valid_until)<div><dt>Devis valable jusqu’au</dt><dd>{{ $document->valid_until->format('d/m/Y') }}</dd></div>@endif
                    @if ($document->due_date)<div><dt>Échéance</dt><dd>{{ $document->due_date->format('d/m/Y') }}</dd></div>@endif
                    @if ($document->sent_at)<div><dt>Transmis le</dt><dd>{{ $document->sent_at->format('d/m/Y') }}</dd></div>@endif
                    @if ($document->paid_at)<div><dt>Règlement enregistré</dt><dd>{{ $document->paid_at->format('d/m/Y') }}</dd></div>@endif
                    @if ($document->invoice)<div><dt>Facture liée</dt><dd><a class="text-link" href="{{ route('portal.documents.show', $document->invoice) }}">{{ $document->invoice->number }}</a></dd></div>@endif
                    @if ($document->sourceQuote)<div><dt>Devis d’origine</dt><dd><a class="text-link" href="{{ route('portal.documents.show', $document->sourceQuote) }}">{{ $document->sourceQuote->number }}</a></dd></div>@endif
                </dl>
            </section>
            @if ($document->type === \App\Models\Document::TYPE_QUOTE && $document->status === 'sent')
                <section class="panel detail-panel document-actions-panel">
                    <h2>Votre réponse</h2>
                    <p class="muted">Après consultation, vous pouvez donner votre accord ou refuser ce devis.</p>
                    <form action="{{ route('portal.quotes.accept', $document) }}" method="POST">
                        @csrf
                        <button class="button button-primary button-full" type="submit">Accepter le devis</button>
                    </form>
                    <form action="{{ route('portal.quotes.reject', $document) }}" method="POST">
                        @csrf
                        <button class="button button-quiet button-full" type="submit">Refuser le devis</button>
                    </form>
                </section>
            @elseif ($document->type === \App\Models\Document::TYPE_QUOTE && $document->status === 'accepted')
                <section class="panel detail-panel"><h2>Accord enregistré</h2><p class="muted">Votre réponse a été transmise à {{ $company->name }}.</p></section>
            @endif
        </aside>
    </div>
@endsection
