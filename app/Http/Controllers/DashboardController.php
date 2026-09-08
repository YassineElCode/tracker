<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Datum;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $earliestDate = null;

        if (! $user->notification) {
            return Inertia::render('dashboard', [
                'latestDate' => null,
            ]);
        }

        $latestDatumEntry = Datum::latest()->first();

        if (! $latestDatumEntry || empty($latestDatumEntry->olddatums)) {
            return Inertia::render('dashboard', [
                'latestDate' => null,
            ]);
        }

        $dates = $latestDatumEntry->olddatums;

        $carbonDates = array_map(function ($dateObject) {
            try {
                return Carbon::parse($dateObject['date']);
            } catch (\Exception $e) {
                return null;
            }
        }, $dates);

        $futureDates = array_filter($carbonDates, function ($date) {
            return $date && ($date->isFuture() || $date->isToday());
        });

        if (! empty($futureDates)) {
            $earliestDate = min($futureDates);
        }

        return Inertia::render('dashboard', [
            'latestDate' => $earliestDate?->toIso8601String(),
        ]);
    }
}
