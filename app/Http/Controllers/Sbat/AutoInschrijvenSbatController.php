<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sbat;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AutoInschrijvenSbatController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('settings/autoinschrijvensbat');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'datum_slagen_theorieB' => ['required', 'string'],
            'type_voorlopig_rijbewijs' => ['required', 'string'],
            'afgiftedatum_voorlopig_rijbewijsB' => ['required', 'string'],
            'hoeveelste_poging' => ['required', 'string'],
        ]);

        $user = Auth::user();
        $user->update([
            'sbat_email' => $validated['email'],
            'sbat_password' => $validated['password'],
            'datum_slagen_theorieB' => $validated['datum_slagen_theorieB'],
            'type_voorlopig_rijbewijs' => $validated['type_voorlopig_rijbewijs'],
            'afgiftedatum_voorlopig_rijbewijsB' => $validated['afgiftedatum_voorlopig_rijbewijsB'],
            'hoeveelste_poging' => $validated['hoeveelste_poging'],
        ]);

        return back()->with([
            'success' => 'SBAT instellingen zijn bijgewerkt!',
        ]);
    }
}
