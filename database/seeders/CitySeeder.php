<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            ['name' => 'Deurne', 'code' => '1004', 'company' => 'Autoveiligheid'],
            ['name' => 'Alken', 'code' => '1005', 'company' => 'Autoveiligheid'],
            ['name' => 'Kontich', 'code' => '1022', 'company' => 'Autoveiligheid'],
            ['name' => 'Geel', 'code' => '1023', 'company' => 'Autoveiligheid'],
            ['name' => 'Haasrode', 'code' => '1024', 'company' => 'Autoveiligheid'],
            ['name' => 'Bree', 'code' => '1033', 'company' => 'Autoveiligheid'],

            // SBAT exam centers
            ['name' => 'Sint-Denijs-Westrem', 'code' => '1', 'company' => 'SBAT'],
            ['name' => 'Brakel', 'code' => '7', 'company' => 'SBAT'],
            ['name' => 'Eeklo', 'code' => '8', 'company' => 'SBAT'],
            ['name' => 'Erembodegem', 'code' => '9', 'company' => 'SBAT'],
            ['name' => 'Sint-Niklaas', 'code' => '10', 'company' => 'SBAT'],
        ];

        foreach ($cities as $city) {
            City::create($city);
        }
    }
}
