<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 32px 38px; }
        body { color: #23263a; font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.55; }
        .header { border-bottom: 2px solid #34384f; padding-bottom: 19px; }
        .brand { color: #252940; font-size: 19px; font-weight: bold; }
        .document-type { color: #686c7f; font-size: 9px; letter-spacing: 2px; margin-top: 16px; text-transform: uppercase; }
        h1 { font-size: 25px; margin: 2px 0 0; }
        .meta { color: #5f6374; margin-top: 6px; }
        .parties { margin: 25px 0 21px; width: 100%; }
        .parties td { vertical-align: top; width: 50%; }
        .label { color: #74788a; font-size: 8px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
        .party-name { font-size: 12px; font-weight: bold; margin: 6px 0; }
        table.lines { border-collapse: collapse; width: 100%; }
        .lines th { background: #f1f2f6; color: #41455a; font-size: 8px; text-align: left; }
        .lines th, .lines td { border-bottom: 1px solid #e3e4ea; padding: 9px 7px; }
        .right { text-align: right !important; }
        .totals { margin: 14px 0 0 auto; width: 240px; }
        .totals td { padding: 4px 5px; }
        .grand-total { border-top: 1px solid #34384f; font-size: 13px; font-weight: bold; padding-top: 9px !important; }
        .notes { margin-top: 23px; }
        .footer { border-top: 1px solid #dedfe5; bottom: 0; color: #777b8a; font-size: 8px; left: 0; padding-top: 8px; position: fixed; right: 0; }
    </style>
</head>
@php
    $isQuote = $document->type === \App\Models\Document::TYPE_QUOTE;
    $statusLabels = ['draft' => 'Brouillon', 'sent' => 'Envoyé', 'accepted' => 'Accepté', 'rejected' => 'Refusé', 'paid' => 'Payé', 'overdue' => 'En retard'];
@endphp
<body>
    <header class="header">
        <div class="brand">{{ $document->company_snapshot['name'] }}</div>
        <div>{{ $document->company_snapshot['address'] }}</div>
        <div>{{ trim(($document->company_snapshot['postal_code'] ?? '').' '.($document->company_snapshot['city'] ?? '')) }}</div>
        <div>{{ $document->company_snapshot['email'] }}{{ $document->company_snapshot['phone'] ? ' · '.$document->company_snapshot['phone'] : '' }}</div>
        @if ($document->company_snapshot['registration_number'])<div>Immatriculation : {{ $document->company_snapshot['registration_number'] }}</div>@endif
        @if ($document->company_snapshot['vat_number'])<div>TVA : {{ $document->company_snapshot['vat_number'] }}</div>@endif
        <div class="document-type">{{ $isQuote ? 'Devis' : 'Facture' }}</div>
        <h1>{{ $document->number }}</h1>
        <div class="meta">Date : {{ $document->issue_date->format('d/m/Y') }} · Statut : {{ $statusLabels[$document->displayStatus()] ?? $document->displayStatus() }}</div>
        @if ($document->valid_until)<div class="meta">Devis valable jusqu’au {{ $document->valid_until->format('d/m/Y') }}</div>@endif
        @if ($document->due_date)<div class="meta">Date d’échéance : {{ $document->due_date->format('d/m/Y') }}</div>@endif
    </header>
    <table class="parties"><tr>
        <td><div class="label">Émetteur</div><div class="party-name">{{ $document->company_snapshot['name'] }}</div></td>
        <td><div class="label">Client</div><div class="party-name">{{ $document->client_snapshot['company'] ?: $document->client_snapshot['name'] }}</div>
            @if ($document->client_snapshot['company'])<div>{{ $document->client_snapshot['name'] }}</div>@endif
            @if ($document->client_snapshot['address'])<div>{{ $document->client_snapshot['address'] }}</div>@endif
            <div>{{ trim(($document->client_snapshot['postal_code'] ?? '').' '.($document->client_snapshot['city'] ?? '')) }}</div>
            @if ($document->client_snapshot['email'])<div>{{ $document->client_snapshot['email'] }}</div>@endif
        </td>
    </tr></table>
    <table class="lines">
        <thead><tr><th>Désignation</th><th>Qté</th><th>Unité</th><th class="right">Prix HT</th><th class="right">TVA</th><th class="right">Total TTC</th></tr></thead>
        <tbody>@foreach ($document->lines as $line)
            <tr><td>{{ $line->description }}</td><td>{{ number_format((float) $line->quantity, 2, ',', ' ') }}</td><td>{{ $line->unit }}</td><td class="right">{{ $document->formatAmount($line->unit_price) }}</td><td class="right">{{ number_format((float) $line->tax_rate, 2, ',', ' ') }} %</td><td class="right">{{ $document->formatAmount($line->total) }}</td></tr>
        @endforeach</tbody>
    </table>
    <table class="totals">
        <tr><td>Sous-total HT</td><td class="right">{{ $document->formatAmount($document->subtotal) }}</td></tr>
        <tr><td>TVA</td><td class="right">{{ $document->formatAmount($document->tax_total) }}</td></tr>
        <tr><td class="grand-total">Total TTC</td><td class="right grand-total">{{ $document->formatAmount($document->total) }}</td></tr>
    </table>
    @if ($document->notes || $document->terms || $document->company_snapshot['iban'])
        <div class="notes">
            @if ($document->notes)<div><strong>Note :</strong> {{ $document->notes }}</div>@endif
            @if ($document->terms)<div><strong>Conditions :</strong> {{ $document->terms }}</div>@endif
            @if (! $isQuote && $document->company_snapshot['iban'])<div><strong>IBAN :</strong> {{ $document->company_snapshot['iban'] }}</div>@endif
        </div>
    @endif
    <footer class="footer">{{ $document->company_snapshot['name'] }} · {{ $document->number }} · Document édité le {{ now()->format('d/m/Y') }}</footer>
</body>
</html>
