@extends('layouts.app')
@section('title', 'Équipe')
@section('breadcrumb', 'Équipe')
@section('content')
    <div class="page-heading"><div><p class="eyebrow">ACCÈS AU LOGICIEL</p><h1>Équipe</h1><p class="muted">Créez un compte par collaborateur. Les accès sont attribués par rôle.</p></div></div>
    <div class="dashboard-secondary staff-layout">
        <section class="panel form-panel"><h2>Ajouter un collaborateur</h2><p class="muted panel-intro">Communiquez son mot de passe initial de manière sécurisée.</p>
            <form action="{{ route('staff.store') }}" method="POST">@csrf
                <div class="form-grid form-grid-single">
                    <div class="field"><label for="name">Nom complet</label><input id="name" name="name" value="{{ old('name') }}" required maxlength="150"></div>
                    <div class="field"><label for="email">E-mail professionnel</label><input id="email" type="email" name="email" value="{{ old('email') }}" required></div>
                    <div class="field"><label for="role">Rôle</label><select id="role" name="role" required><option value="sales">Commercial — clients, devis et factures</option><option value="accountant">Comptabilité — consultation et règlements</option></select></div>
                    <div class="field"><label for="password">Mot de passe initial (12 caractères minimum)</label><input id="password" type="password" name="password" required minlength="12"></div>
                    <div class="field"><label for="password_confirmation">Confirmer le mot de passe</label><input id="password_confirmation" type="password" name="password_confirmation" required minlength="12"></div>
                </div>
                <div class="form-actions"><button class="button button-primary" type="submit">Créer le compte</button></div>
            </form>
        </section>
        <section class="panel">
            <div class="panel-heading"><div><h2>Comptes actifs</h2><p class="muted">{{ $users->count() }} membre{{ $users->count() === 1 ? '' : 's' }} dans l’espace de travail.</p></div></div>
            <div class="table-wrap"><table class="data-table"><thead><tr><th>MEMBRE</th><th>RÔLE</th><th></th></tr></thead><tbody>
                @foreach ($users as $user)<tr><td><span class="table-primary">{{ $user->name }}</span><span class="table-secondary">{{ $user->email }}</span></td><td>{{ \App\Models\User::roleLabel($user->role) }}</td><td>@if (! $user->isAdministrator())<form action="{{ route('staff.destroy', $user) }}" method="POST" onsubmit="return confirm('Supprimer ce compte et révoquer ses jetons API ?')">@csrf @method('DELETE')<button class="link-button" type="submit">Révoquer l’accès</button></form>@else<span class="muted">Administrateur actif</span>@endif</td></tr>@endforeach
            </tbody></table></div>
        </section>
    </div>
@endsection
