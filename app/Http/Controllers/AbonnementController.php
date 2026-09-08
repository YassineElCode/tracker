<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AbonnementController extends Controller
{
    public function edit(): Response
    {
        $user = Auth::user()->load('enrollmentAutoInschrijven');
        $enrollments = $user->enrollmentAutoInschrijven;

        return Inertia::render('settings/abonnement', [
            'enrollmentStatus' => $enrollments->isNotEmpty(),
        ]);
    }
}
