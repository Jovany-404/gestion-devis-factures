@extends('layouts.app')

@section('title', 'Catalogue')
@section('breadcrumb', 'Catalogue')

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">TARIFS ET PRESTATIONS</p><h1>Catalogue</h1><p class="muted">Les prix et taux de TVA du catalogue sont copiés dans les documents à leur création.</p></div>
        @can('manage-catalog')<a class="button button-primary" href="{{ route('articles.create') }}">Ajouter une prestation</a>@endcan
    </div>
    <section class="panel">
        <form class="search-form" action="{{ route('articles.index') }}" method="GET">
            <label class="search-box"><span aria-hidden="true">⌕</span><input type="search" name="search" value="{{ $search }}" placeholder="Rechercher par référence ou désignation…" aria-label="Rechercher une prestation"></label>
            <button class="button button-secondary" type="submit">Rechercher</button>
        </form>
        @if ($articles->isEmpty())
            <div class="empty-state"><span class="empty-icon">02</span><h3>Le catalogue est vide</h3><p>Ajoutez vos prestations avec leur prix hors taxes et leur taux de TVA pour les réutiliser dans vos devis.</p>@can('manage-catalog')<a class="button button-primary" href="{{ route('articles.create') }}">Ajouter une prestation</a>@endcan</div>
        @else
            <div class="table-wrap"><table class="data-table">
                <thead><tr><th>RÉFÉRENCE</th><th>DÉSIGNATION</th><th>UNITÉ</th><th>PRIX HT</th><th>TVA</th><th>ÉTAT</th><th></th></tr></thead>
                <tbody>@foreach ($articles as $article)
                    <tr>
                        <td class="table-secondary">{{ $article->sku }}</td>
                        <td><span class="table-primary">{{ $article->name }}</span><span class="table-secondary">{{ $article->description }}</span></td>
                        <td>{{ $article->unit }}</td>
                        <td class="amount-cell">{{ $profile->formatAmount($article->unit_price) }}</td>
                        <td>{{ number_format((float) $article->tax_rate, 2, ',', ' ') }} %</td>
                        <td><span class="status-badge status-{{ $article->is_active ? 'active' : 'archived' }}">{{ $article->is_active ? 'Actif' : 'Archivé' }}</span></td>
                        <td>@can('manage-catalog')@if ($article->is_active)<div class="heading-actions"><a class="row-action" href="{{ route('articles.edit', $article) }}" aria-label="Modifier {{ $article->name }}">Modifier</a><form action="{{ route('articles.destroy', $article) }}" method="POST">@csrf @method('DELETE')<button class="link-button" type="submit">Archiver</button></form></div>@endif @endcan</td>
                    </tr>
                @endforeach</tbody>
            </table></div>
            <div class="pagination">{{ $articles->links() }}</div>
        @endif
    </section>
@endsection
