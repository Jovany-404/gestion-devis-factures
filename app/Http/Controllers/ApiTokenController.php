<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiTokenController extends Controller
{
    public function index(Request $request): View
    {
        return view('settings.api-tokens', [
            'tokens' => $request->user()->tokens()->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $abilities = match ($request->user()->role) {
            User::ROLE_ADMIN, User::ROLE_SALES => [
                'documents:read',
                'documents:write',
                'payments:write',
            ],
            User::ROLE_ACCOUNTANT => [
                'documents:read',
                'payments:write',
            ],
            default => ['documents:read'],
        };

        $token = $request->user()->createToken($data['name'], $abilities);

        return to_route('api-tokens.index')
            ->with('new_token', $token->plainTextToken)
            ->with('success', 'Copiez ce jeton maintenant. Il ne sera plus affiché.');
    }

    public function destroy(Request $request, string $token): RedirectResponse
    {
        $request->user()->tokens()->whereKey($token)->firstOrFail()->delete();

        return to_route('api-tokens.index')->with('success', 'Le jeton API a été révoqué.');
    }
}
