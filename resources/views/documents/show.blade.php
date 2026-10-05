@extends('layouts.app')
@section('title', $document->number)
@section('breadcrumb', ($view === 'quote' ? 'Devis' : 'Factures').' / '.$document->number)
@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">{{ $view === 'quote' ? 'DEVIS' : 'FACTURE' }} · {{ $document->number }}</p><h1>{{ $document->number }}</h1><p class="muted">{{ $document->client_snapshot['company'] ?: $document->client_snapshot['name'] }}</p></div>
        <div class="heading-actions">
            <a class="button button-quiet" href="{{ route($view === 'quote' ? 'quotes.index' : 'invoices.index') }}">Retour à la liste</a>
            <a class="button button-secondary" href="{{ route('documents.pdf', $document) }}">Télécharger le PDF</a>
            <a class="button button-quiet" href="{{ route('documents.export', ['type' => $document->type]) }}">Exporter CSV</a>
        </div>
    </div>
    <div class="document-detail-grid">
        <section class="panel detail-panel">
            <div class="document-status-line"><span class="status-badge status-{{ $document->displayStatus() }}">{{ $document->statusLabel() }}</span><span class="muted">Créé le {{ $document->created_at->format('d/m/Y à H:i') }}</span></div>
            <h2>Prestations</h2>
            <div class="table-wrap"><table class="data-table">
                <thead><tr><th>DÉSIGNATION</th><th>QTÉ</th><th>PRIX HT</th><th>TVA</th><th class="text-right">TOTAL TTC</th></tr></thead>
                <tbody>@foreach ($document->lines as $line)<tr><td><span class="table-primary">{{ $line->description }}</span><span class="table-secondary">Unité : {{ $line->unit }}</span></td><td>{{ number_format((float) $line->quantity, 2, ',', ' ') }}</td><td>{{ number_format((float) $line->unit_price, 2, ',', ' ') }} €</td><td>{{ number_format((float) $line->tax_rate, 2, ',', ' ') }} %</td><td class="amount-cell">{{ number_format((float) $line->total, 2, ',', ' ') }} €</td></tr>@endforeach</tbody>
            </table></div>
            <div class="line-summary document-totals"><div><span>Sous-total HT</span><strong>{{ number_format((float) $document->subtotal, 2, ',', ' ') }} €</strong></div><div><span>TVA</span><strong>{{ number_format((float) $document->tax_total, 2, ',', ' ') }} €</strong></div><div class="summary-total"><span>Total TTC</span><strong>{{ number_format((float) $document->total, 2, ',', ' ') }} €</strong></div></div>
            @if ($document->notes)<div class="document-note"><h3>Note au client</h3><p>{{ $document->notes }}</p></div>@endif
            @if ($document->terms)<div class="document-note"><h3>Conditions</h3><p>{{ $document->terms }}</p></div>@endif
        </section>
        <aside class="document-sidebar">
            <section class="panel detail-panel">
                <h2>Informations</h2>
                <dl class="details-grid details-single">
                    <div><dt>Client</dt><dd>{{ $document->client_snapshot['company'] ?: $document->client_snapshot['name'] }}</dd></div>
                    @if ($document->client_snapshot['email'])<div><dt>E-mail</dt><dd>{{ $document->client_snapshot['email'] }}</dd></div>@endif
                    <div><dt>Date d’émission</dt><dd>{{ $document->issue_date->format('d/m/Y') }}</dd></div>
                    @if ($document->valid_until)<div><dt>Valide jusqu’au</dt><dd>{{ $document->valid_until->format('d/m/Y') }}</dd></div>@endif
                    @if ($document->due_date)<div><dt>Échéance</dt><dd>{{ $document->due_date->format('d/m/Y') }}</dd></div>@endif
                    @if ($document->sent_at)<div><dt>Marqué envoyé</dt><dd>{{ $document->sent_at->format('d/m/Y H:i') }}</dd></div>@endif
                    @if ($document->paid_at)<div><dt>Règlement enregistré</dt><dd>{{ $document->paid_at->format('d/m/Y H:i') }}</dd></div>@endif
                    @if ($document->sourceQuote)<div><dt>Devis d’origine</dt><dd><a class="text-link" href="{{ route('quotes.show', $document->sourceQuote) }}">{{ $document->sourceQuote->number }}</a></dd></div>@endif
                </dl>
            </section>
            <section class="panel detail-panel document-actions-panel">
                <h2>Actions</h2>
                @if ($view === 'quote' && $document->status === 'draft')
                    @can('manage-documents')
                        <a class="button button-secondary button-full" href="{{ route('quotes.edit', $document) }}">Modifier le brouillon</a>
                        <form action="{{ route('quotes.send', $document) }}" method="POST">@csrf<button class="button button-primary button-full" type="submit">Marquer comme envoyé</button></form>
                        <form action="{{ route('quotes.destroy', $document) }}" method="POST" onsubmit="return confirm('Supprimer ce devis brouillon ?')">@csrf @method('DELETE')<button class="button button-danger-quiet button-full" type="submit">Supprimer le brouillon</button></form>
                    @endcan
                @elseif ($view === 'quote' && $document->status === 'sent')
                    @can('manage-documents')
                        <form action="{{ route('quotes.accept', $document) }}" method="POST">@csrf<button class="button button-primary button-full" type="submit">Enregistrer l’acceptation</button></form>
                        <form action="{{ route('quotes.reject', $document) }}" method="POST">@csrf<button class="button button-quiet button-full" type="submit">Enregistrer le refus</button></form>
                    @endcan
                @elseif ($view === 'quote' && $document->status === 'accepted' && ! $document->invoice)
                    @can('manage-documents')<form action="{{ route('quotes.convert', $document) }}" method="POST">@csrf<button class="button button-primary button-full" type="submit">Créer la facture correspondante</button></form>@endcan
                @elseif ($view === 'invoice' && $document->status === 'draft')
                    @can('manage-documents')<form action="{{ route('invoices.send', $document) }}" method="POST">@csrf<button class="button button-primary button-full" type="submit">Marquer comme envoyé</button></form>@endcan
                @elseif ($view === 'invoice' && $document->status === 'sent')
                    @can('record-payment')<form action="{{ route('invoices.pay', $document) }}" method="POST">@csrf<button class="button button-primary button-full" type="submit">Enregistrer le paiement intégral</button></form>@endcan
                @elseif ($view === 'invoice' && $document->status === 'paid')
                    <p class="muted">Le paiement intégral de cette facture est enregistré.</p>
                @elseif ($view === 'quote' && $document->invoice)
                    <a class="button button-secondary button-full" href="{{ route('invoices.show', $document->invoice) }}">Ouvrir la facture {{ $document->invoice->number }}</a>
                @else
                    <p class="muted">Aucune autre action n’est disponible pour ce statut.</p>
                @endif
                <p class="muted action-note">« Marquer comme envoyé » suit l’état d’envoi ; cela n’envoie pas d’e-mail. Téléchargez le PDF pour le transmettre à votre client.</p>
            </section>
        </aside>
    </div>
@endsection
