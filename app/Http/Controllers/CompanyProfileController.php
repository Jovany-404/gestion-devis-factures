<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyProfileController extends Controller
{
    public function edit(): View
    {
        return view('company.edit', ['profile' => CompanyProfile::current()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'size:2'],
            'registration_number' => ['nullable', 'string', 'max:40'],
            'vat_number' => ['nullable', 'string', 'max:40'],
            'iban' => ['nullable', 'string', 'max:34'],
            'default_tax_rate' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:100'],
            'quote_validity_days' => ['required', 'integer', 'between:1,365'],
            'invoice_due_days' => ['required', 'integer', 'between:0,365'],
        ]);

        CompanyProfile::current()->update($data);

        return to_route('company.edit')->with('success', 'Les coordonnées de l’entreprise ont été enregistrées.');
    }
}
