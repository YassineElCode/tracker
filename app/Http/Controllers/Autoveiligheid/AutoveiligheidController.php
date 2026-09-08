<?php

declare(strict_types=1);

namespace App\Http\Controllers\Autoveiligheid;

use App\Http\Controllers\Controller;
use App\Traits\ProcessesDatums;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AutoveiligheidController extends Controller
{
    use ProcessesDatums;

    public function compare(Request $request): JsonResponse
    {
        $request->validate([
            'newdatums' => ['array'],
            'newdatums.*.date' => ['required', 'string'],
            'newdatums.*.text' => ['required', 'string'],
            'newdatums.*.times' => ['array'],
            'city' => ['required', 'string'],
        ]);

        $response = $this->processNotifications($request, 'autoveiligheid');

        return response()->json($response);
    }

    private function parseAndValidateDatums(Request $request): Collection
    {
        if (empty($request->input('newdatums'))) {
            $this->handleEmptyDatums($request);
        }

        return collect($request->input('newdatums'))
            ->map(fn ($item) => $this->mapDatumItem($item, $request->city))
            ->filter();
    }

    private function mapDatumItem(array $item, string $cityName): ?array
    {
        $date = DateTime::createFromFormat('!d/m/Y', $item['date']);

        if (! $date) {
            return null;
        }

        return [
            'date' => $date->format('Y-m-d'),
            'text' => $item['text'],
            'times' => $item['times'] ?? [],
            'city' => $cityName,
        ];
    }
}
