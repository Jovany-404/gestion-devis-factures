@extends('layouts.app')
@section('title', 'Ajouter une prestation')
@section('breadcrumb', 'Catalogue / Nouvelle prestation')
@section('content')
    <div class="page-heading"><div><p class="eyebrow">CATALOGUE</p><h1>Nouvelle prestation</h1><p class="muted">Les montants du catalogue sont exprimés hors taxes.</p></div><a class="button button-quiet" href="{{ route('articles.index') }}">Retour au catalogue</a></div>
    <section class="panel form-panel"><form action="{{ route('articles.store') }}" method="POST">@csrf @include('articles._form')<div class="form-actions"><a class="button button-quiet" href="{{ route('articles.index') }}">Annuler</a><button class="button button-primary" type="submit">Enregistrer la prestation</button></div></form></section>
@endsection
