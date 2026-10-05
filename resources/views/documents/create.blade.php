@extends('layouts.app')
@section('title', 'Nouveau devis')
@section('breadcrumb', 'Devis / Nouveau')
@section('content')
    <div class="page-heading"><div><p class="eyebrow">NOUVEAU DOCUMENT</p><h1>Créer un devis</h1><p class="muted">Les prix sont calculés depuis le catalogue. Le numéro sera attribué à l’enregistrement.</p></div><a class="button button-quiet" href="{{ route('quotes.index') }}">Retour aux devis</a></div>
    @if ($clients->isEmpty() || $articles->isEmpty())
        <div class="flash-warning">Un devis nécessite au moins un client et une prestation active dans le catalogue.
            @if ($clients->isEmpty()) <a class="text-link" href="{{ route('clients.create') }}">Ajouter un client →</a>@endif
            @if ($articles->isEmpty()) <a class="text-link" href="{{ route('articles.create') }}">Ajouter une prestation →</a>@endif
        </div>
    @endif
    <section class="panel form-panel">@include('documents._form')</section>
@endsection
