@extends('layouts.app')

@section('title', 'Clients')
@section('breadcrumb', 'Clients')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">VOTRE CARNET D'ADRESSES</p>
            <h1>Clients</h1>
            <p class="muted">Retrouvez et gérez les coordonnées de vos clients.</p>
        </div>
        <a class="button button-primary" href="{{ route('clients.create') }}"><span>＋</span> Ajouter un client</a>
    </div>

    <section class="panel">
        <form class="search-form" action="{{ route('clients.index') }}" method="GET">
            <label class="search-box">
                <span aria-hidden="true">⌕</span>
                <input type="search" name="search" value="{{ $search }}" placeholder="Rechercher par nom, entreprise, e-mail ou ville…" aria-label="Rechercher un client">
            </label>
            <button class="button button-secondary" type="submit">Rechercher</button>
            @if ($search !== '')
                <a class="clear-search" href="{{ route('clients.index') }}">Effacer</a>
            @endif
        </form>

        @if ($clients->isEmpty())
            <div class="empty-state">
                <span class="empty-icon">⌕</span>
                <h3>{{ $search !== '' ? 'Aucun résultat' : 'Aucun client pour le moment' }}</h3>
                <p>{{ $search !== '' ? 'Essayez avec un autre nom, e-mail ou ville.' : 'Créez une fiche pour retrouver facilement les coordonnées de vos clients.' }}</p>
                @if ($search === '')
                    <a class="button button-secondary" href="{{ route('clients.create') }}">Ajouter un client</a>
                @endif
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>CLIENT</th><th>E-MAIL</th><th>TÉLÉPHONE</th><th>VILLE</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($clients as $client)
                        <tr>
                            <td><a class="table-primary" href="{{ route('clients.show', $client) }}">{{ $client->name }}</a><span class="table-secondary">{{ $client->company ?: 'Particulier' }}</span></td>
                            <td>{{ $client->email ?: '—' }}</td>
                            <td>{{ $client->phone ?: '—' }}</td>
                            <td>{{ $client->city ?: '—' }}</td>
                            <td><a class="row-action" href="{{ route('clients.show', $client) }}" aria-label="Voir {{ $client->name }}">→</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @if ($clients->hasPages())
                <div class="pagination">{{ $clients->links() }}</div>
            @endif
        @endif
    </section>
@endsection
