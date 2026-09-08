<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DatesController extends Controller
{
    /**
     * Show the form for editing the user's date and time settings.
     *
     * @return \Inertia\Response
     */
    public function show()
    {
        return Inertia::render('settings/dates');
    }

    /**
     * Update the user's date and time settings.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'startDatum' => ['nullable', 'string', 'max:255'],
            'endDatum' => ['nullable', 'string', 'max:255'],
            'startUur' => ['nullable', 'string', 'max:255'],
            'endUur' => ['nullable', 'string', 'max:255'],
        ]);

        $request->user()->update($validated);

        return to_route('settings.dates.show');
    }
}
