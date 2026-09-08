<?php

use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/settings/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/settings/profile', [
            'voornaam' => 'Test',
            'achternaam' => 'User',
            'email' => 'test2@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors();

    $user->refresh();

    expect($user->voornaam)->toBe('Test');
    expect($user->achternaam)->toBe('User');
    expect($user->email)->toBe('test2@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/settings/profile', [
            'voornaam' => 'Test',
            'achternaam' => 'User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});
