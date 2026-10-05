@extends('layouts.app')

@section('title', 'Modifier le client')
@section('breadcrumb', 'Clients / Modifier')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">CARNET D'ADRESSES</p>
            <h1>Modifier le client</h1>
            <p class="muted">Mettez à jour les coordonnées de {{ $client->name }}.</p>
        </div>
        <a class="button button-quiet" href="{{ route('clients.show', $client) }}">← Retour à la fiche</a>
    </div>

    <section class="panel form-panel">
        <form action="{{ route('clients.update', $client) }}" method="POST">
            @csrf
            @method('PUT')
            @include('clients._form')
            <div class="form-actions">
                <a class="button button-quiet" href="{{ route('clients.show', $client) }}">Annuler</a>
                <button class="button button-primary" type="submit">Enregistrer les modifications</button>
            </div>
        </form>
    </section>
@endsection
