@extends('layouts.app')
@section('title', 'Modifier une prestation')
@section('breadcrumb', 'Catalogue / Modifier')
@section('content')
    <div class="page-heading"><div><p class="eyebrow">CATALOGUE</p><h1>Modifier la prestation</h1><p class="muted">Les documents déjà créés conservent leur prix historique.</p></div><a class="button button-quiet" href="{{ route('articles.index') }}">Retour au catalogue</a></div>
    <section class="panel form-panel"><form action="{{ route('articles.update', $article) }}" method="POST">@csrf @method('PUT') @include('articles._form')<div class="form-actions"><a class="button button-quiet" href="{{ route('articles.index') }}">Annuler</a><button class="button button-primary" type="submit">Enregistrer</button></div></form></section>
@endsection
