<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        return view('staff.index', ['users' => User::query()->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in([User::ROLE_SALES, User::ROLE_ACCOUNTANT])],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        User::create([
            ...$data,
            'password' => Hash::make($data['password']),
        ]);

        return to_route('staff.index')->with('success', 'Le compte collaborateur a été créé.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->isAdministrator(), 403, 'Un administrateur ne peut pas supprimer un autre administrateur.');

        if ($user->documents()->exists()) {
            return to_route('staff.index')
                ->with('warning', 'Ce compte est à l’origine de documents historiques et ne peut pas être supprimé.');
        }

        $user->tokens()->delete();
        $user->delete();

        return to_route('staff.index')->with('success', 'Le compte collaborateur et ses jetons API ont été supprimés.');
    }
}
