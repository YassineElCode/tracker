<?php

use App\Models\City;
use App\Models\Datum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'send_notifications' => true,
        'startDatum' => '01/01/2024',
        'endDatum' => '31/12/2025',
        'startUur' => '08:00',
        'endUur' => '16:00',
    ]);

    $this->city = City::factory()->create([
        'name' => 'Deurne',
        'code' => '1004',
        'company' => 'Autoveiligheid',
    ]);

    $this->city->users()->attach($this->user);

    Datum::create([
        'city_id' => $this->city->id,
        'olddatums' => [
            [
                'date' => '10/08/2025',
                'text' => 'Old available date',
                'times' => ['10:00'],
            ],
        ],
    ]);
});

test('compare-datums: sends notifications if the same date but earlier time', function () {
    $response = $this->postJson('/api/compare-datums', [
        'newdatums' => [
            [
                'date' => '10/08/2025',
                'text' => 'New early date',
                'times' => ['09:00'],
            ],
        ],
        'city' => 'Deurne',
    ]);

    $response->assertOk();
    $response->assertJsonFragment([
        'message' => 'New earlier dates found for Deurne and notifications sent.',
    ]);
});

test('compare-datums: sends notifications if earlier datums found for Deurne', function () {
    $response = $this->postJson('/api/compare-datums', [
        'newdatums' => [
            [
                'date' => '10/08/2025',
                'text' => 'New early date',
                'times' => ['09:00'],
            ],
        ],
        'city' => 'Deurne',
    ]);
    $response->assertOk();
    $response->assertJsonFragment([
        'message' => 'New earlier dates found for Deurne and notifications sent.',
    ]);
});

test('compare-datums: fails validation with missing fields', function () {
    $response = $this->postJson('/api/compare-datums', [
        'newdatums' => [
            ['text' => 'Missing date'],
        ],
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['newdatums.0.date', 'city']);
});

test('compare-datums: returns 400 when newdatums is empty', function () {
    $response = $this->postJson('/api/compare-datums', [
        'newdatums' => [],
        'city' => 'Deurne',
    ]);

    $latestDatum = Datum::latest()->first();

    $this->assertCount(0, $latestDatum->olddatums);

    $response->assertStatus(400);
    $response->assertJson([
        'message' => 'No dates provided.',
    ]);
});

test('compare-datums: returns 404 when city not found', function () {
    $response = $this->postJson('/api/compare-datums', [
        'newdatums' => [
            [
                'date' => '01/08/2025',
                'text' => 'Test date',
                'times' => ['09:00'],
            ],
        ],
        'city' => 'NonExistentCity',
    ]);

    $response->assertStatus(404);
});
