<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function show(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        return response()->json([
            'personal_info' => [
                'achternaam' => $user->achternaam,
                'voornaam' => $user->voornaam,
                'rrn' => $user->rrn,
                'gbdatum' => $user->gbdatum,
                'tel' => $user->tel,
                'email' => $user->email,
                'adres' => $user->adres,
                'postcode' => $user->postcode,
                'sbat_email' => $user->sbat_email,
                'sbat_password' => $user->sbat_password,
                'datum_slagen_theorieB' => $user->datum_slagen_theorieB,
                'type_voorlopig_rijbewijs' => $user->type_voorlopig_rijbewijs,
                'afgiftedatum_voorlopig_rijbewijsB' => $user->afgiftedatum_voorlopig_rijbewijsB,
                'hoeveelste_poging' => $user->hoeveelste_poging,
            ],
            'license_info' => [
                'zeersteVRijbewijsDatum' => $user->zeersteVRijbewijsDatum,
                'zhuidigVRijbewijsDatum' => $user->zhuidigVRijbewijsDatum,
                'zhuidigVRijbewijsGeldigTot' => $user->zhuidigVRijbewijsGeldigTot,
            ],
            'preferences' => [
                'startDatum' => $user->startDatum,
                'endDatum' => $user->endDatum,
                'startUur' => $user->startUur,
                'endUur' => $user->endUur,
            ],
        ]);
    }
}
