@extends('layouts.app')
@section('title', 'Entreprise')
@section('breadcrumb', 'Entreprise')
@section('content')
    <div class="page-heading"><div><p class="eyebrow">PARAMÈTRES DE FACTURATION</p><h1>Entreprise</h1><p class="muted">Ces coordonnées sont figées sur chaque nouveau document et apparaissent sur les PDF.</p></div></div>
    <section class="panel form-panel"><form action="{{ route('company.update') }}" method="POST">@csrf @method('PUT')
        <div class="form-grid">
            <div class="field field-wide"><label for="name">Raison sociale ou nom commercial *</label><input id="name" name="name" value="{{ old('name', $profile->name) }}" required maxlength="180">@error('name')<span class="field-error">{{ $message }}</span>@enderror</div>
            <div class="field"><label for="email">E-mail professionnel *</label><input id="email" type="email" name="email" value="{{ old('email', $profile->email) }}" required>@error('email')<span class="field-error">{{ $message }}</span>@enderror</div>
            <div class="field"><label for="phone">Téléphone</label><input id="phone" name="phone" value="{{ old('phone', $profile->phone) }}" maxlength="50"></div>
            <div class="field field-wide"><label for="address">Adresse *</label><input id="address" name="address" value="{{ old('address', $profile->address) }}" required maxlength="255"></div>
            <div class="field"><label for="postal_code">Code postal *</label><input id="postal_code" name="postal_code" value="{{ old('postal_code', $profile->postal_code) }}" required maxlength="20"></div>
            <div class="field"><label for="city">Ville *</label><input id="city" name="city" value="{{ old('city', $profile->city) }}" required maxlength="100"></div>
            <div class="field"><label for="country">Code pays ISO (ex. FR)</label><input id="country" name="country" value="{{ old('country', $profile->country) }}" required minlength="2" maxlength="2"></div>
            <div class="field"><label for="currency">Devise des nouveaux documents</label><input id="currency" value="{{ $profile->currency }} ({{ $profile->currencyLabel() }})" disabled><span class="muted">La devise est définie par l’espace de démonstration ou l’installation.</span></div>
            <div class="field"><label for="registration_number">SIREN / numéro d’immatriculation</label><input id="registration_number" name="registration_number" value="{{ old('registration_number', $profile->registration_number) }}" maxlength="40"></div>
            <div class="field"><label for="vat_number">Numéro de TVA intracommunautaire</label><input id="vat_number" name="vat_number" value="{{ old('vat_number', $profile->vat_number) }}" maxlength="40"></div>
            <div class="field"><label for="iban">IBAN pour les règlements</label><input id="iban" name="iban" value="{{ old('iban', $profile->iban) }}" maxlength="34" autocomplete="off"></div>
            <div class="field"><label for="default_tax_rate">TVA par défaut (%)</label><input id="default_tax_rate" type="number" name="default_tax_rate" min="0" max="100" step="0.01" value="{{ old('default_tax_rate', $profile->default_tax_rate) }}" required></div>
            <div class="field"><label for="quote_validity_days">Validité par défaut d’un devis (jours)</label><input id="quote_validity_days" type="number" name="quote_validity_days" min="1" max="365" value="{{ old('quote_validity_days', $profile->quote_validity_days) }}" required></div>
            <div class="field"><label for="invoice_due_days">Délai de paiement par défaut (jours)</label><input id="invoice_due_days" type="number" name="invoice_due_days" min="0" max="365" value="{{ old('invoice_due_days', $profile->invoice_due_days) }}" required></div>
        </div>
        <div class="form-actions"><button class="button button-primary" type="submit">Enregistrer les coordonnées</button></div>
    </form></section>
@endsection
