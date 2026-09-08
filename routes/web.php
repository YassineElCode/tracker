<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::post('/locale/{locale}', function (string $locale) {
    if (! in_array($locale, ['nl', 'fr', 'en'])) {
        abort(400);
    }

    session()->put('locale', $locale);

    return redirect()->back();
})->name('locale.switch');

Route::get('/', function () {
    return Inertia::render('homepage');
})->name('home');

Route::get('/danku', function () {
    return Inertia::render('danku');
})->name('danku')->middleware(\App\Http\Middleware\EnsureContactFormSent::class);

Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'send'])
    ->name('contact.send')
    ->middleware('throttle:2,60');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
});

require __DIR__.'/auth.php';
require __DIR__.'/settings.php';
