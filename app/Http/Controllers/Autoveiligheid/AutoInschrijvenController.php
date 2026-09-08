<?php

declare(strict_types=1);

namespace App\Http\Controllers\Autoveiligheid;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AutoInschrijvenController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('settings/autoinschrijven');
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return to_route('autoinschrijven.edit');
    }
}
