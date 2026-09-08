<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sbat;

use App\Http\Controllers\Controller;
use App\Traits\ProcessesDatums;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SbatController extends Controller
{
    use ProcessesDatums;

    public function compare(Request $request): JsonResponse
    {
        $request->validate([
            'newdatums' => ['array'],
            'newdatums.*.date' => ['required', 'string'],
            'newdatums.*.text' => ['required', 'string'],
            'newdatums.*.times' => ['array'],
            'newdatums.*.id_afspraak' => ['required', 'int'],
            'city' => ['required', 'string'],
        ]);

        $response = $this->processNotifications($request, 'sbat');

        return response()->json($response);
    }

    private function parseAndValidateSbatDatums(Request $request): Collection
    {
        if (empty($request->input('newdatums'))) {
            $this->handleEmptyDatums($request);
        }

        return collect($request->input('newdatums'))
            ->map(fn ($item) => $this->mapSbatDatumItem($item, $request->city))
            ->filter();
    }

    private function mapSbatDatumItem(array $item, string $cityName): ?array
    {
        $date = DateTime::createFromFormat('!d/m/Y', $item['date']);

        if (! $date) {
            return null;
        }

        return [
            'date' => $date->format('Y-m-d'),
            'text' => $item['text'],
            'times' => $item['times'] ?? [],
            'id_afspraak' => $item['id_afspraak'],
            'city' => $cityName,
        ];
    }
}
