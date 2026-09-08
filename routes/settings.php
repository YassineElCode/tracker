<?php

use App\Http\Controllers\AbonnementController;
use App\Http\Controllers\Autoveiligheid\AutoInschrijvenController;
use App\Http\Controllers\Sbat\AutoInschrijvenSbatController;
use App\Http\Controllers\Settings\DatesController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('settings/dates', [DatesController::class, 'show'])->name('settings.dates.show');
    Route::patch('settings/dates', [DatesController::class, 'update'])->name('settings.dates.update');

    Route::get('settings/autoinschrijven', [AutoInschrijvenController::class, 'edit'])->name('autoinschrijven.edit');
    Route::patch('settings/autoinschrijven', [AutoInschrijvenController::class, 'update'])->name('autoinschrijven.update');

    Route::get('settings/autoinschrijvensbat', [AutoInschrijvenSbatController::class, 'edit'])->name('autoinschrijvensbat.edit');
    Route::patch('settings/autoinschrijvensbat', [AutoInschrijvenSbatController::class, 'update'])->name('autoinschrijvensbat.update');

    Route::get('settings/abonnement', [AbonnementController::class, 'edit'])->name('abonnement.edit');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/appearance');
    })->name('appearance.show');
});
