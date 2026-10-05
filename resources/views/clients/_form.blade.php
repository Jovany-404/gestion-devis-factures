<div class="form-grid">
    <div class="field">
        <label for="name">Nom complet <span class="required">*</span></label>
        <input id="name" name="name" value="{{ old('name', $client->name ?? '') }}" required maxlength="150" autocomplete="name">
        @error('name') <span class="field-error">{{ $message }}</span> @enderror
    </div>
    <div class="field">
        <label for="company">Entreprise</label>
        <input id="company" name="company" value="{{ old('company', $client->company ?? '') }}" maxlength="150" autocomplete="organization">
        @error('company') <span class="field-error">{{ $message }}</span> @enderror
    </div>
    <div class="field">
        <label for="email">Adresse e-mail</label>
        <input id="email" type="email" name="email" value="{{ old('email', $client->email ?? '') }}" maxlength="255" autocomplete="email">
        @error('email') <span class="field-error">{{ $message }}</span> @enderror
    </div>
    <div class="field">
        <label for="phone">Téléphone</label>
        <input id="phone" type="tel" name="phone" value="{{ old('phone', $client->phone ?? '') }}" maxlength="50" autocomplete="tel">
        @error('phone') <span class="field-error">{{ $message }}</span> @enderror
    </div>
    <div class="field field-wide">
        <label for="address">Adresse</label>
        <input id="address" name="address" value="{{ old('address', $client->address ?? '') }}" maxlength="255" autocomplete="street-address">
        @error('address') <span class="field-error">{{ $message }}</span> @enderror
    </div>
    <div class="field">
        <label for="postal_code">Code postal</label>
        <input id="postal_code" name="postal_code" value="{{ old('postal_code', $client->postal_code ?? '') }}" maxlength="20" autocomplete="postal-code">
        @error('postal_code') <span class="field-error">{{ $message }}</span> @enderror
    </div>
    <div class="field">
        <label for="city">Ville</label>
        <input id="city" name="city" value="{{ old('city', $client->city ?? '') }}" maxlength="100" autocomplete="address-level2">
        @error('city') <span class="field-error">{{ $message }}</span> @enderror
    </div>
    <div class="field field-wide">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes" rows="4" maxlength="5000" placeholder="Informations utiles sur ce client…">{{ old('notes', $client->notes ?? '') }}</textarea>
        @error('notes') <span class="field-error">{{ $message }}</span> @enderror
    </div>
</div>
