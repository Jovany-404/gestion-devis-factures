@extends('layouts.app')

@section('title', $client->name)
@section('breadcrumb', 'Clients / Fiche')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">FICHE CLIENT</p>
            <h1>{{ $client->name }}</h1>
            <p class="muted">{{ $client->company ?: 'Particulier' }}</p>
        </div>
        <div class="heading-actions">
            <a class="button button-quiet" href="{{ route('clients.index') }}">← Tous les clients</a>
            <a class="button button-primary" href="{{ route('clients.edit', $client) }}">Modifier</a>
        </div>
    </div>

    <section class="panel detail-panel">
        <h2>Coordonnées</h2>
        <dl class="details-grid">
            <div><dt>Adresse e-mail</dt><dd>{{ $client->email ?: '—' }}</dd></div>
            <div><dt>Téléphone</dt><dd>{{ $client->phone ?: '—' }}</dd></div>
            <div><dt>Adresse</dt><dd>{{ $client->address ?: '—' }}</dd></div>
            <div><dt>Code postal et ville</dt><dd>{{ trim(($client->postal_code ?? '').' '.($client->city ?? '')) ?: '—' }}</dd></div>
            @if ($client->notes)
                <div class="detail-wide"><dt>Notes</dt><dd class="notes-text">{{ $client->notes }}</dd></div>
            @endif
        </dl>
        <div class="detail-footer">
            <span class="muted">Fiche créée le {{ $client->created_at->format('d/m/Y') }}</span>
            <form action="{{ route('clients.destroy', $client) }}" method="POST" onsubmit="return confirm('Supprimer définitivement cette fiche client ?')">
                @csrf
                @method('DELETE')
                <button class="button button-danger-quiet" type="submit">Supprimer ce client</button>
            </form>
        </div>
    </section>
@endsection
