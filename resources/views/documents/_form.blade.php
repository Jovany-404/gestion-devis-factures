<form action="{{ $document->exists ? route('quotes.update', $document) : route('quotes.store') }}" method="POST" id="document-form" data-currency="{{ $currency }}">
    @csrf
    @if ($document->exists) @method('PUT') @endif
    <div class="form-grid">
        <div class="field field-wide">
            <label for="client_id">Client</label>
            <select id="client_id" name="client_id" required>
                    <option value="">Sélectionner un client</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" @selected((string) old('client_id', $document->client_id) === (string) $client->id)>{{ $client->company ?: $client->name }}{{ $client->company ? ' · '.$client->name : '' }}</option>
                @endforeach
            </select>
            @error('client_id')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field"><label for="issue_date">Date du devis</label><input id="issue_date" type="date" name="issue_date" value="{{ old('issue_date', $document->issue_date?->format('Y-m-d') ?? today()->toDateString()) }}" required>@error('issue_date')<span class="field-error">{{ $message }}</span>@enderror</div>
        <div class="field"><label for="valid_until">Valide jusqu’au</label><input id="valid_until" type="date" name="valid_until" value="{{ old('valid_until', $document->valid_until?->format('Y-m-d')) }}">@error('valid_until')<span class="field-error">{{ $message }}</span>@enderror</div>
    </div>

    <div class="form-section-heading"><div><h2>Prestations</h2><p class="muted">Le prix et la TVA sont lus dans le catalogue et figés sur le devis.</p></div><button class="button button-secondary" type="button" id="add-line">Ajouter une ligne</button></div>
    @error('lines')<span class="field-error">{{ $message }}</span>@enderror
    <div class="table-wrap line-table-wrap">
        <table class="data-table line-table">
            <thead><tr><th>PRESTATION</th><th>QTÉ</th><th>UNITÉ</th><th>PRIX HT</th><th>TVA</th><th>TOTAL TTC</th><th></th></tr></thead>
            <tbody id="document-lines">
                @php
                    $formLines = old('lines', $document->exists
                        ? $document->lines->map(fn ($line) => ['article_id' => $line->article_id, 'quantity' => $line->quantity])->all()
                        : [['article_id' => '', 'quantity' => '1']]);
                @endphp
                @foreach ($formLines as $index => $formLine)
                    <tr class="line-row">
                        <td><select name="lines[{{ $index }}][article_id]" class="line-article" required><option value="">Choisir une prestation</option>@foreach ($articles as $article)<option value="{{ $article->id }}" data-name="{{ $article->name }}" data-description="{{ $article->description }}" data-unit="{{ $article->unit }}" data-price="{{ $article->unit_price }}" data-tax="{{ $article->tax_rate }}" @selected((string) ($formLine['article_id'] ?? '') === (string) $article->id)>{{ $article->sku }} — {{ $article->name }}</option>@endforeach</select></td>
                        <td><input class="line-quantity" type="number" name="lines[{{ $index }}][quantity]" value="{{ $formLine['quantity'] ?? 1 }}" min="0.01" max="999999" step="0.01" required></td>
                        <td class="line-unit">—</td><td class="line-price">—</td><td class="line-tax">—</td><td class="line-total amount-cell">—</td>
                        <td><button class="line-remove link-button" type="button" aria-label="Supprimer cette ligne">Retirer</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="line-summary">
        <div><span>Sous-total HT</span><strong id="subtotal-preview">{{ (new \App\Models\Document(['currency' => $currency]))->formatAmount(0) }}</strong></div>
        <div><span>TVA</span><strong id="tax-preview">{{ (new \App\Models\Document(['currency' => $currency]))->formatAmount(0) }}</strong></div>
        <div class="summary-total"><span>Total TTC</span><strong id="total-preview">{{ (new \App\Models\Document(['currency' => $currency]))->formatAmount(0) }}</strong></div>
    </div>
    <template id="line-template"><tr class="line-row">
        <td><select name="lines[__INDEX__][article_id]" class="line-article" required><option value="">Choisir une prestation</option>@foreach ($articles as $article)<option value="{{ $article->id }}" data-name="{{ $article->name }}" data-description="{{ $article->description }}" data-unit="{{ $article->unit }}" data-price="{{ $article->unit_price }}" data-tax="{{ $article->tax_rate }}">{{ $article->sku }} — {{ $article->name }}</option>@endforeach</select></td>
        <td><input class="line-quantity" type="number" name="lines[__INDEX__][quantity]" value="1" min="0.01" max="999999" step="0.01" required></td>
        <td class="line-unit">—</td><td class="line-price">—</td><td class="line-tax">—</td><td class="line-total amount-cell">—</td>
        <td><button class="line-remove link-button" type="button" aria-label="Supprimer cette ligne">Retirer</button></td>
    </tr></template>

    <div class="form-grid document-extra-fields">
        <div class="field"><label for="notes">Note destinée au client</label><textarea id="notes" name="notes" rows="3" maxlength="5000">{{ old('notes', $document->notes) }}</textarea></div>
        <div class="field"><label for="terms">Conditions du devis</label><textarea id="terms" name="terms" rows="3" maxlength="5000">{{ old('terms', $document->terms) }}</textarea></div>
    </div>
    <div class="form-actions">
        <a class="button button-quiet" href="{{ route('quotes.index') }}">Annuler</a>
        <button class="button button-primary" type="submit">{{ $document->exists ? 'Enregistrer le brouillon' : 'Créer le devis brouillon' }}</button>
    </div>
</form>
<script src="{{ asset('js/document-form.js') }}" defer></script>
