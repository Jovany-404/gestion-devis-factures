@extends('layouts.app')
@section('title', 'Modifier le devis')
@section('breadcrumb', 'Devis / Modifier')
@section('content')
    <div class="page-heading"><div><p class="eyebrow">BROUILLON {{ $document->number }}</p><h1>Modifier le devis</h1><p class="muted">Le document conserve son numéro. Toute modification est impossible après son envoi.</p></div><a class="button button-quiet" href="{{ route('quotes.show', $document) }}">Retour au devis</a></div>
    <section class="panel form-panel">@include('documents._form')</section>
@endsection
