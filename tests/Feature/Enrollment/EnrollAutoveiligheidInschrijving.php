<?php

use App\Models\EnrollmentAutoInschrijven;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

test('user gets enrolled via Artisan command', function () {
    $user = User::factory()->create();

    Artisan::call('enrollment:manage', [
        'action' => 'enroll',
        'center' => 'autoveiligheid',
        'user_id' => $user->id,
    ]);

    $output = Artisan::output();

    expect($output)->toContain("{$user->name} has been enrolled in Autoveiligheid.");

    $this->assertDatabaseHas('enrollment_auto_inschrijven', [
        'user_id' => $user->id,
        'examencentrum' => 'Autoveiligheid',
    ]);
});

test('user gets unenrolled via Artisan command', function () {
    $user = User::factory()->create();

    EnrollmentAutoInschrijven::create([
        'user_id' => $user->id,
        'examencentrum' => 'SBAT',
    ]);

    Artisan::call('enrollment:manage', [
        'action' => 'unenroll',
        'center' => 'sbat',
        'user_id' => $user->id,
    ]);

    $output = Artisan::output();

    expect($output)->toContain("{$user->name} has been unenrolled from SBAT.");

    $this->assertDatabaseMissing('enrollment_auto_inschrijven', [
        'user_id' => $user->id,
        'examencentrum' => 'SBAT',
    ]);
});

test('fails when user does not exist', function () {
    $result = Artisan::call('enrollment:manage', [
        'action' => 'enroll',
        'center' => 'autoveiligheid',
        'user_id' => 999999,
    ]);

    $output = Artisan::output();

    expect($result)->toBe(1); // Command::FAILURE
    expect($output)->toContain('User with ID 999999 not found.');
});

test('fails with invalid center', function () {
    $user = User::factory()->create();

    $result = Artisan::call('enrollment:manage', [
        'action' => 'enroll',
        'center' => 'invalidcenter',
        'user_id' => $user->id,
    ]);

    $output = Artisan::output();

    expect($result)->toBe(1);
    expect($output)->toContain('Invalid exam center');
});

test('fails with invalid action', function () {
    $user = User::factory()->create();

    $result = Artisan::call('enrollment:manage', [
        'action' => 'invalid',
        'center' => 'sbat',
        'user_id' => $user->id,
    ]);

    $output = Artisan::output();

    expect(value: $result)->toBe(1);
});
