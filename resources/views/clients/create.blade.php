@extends('layouts.app')

@section('title', 'Nouveau client')
@section('breadcrumb', 'Clients / Nouveau')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">CARNET D'ADRESSES</p>
            <h1>Ajouter un client</h1>
            <p class="muted">Ajoutez les coordonnées qui seront reprises sur les prochains documents.</p>
        </div>
        <a class="button button-quiet" href="{{ route('clients.index') }}">← Retour aux clients</a>
    </div>

    <section class="panel form-panel">
        <form action="{{ route('clients.store') }}" method="POST">
            @csrf
            @include('clients._form')
            <div class="form-actions">
                <a class="button button-quiet" href="{{ route('clients.index') }}">Annuler</a>
                <button class="button button-primary" type="submit">Enregistrer le client</button>
            </div>
        </form>
    </section>
@endsection
