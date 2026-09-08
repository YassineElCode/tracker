<?php

declare(strict_types=1);

namespace App\Traits;

use App\Jobs\ProcessAutoEnrollment;
use App\Jobs\ProcessAutoEnrollmentSBAT;
use App\Mail\NewEarlierDateFound;
use App\Models\City;
use App\Models\Datum;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

trait ProcessesDatums
{
    protected function processNotifications(Request $request, string $type): array
    {
        $incomingDatums = $type === 'sbat'
            ? $this->parseAndValidateSbatDatums($request)
            : $this->parseAndValidateDatums($request);

        $city = City::where('name', $request->city)->firstOrFail();
        $datum = Datum::where('city_id', $city->id)->latest()->first();

        $usersSubscribedToCity = $city->users;
        $existingDatums = collect($datum->olddatums ?? []);

        $allNewDatums = $this->processUsersForNewDatums(
            $usersSubscribedToCity,
            $incomingDatums,
            $existingDatums,
            $city,
            $type
        );

        $foundNewDatums = $allNewDatums->isNotEmpty();
        $this->updateDatums($datum, $incomingDatums, $city->id);

        if ($foundNewDatums) {
            return [
                'message' => "New dates found for {$city->name} and notifications sent.",
                'new_datums' => $allNewDatums->unique('date')->values(),
            ];
        }

        return [
            'message' => "No new dates found for {$city->name}.",
        ];
    }

    protected function processUsersForNewDatums(
        Collection $users,
        Collection $incomingDatums,
        Collection $existingDatums,
        City $city,
        string $type
    ): Collection {
        return $users
            ->map(function (User $user) use ($incomingDatums, $existingDatums, $city, $type) {
                $filters = $this->getFilterParameters($user);
                $datumsToNotify = $this->findDatumsToNotify(
                    $incomingDatums,
                    $existingDatums,
                    $filters
                );

                if ($datumsToNotify->isEmpty()) {
                    return collect();
                }

                $this->processUserNotificationsAndEnrollments($user, $city, $type, $datumsToNotify);

                return $datumsToNotify;
            })
            ->flatten(1);
    }

    protected function processUserNotificationsAndEnrollments(
        User $user,
        City $city,
        string $type,
        Collection $datumsToNotify
    ): void {
        if ($user->send_notifications) {
            $this->sendNotifications($datumsToNotify, $user, $city->name);
        }

        $this->processEnrollments($user, $city, $type, $datumsToNotify);
    }

    protected function processEnrollments(
        User $user,
        City $city,
        string $type,
        Collection $datumsToNotify
    ): void {
        $enrollments = $user->enrollmentAutoInschrijven;

        if ($enrollments->isEmpty()) {
            return;
        }

        foreach ($enrollments as $enrollment) {
            if ($type === 'autoveiligheid' && $enrollment->examencentrum === 'Autoveiligheid') {
                ProcessAutoEnrollment::dispatch($user, $city);
            }
            if ($type === 'sbat' && $enrollment->examencentrum === 'SBAT') {
                ProcessAutoEnrollmentSBAT::dispatch($user, $city, $datumsToNotify);
            }
        }
    }

    protected function handleEmptyDatums(Request $request): void
    {
        $city = City::where('name', $request->city)->firstOrFail();
        $datum = Datum::where('city_id', $city->id)->latest()->first();
        $this->updateDatums($datum, collect(), $city->id);
        abort(response()->json(['message' => 'No dates provided.'], 400));
    }

    protected function getFilterParameters(?User $user): array
    {
        $startDatum = $user?->startDatum
            ? DateTime::createFromFormat('!d/m/Y', $user->startDatum)?->format('Y-m-d')
            : null;

        $endDatum = $user?->endDatum
            ? DateTime::createFromFormat('!d/m/Y', $user->endDatum)?->format('Y-m-d')
            : null;

        return [
            'startDatum' => $startDatum,
            'endDatum' => $endDatum,
            'startUur' => $user?->startUur,
            'endUur' => $user?->endUur,
        ];
    }

    protected function findDatumsToNotify(
        Collection $incomingDatums,
        Collection $existingDatums,
        array $filters
    ): Collection {
        // Build lookup of already-stored date→times for deduplication
        // e.g. ['2026-03-09' => ['08:00', '09:00'], '2026-03-15' => ['08:00']]
        $storedLookup = $existingDatums->mapWithKeys(
            fn ($d) => [$d['date'] => $d['times'] ?? []]
        )->toArray();

        return $incomingDatums
            ->map(function ($item) use ($storedLookup, $filters) {
                $itemDate = DateTime::createFromFormat('!Y-m-d', $item['date']);

                if (! $itemDate) {
                    return null;
                }

                if (! $this->isWithinDateRange($itemDate, $filters)) {
                    return null;
                }

                $storedTimesForDate = $storedLookup[$item['date']] ?? [];

                $newTimes = collect($item['times'] ?? [])
                    ->reject(fn ($time) => in_array($time, $storedTimesForDate))
                    ->filter(fn ($time) => $this->isTimeWithinRange($time, $filters))
                    ->values()
                    ->toArray();

                if (empty($newTimes)) {
                    return null;
                }

                return array_merge($item, ['times' => $newTimes]);
            })
            ->filter()
            ->sortBy('date')
            ->values();
    }

    private function isWithinDateRange(DateTime $itemDate, array $filters): bool
    {
        if ($filters['startDatum']) {
            $startDate = DateTime::createFromFormat('!Y-m-d', $filters['startDatum']);
            if ($startDate && $itemDate < $startDate) {
                return false;
            }
        }

        if ($filters['endDatum']) {
            $endDate = DateTime::createFromFormat('!Y-m-d', $filters['endDatum']);
            if ($endDate && $itemDate > $endDate) {
                return false;
            }
        }

        return true;
    }

    private function isTimeWithinRange(string $time, array $filters): bool
    {
        if (! $filters['startUur'] || ! $filters['endUur']) {
            return true;
        }

        return $time >= $filters['startUur'] && $time <= $filters['endUur'];
    }

    protected function updateDatums(?Datum $datum, Collection $incomingDatums, int $cityId): void
    {
        $newDatumsToStore = $incomingDatums->sortBy('date')->values()->toArray();

        if ($datum) {
            $datum->update(['olddatums' => $newDatumsToStore]);

            return;
        }

        Datum::create([
            'city_id' => $cityId,
            'olddatums' => $newDatumsToStore,
        ]);
    }

    protected function sendNotifications(Collection $datumsToNotify, User $user, string $cityName): void
    {
        Mail::to($user->email)->queue(new NewEarlierDateFound(
            $datumsToNotify->toArray(),
            $cityName
        ));
    }
}
