@extends('layouts.app')
@section('title', 'Accès API')
@section('breadcrumb', 'Accès API')
@section('content')
    <div class="page-heading"><div><p class="eyebrow">INTÉGRATIONS</p><h1>Accès API</h1><p class="muted">Créez des jetons personnels pour connecter un outil tiers à vos données.</p></div></div>
    @if (session('new_token'))
        <section class="token-reveal" role="status"><strong>Copiez ce jeton : il ne sera affiché qu’une seule fois.</strong><code>{{ session('new_token') }}</code></section>
    @endif
    <div class="dashboard-secondary staff-layout">
        <section class="panel form-panel"><h2>Créer un jeton</h2><p class="muted panel-intro">Les droits du jeton sont limités par votre rôle : {{ auth()->user()->role === 'accountant' ? 'consultation et règlements' : 'gestion des documents et consultation' }}.</p>
            <form action="{{ route('api-tokens.store') }}" method="POST">@csrf
                <div class="field"><label for="name">Nom de l’intégration</label><input id="name" name="name" required maxlength="100" placeholder="Ex. synchronisation comptable"></div>
                <div class="form-actions"><button class="button button-primary" type="submit">Créer le jeton</button></div>
            </form>
        </section>
        <section class="panel">
            <div class="panel-heading"><div><h2>Jetons actifs</h2><p class="muted">Révoquez un jeton si une intégration n’est plus utilisée.</p></div></div>
            @if ($tokens->isEmpty())<div class="empty-state"><h3>Aucun jeton API</h3><p>Créez un jeton pour utiliser l’API documentée du logiciel.</p></div>
            @else<div class="table-wrap"><table class="data-table"><thead><tr><th>NOM</th><th>DROITS</th><th>DERNIER ACCÈS</th><th></th></tr></thead><tbody>@foreach ($tokens as $token)<tr><td class="table-primary">{{ $token->name }}</td><td class="table-secondary">{{ implode(', ', $token->abilities ?? []) }}</td><td>{{ $token->last_used_at?->format('d/m/Y H:i') ?? 'Jamais' }}</td><td><form action="{{ route('api-tokens.destroy', $token->id) }}" method="POST">@csrf @method('DELETE')<button class="link-button" type="submit">Révoquer</button></form></td></tr>@endforeach</tbody></table></div>@endif
        </section>
    </div>
    <p class="muted api-doc-link">Spécification OpenAPI : <a class="text-link" href="{{ route('api.docs') }}">télécharger openapi.yaml</a>. Exemples d’intégration : <code>docs/api.md</code>.</p>
@endsection
